<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Tests;

use PHPUnit\Framework\TestCase;
use ShinyJsonLogic\ShinyJsonLogic;
use ShinyJsonLogic\Errors\JsonLogicException as ErrorBase;

class OfficialTest extends TestCase
{
    /**
     * @dataProvider officialTestCases
     */
    public function testOfficialCase(
        string $description,
        mixed $rule,
        mixed $data,
        mixed $expectedResult,
        mixed $expectedError
    ): void {
        if ($expectedError !== null) {
            try {
                ShinyJsonLogic::apply($rule, $data);
                $this->fail("Expected an error to be thrown for: $description");
            } catch (ErrorBase $e) {
                $this->assertEquals($expectedError, $e->payload(), $description);
            }
        } else {
            $result = ShinyJsonLogic::apply($rule, $data);
            $this->assertEquals($expectedResult, $result, $description);
        }
    }

    public static function officialTestCases(): array
    {
        $path = __DIR__ . '/fixtures/tests_v2.json';
        $cases = json_decode(file_get_contents($path), true);

        $tests = [];
        foreach ($cases as $index => $testcase) {
            if (!is_array($testcase)) {
                continue; // skip comment strings
            }
            $description = $testcase['description'] ?? "case #$index";
            $tests[$description] = [
                $description,
                $testcase['rule'] ?? null,
                $testcase['data'] ?? null,
                $testcase['result'] ?? null,
                $testcase['error'] ?? null,
            ];
        }
        return $tests;
    }
}
