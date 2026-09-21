<?php

namespace Tests\Support\Parity;

use RuntimeException;
use Throwable;
use Viewi\JsTranspile\BaseFunction;

/**
 * Runs every parity case through PHP and through the JS port (one node process for the whole
 * suite), and caches both results for PhpJsParityTest.
 *
 * Cases live in tests/Support/Data/parity/<Group>.php, each returning [fn => [case, …]].
 * A case is either a plain argument list — ['abc', 2] — or an array with 'args' and any of:
 *   'refs'      => [argIndex, …]  by-reference arguments compared after the call too
 *                                 (sort, preg_match's $matches, array_splice…)
 *   'knownDiff' => 'id'           an accepted difference from Data/parity-known-differences.php;
 *                                 the case must keep differing
 *   'shape'     => true           compare types and sizes only (rand, uniqid, shuffle…)
 *   'label'     => '…'            test name, defaults to the arguments
 * PHP constants go in as new PhpConstant('NAME'), never as their value.
 */
final class ParityRunner
{
    private const CASES_DIR = __DIR__ . '/../Data/parity';
    private const NODE_RUNNER = __DIR__ . '/parity-runner.mjs';
    private const KNOWN_DIFFERENCES = __DIR__ . '/../Data/parity-known-differences.php';

    /** @var array<string, class-string<BaseFunction>>|null */
    private static ?array $functions = null;
    private static ?array $cases = null;
    private static ?array $results = null;

    /** @return array<string, class-string<BaseFunction>> fn name => BaseFunction class, as the Builder sees it */
    public static function functions(): array
    {
        return self::$functions ??= require __DIR__ . '/../../../src/Viewi/JsTranspile/functions.php';
    }

    /** @return array<string, array{functions: list<string>, why: string, advice: string}> */
    public static function knownDifferences(): array
    {
        return require self::KNOWN_DIFFERENCES;
    }

    /** @return array<string, list<array{args: list<mixed>, refs: list<int>, knownDiff: ?string, shape: bool, label: string}>> */
    public static function cases(): array
    {
        if (self::$cases !== null) {
            return self::$cases;
        }
        self::$cases = [];
        foreach (glob(self::CASES_DIR . '/*.php') as $file) {
            foreach (require $file as $fn => $list) {
                if (isset(self::$cases[$fn])) {
                    throw new RuntimeException("Parity: '$fn' has cases in more than one file (" . basename($file) . ').');
                }
                self::$cases[$fn] = array_map([self::class, 'normalizeCase'], $list);
            }
        }
        ksort(self::$cases);
        return self::$cases;
    }

    /** @return array{php: array, js: array, phpRefs: array, jsRefs: array} tagged values (see Value) */
    public static function result(string $fn, int $index): array
    {
        self::$results ??= self::runAll();
        return self::$results[$fn][$index];
    }

    private static function normalizeCase(array $case): array
    {
        if (!array_key_exists('args', $case)) {
            $case = ['args' => $case];
        }
        $case['refs'] ??= [];
        $case['knownDiff'] ??= null;
        $case['shape'] ??= false;
        $case['label'] ??= self::label($case['args']);
        return $case;
    }

    private static function label(array $args): string
    {
        $parts = array_map(
            fn($arg) => match (true) {
                $arg instanceof PhpConstant => $arg->name,
                is_string($arg) && !mb_check_encoding($arg, 'UTF-8') => 'bytes:' . bin2hex($arg),
                default => json_encode($arg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_PARTIAL_OUTPUT_ON_ERROR),
            },
            $args
        );
        $label = implode(', ', $parts);
        return mb_strlen($label) > 80 ? mb_substr($label, 0, 77) . '...' : $label;
    }

    private static function runAll(): array
    {
        $functions = self::functions();
        $jobs = [];
        $results = [];
        foreach (self::cases() as $fn => $cases) {
            try {
                $source = isset($functions[$fn]) ? self::jsSource($fn) : null;
            } catch (RuntimeException $e) {
                $source = null;
                $sourceError = $e->getMessage();
            }
            foreach ($cases as $index => $case) {
                $results[$fn][$index] = self::runPhp($fn, $case);
            }
            if ($source === null) {
                $error = ['t' => 'error', 'v' => $sourceError ?? "'$fn' is not in functions.php"];
                foreach ($cases as $index => $_) {
                    $results[$fn][$index] += ['js' => $error, 'jsRefs' => []];
                }
                unset($sourceError);
                continue;
            }
            $jobs[] = [
                'fn' => $fn,
                'source' => $source,
                'cases' => array_map(
                    fn($case) => ['args' => array_map([Value::class, 'encode'], $case['args']), 'refs' => $case['refs']],
                    $cases
                ),
            ];
        }

        foreach (self::runNode($jobs) as $fn => $jsResults) {
            foreach ($jsResults as $index => $js) {
                $results[$fn][$index] += ['js' => $js['ret'], 'jsRefs' => (array)$js['refs']];
            }
        }
        return $results;
    }

    /** The port plus everything it pulls in through getUses(), dependencies first — what the Builder ships. */
    private static function jsSource(string $fn): string
    {
        $functions = self::functions();
        $ordered = [];
        $visit = function (string $name, string $requiredBy) use (&$visit, &$ordered, $functions) {
            if (isset($ordered[$name])) {
                return;
            }
            if (!isset($functions[$name])) {
                throw new RuntimeException("dependency '$name' (required by $requiredBy) is not in functions.php — the Builder would fail too");
            }
            $ordered[$name] = false; // in progress: tolerate cycles
            foreach ($functions[$name]::getUses() as $dependency) {
                $visit($dependency, $name);
            }
            $ordered[$name] = $functions[$name]::getJs();
        };
        $visit($fn, $fn);
        return implode("\n\n", $ordered);
    }

    private static function runPhp(string $fn, array $case): array
    {
        $callable = PhpEquivalents::get($fn) ?? $fn;
        if (!is_callable($callable)) {
            return ['php' => ['t' => 'error', 'v' => "no PHP function '$fn' — add it to PhpEquivalents"], 'phpRefs' => []];
        }
        $args = array_map(fn($arg) => $arg instanceof PhpConstant ? constant($arg->name) : $arg, $case['args']);
        $previousZone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        // Warnings/deprecations don't change what PHP returns; only the return value is compared.
        set_error_handler(fn() => true);
        try {
            $ret = Value::encode($callable(...$args)); // by-ref params write back into $args
            $refs = [];
            foreach ($case['refs'] as $refIndex) {
                $refs[$refIndex] = Value::encode($args[$refIndex] ?? null);
            }
            return ['php' => $ret, 'phpRefs' => $refs];
        } catch (Throwable $e) {
            return ['php' => ['t' => 'error', 'v' => get_class($e) . ': ' . $e->getMessage()], 'phpRefs' => []];
        } finally {
            restore_error_handler();
            date_default_timezone_set($previousZone);
        }
    }

    private static function runNode(array $jobs): array
    {
        if ($jobs === []) {
            return [];
        }
        $process = proc_open(
            ['node', self::NODE_RUNNER],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );
        if (!is_resource($process)) {
            throw new RuntimeException('Parity: can not start node — is it on PATH?');
        }
        fwrite($pipes[0], json_encode(['jobs' => $jobs], JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION));
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);
        if ($exitCode !== 0) {
            throw new RuntimeException("Parity: node runner exited with $exitCode:\n$stderr");
        }
        return json_decode($stdout, true, 512, JSON_THROW_ON_ERROR)['results'];
    }
}
