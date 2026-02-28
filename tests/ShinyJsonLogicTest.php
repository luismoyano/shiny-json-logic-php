<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Tests;

use PHPUnit\Framework\TestCase;
use ShinyJsonLogic\ShinyJsonLogic;
use ShinyJsonLogic\Errors\JsonLogicException as ErrorBase;

class ShinyJsonLogicTest extends TestCase
{
    public function testApplyReturnsSimpleValue(): void
    {
        $this->assertEquals(3, ShinyJsonLogic::apply(['+' => [1, 2]]));
    }

    public function testJsonLogicAlias(): void
    {
        $this->assertEquals(3, \JsonLogic::apply(['+' => [1, 2]]));
    }

    public function testJSONLogicAliasWorks(): void
    {
        $this->assertEquals(3, \JSONLogic::apply(['+' => [1, 2]]));
    }

    /**
     * @dataProvider shinyTestCases
     */
    public function testShinyCase(
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
                $this->assertEquals($expectedError['type'], $e->getErrorType(), $description);
            }
        } else {
            $result = ShinyJsonLogic::apply($rule, $data);
            $this->assertEquals($expectedResult, $result, $description);
        }
    }

    public static function shinyTestCases(): array
    {
        $path = __DIR__ . '/fixtures/shiny_tests.json';
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
