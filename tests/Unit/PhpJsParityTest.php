<?php

namespace Tests\Unit;

use Codeception\Attribute\DataProvider;
use Tests\Support\Parity\ParityRunner;
use Tests\Support\Parity\Value;

/**
 * PHP↔JS parity: each case runs through the PHP function and through Viewi's JS port
 * (src/Viewi/PhpJsFunctions), and the results must match exactly, types included.
 * SSR renders with PHP and hydration re-renders with JS, so any difference here is a page
 * that is right on the server and wrong in the browser.
 *
 * Cases: tests/Support/Data/parity/<Group>.php. Engine: tests/Support/Parity/.
 */
class PhpJsParityTest extends \Codeception\Test\Unit
{
    public static function parityCases(): iterable
    {
        foreach (ParityRunner::cases() as $fn => $cases) {
            foreach ($cases as $index => $case) {
                yield "$fn($case[label])" => [$fn, $index];
            }
        }
    }

    #[DataProvider('parityCases')]
    public function testParity(string $fn, int $index): void
    {
        $case = ParityRunner::cases()[$fn][$index];
        $result = ParityRunner::result($fn, $index);
        $call = "$fn($case[label])"; // Codeception lowercases test names; the message keeps the real call
        if ($result['php']['t'] === 'error') {
            $this->markTestSkipped("PHP throws, so SSR fails before the browser runs: {$result['php']['v']} - drop the case");
        }

        $render = $case['shape']
            ? [Value::class, 'shape']
            : fn(array $tagged) => Value::render($tagged, 0, $case['approx']);
        $expected = [$render($result['php'])];
        $actual = [$render($result['js'])];
        foreach ($result['phpRefs'] as $argIndex => $phpRef) {
            $jsRef = $result['jsRefs'][$argIndex] ?? ['t' => 'error', 'v' => 'JS did not report the argument'];
            $expected[] = "by-reference argument #$argIndex: " . $render($phpRef);
            $actual[] = "by-reference argument #$argIndex: " . $render($jsRef);
        }

        if ($case['knownDiff'] !== null) {
            $this->assertNotSame(
                $expected,
                $actual,
                "$call is marked as known difference '$case[knownDiff]' but PHP and JS now agree - remove the marker"
            );
            return;
        }
        $this->assertSame(
            implode("\n", $expected),
            implode("\n", $actual),
            "$call: PHP and JS disagree (expected = PHP, actual = JS)"
        );
    }
}
