<?php

namespace Tests\Unit;

use Tests\Support\Parity\ParityRunner;
use Viewi\JsTranspile\RestrictedFunctions;

/**
 * Keeps the parity suite complete: every JS port in functions.php has parity cases or is
 * restricted (RestrictedFunctions: server-only or internal), and the case files only name
 * things that exist.
 * A port added later without cases fails here, not in someone's browser.
 */
class ParityCoverageTest extends \Codeception\Test\Unit
{
    public function testEveryFunctionHasCasesOrIsRestricted(): void
    {
        $covered = ParityRunner::cases() + RestrictedFunctions::all();
        $missing = array_keys(array_diff_key(ParityRunner::functions(), $covered));
        sort($missing);

        $this->assertSame(
            [],
            $missing,
            count($missing) . ' of ' . count(ParityRunner::functions()) . ' functions in functions.php have no parity '
            . 'cases (tests/Support/Data/parity/) and are not in RestrictedFunctions'
        );
    }

    public function testCasesAndRestrictionsNameRealFunctions(): void
    {
        $functions = ParityRunner::functions();
        $this->assertSame([], array_keys(array_diff_key(ParityRunner::cases(), $functions)), 'parity cases for functions that are not in functions.php');
        $this->assertSame([], array_keys(array_diff_key(RestrictedFunctions::all(), $functions)), 'RestrictedFunctions names functions that are not in functions.php');
    }

    public function testServerOnlyFunctionsHaveNoCases(): void
    {
        $serverOnly = array_filter(RestrictedFunctions::all(), fn($entry) => $entry[0] === RestrictedFunctions::SERVER_ONLY);
        $both = array_keys(array_intersect_key(ParityRunner::cases(), $serverOnly));
        $this->assertSame([], $both, 'server-only functions never run in a browser — drop their parity cases');
    }

    public function testEveryPortDependencyExists(): void
    {
        // The Builder throws on a missing dependency, but only once a component calls the port.
        // Checked for every port that can ship: callable ones and whatever they pull in.
        $functions = ParityRunner::functions();
        $shippable = [];
        $visit = function (string $fn) use (&$visit, &$shippable, $functions) {
            if (isset($shippable[$fn]) || !isset($functions[$fn])) {
                return;
            }
            $shippable[$fn] = true;
            foreach ($functions[$fn]::getUses() as $dependency) {
                $visit($dependency);
            }
        };
        foreach ($functions as $fn => $_) {
            if ((RestrictedFunctions::all()[$fn][0] ?? null) !== RestrictedFunctions::SERVER_ONLY) {
                $visit($fn);
            }
        }
        $broken = [];
        foreach ($shippable as $fn => $_) {
            foreach ($functions[$fn]::getUses() as $dependency) {
                if (!isset($functions[$dependency])) {
                    $broken[] = "$fn → $dependency";
                }
            }
        }
        $this->assertSame([], $broken, 'ports that depend on something functions.php does not have');
    }

    public function testRestrictedFunctionsExplainThemselves(): void
    {
        $this->assertStringContainsString('getenv() is server-only', RestrictedFunctions::message('getenv'));
        $this->assertStringContainsString('_php_cast_int() is an internal Viewi helper', RestrictedFunctions::message('_php_cast_int'));
        $this->assertNull(RestrictedFunctions::message('strlen'));
    }

    public function testKnownDifferenceMarkersExist(): void
    {
        $known = ParityRunner::knownDifferences();
        $unknown = [];
        foreach (ParityRunner::cases() as $fn => $cases) {
            foreach ($cases as $case) {
                if ($case['knownDiff'] !== null && !isset($known[$case['knownDiff']])) {
                    $unknown[] = "$fn($case[label]) → '$case[knownDiff]'";
                }
            }
        }
        $this->assertSame([], $unknown, 'cases point at known differences that parity-known-differences.php does not declare');
    }
}
