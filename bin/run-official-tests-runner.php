<?php

declare(strict_types=1);

/**
 * PHP runner for the official json-logic test suite.
 *
 * Called by run-official-tests.sh for each test file.
 *
 * Arguments:
 *   $argv[1]  project root (where vendor/autoload.php lives)
 *   $argv[2]  test file name shown in output (e.g. "legacy.json")
 *   $argv[3]  verbose flag: "1" to print PASS lines too
 *
 * Input:  JSON test file content on STDIN
 * Output: PASS/FAIL lines, then a final "SUMMARY pass=N fail=N" line
 * Exit:   1 if any test failed, 0 otherwise
 */

$projectDir = $argv[1];
$filename   = $argv[2];
$verbose    = isset($argv[3]) && $argv[3] === '1';

require $projectDir . '/vendor/autoload.php';

use ShinyJsonLogic\ShinyJsonLogic;
use ShinyJsonLogic\Errors\JsonLogicException as ErrorBase;

$json  = stream_get_contents(STDIN);
$cases = json_decode($json, true);

$pass = 0;
$fail = 0;

foreach ($cases as $i => $case) {
    if (!is_array($case)) {
        continue; // skip comment strings
    }

    $description    = $case['description'] ?? "case #$i";
    $rule           = $case['rule']   ?? null;
    $data           = $case['data']   ?? null;
    $expectedResult = $case['result'] ?? null;
    $expectedError  = $case['error']  ?? null;

    try {
        $result = ShinyJsonLogic::apply($rule, $data);

        if ($expectedError !== null) {
            $fail++;
            echo "  FAIL [$filename] $description\n";
            echo "       Expected error \"{$expectedError['type']}\" but no exception was thrown\n";
            continue;
        }

        // Normalise via JSON round-trip so PHP int/float differences don't
        // cause false failures (e.g. apply returns int 2, fixture has float 2.0)
        $got      = json_decode(json_encode($result), true);
        $expected = json_decode(json_encode($expectedResult), true);

        if ($got === $expected) {
            $pass++;
            if ($verbose) {
                echo "  PASS [$filename] $description\n";
            }
        } else {
            $fail++;
            echo "  FAIL [$filename] $description\n";
            echo '       got:      ' . json_encode($got) . "\n";
            echo '       expected: ' . json_encode($expected) . "\n";
        }

    } catch (ErrorBase $e) {
        if ($expectedError !== null) {
            $gotType = $e->getErrorType();
            if ($gotType === $expectedError['type']) {
                $pass++;
                if ($verbose) {
                    echo "  PASS [$filename] $description\n";
                }
            } else {
                $fail++;
                echo "  FAIL [$filename] $description\n";
                echo "       error type got:      \"$gotType\"\n";
                echo "       error type expected: \"{$expectedError['type']}\"\n";
            }
        } else {
            $fail++;
            echo "  FAIL [$filename] $description\n";
            echo '       Unexpected ' . get_class($e) . ': ' . $e->getMessage() . "\n";
        }
    } catch (\Throwable $e) {
        $fail++;
        echo "  FAIL [$filename] $description\n";
        echo '       Unexpected ' . get_class($e) . ': ' . $e->getMessage() . "\n";
    }
}

echo "SUMMARY pass=$pass fail=$fail\n";
exit($fail > 0 ? 1 : 0);
