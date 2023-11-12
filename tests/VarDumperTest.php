<?php

namespace Pkg6\VarDumper\Tests;

use PHPUnit\Framework\TestCase;
use Pkg6\VarDumper\ClosureExporter;
use Pkg6\VarDumper\VarDumper;
use ReflectionClass;
use ReflectionException;
use stdClass;

class VarDumperTest extends TestCase
{
    /**
     * @dataProvider exportDataProvider
     *
     * @param mixed $var
     * @param string $expectedResult
     *
     * @throws ReflectionException
     */
    public function testExport($var, string $expectedResult): void
    {
        $exportResult = VarDumper::create($var)->export();
        $this->assertEqualsWithoutLE($expectedResult, $exportResult);
    }

    public function exportDataProvider(): array
    {

        $incompleteObject = unserialize('O:16:"nonExistingClass":0:{}');

        $emptyObject = new stdClass();

        $objectWithReferences1         = new stdClass();
        $objectWithReferences2         = new stdClass();
        $objectWithReferences1->object = $objectWithReferences2;
        $objectWithReferences2->object = $objectWithReferences1;

        $objectWithClosureInProperty = new stdClass();
        // @formatter:off
        $objectWithClosureInProperty->a = fn() => 1;
        // @formatter:on
        $objectWithClosureInPropertyId = spl_object_id($objectWithClosureInProperty);

        return [
            'incomplete object'                    => [
                $incompleteObject,
                <<<S
                unserialize('O:16:"nonExistingClass":0:{}')
                S,
            ],
            'empty object'                         => [
                $emptyObject,
                <<<S
                unserialize('O:8:"stdClass":0:{}')
                S,
            ],
            'function'                             => [
                function () {
                    return 1;
                },
                'function () {
                    return 1;
                }',
            ],
            'string'                               => [
                'Hello, var_dumper!',
                "'Hello, var_dumper!'",
            ],
            'empty string'                         => [
                '',
                "''",
            ],
            'null'                                 => [
                null,
                'null',
            ],
            'integer'                              => [
                1,
                '1',
            ],
            'integer with separator'               => [
                1_23_456,
                '123456',
            ],
            'boolean'                              => [
                true,
                'true',
            ],
            'resource'                             => [
                fopen('php://input', 'rb'),
                'NULL',
            ],
            'empty array'                          => [
                [],
                '[]',
            ],
            'array of 3 elements, automatic keys'  => [
                [
                    'one',
                    'two',
                    'three',
                ],
                <<<S
                [
                    'one',
                    'two',
                    'three',
                ]
                S,
            ],
            'array of 3 elements, custom keys'     => [
                [
                    2     => 'one',
                    'two' => 'two',
                    0     => 'three',
                ],
                <<<S
                [
                    2 => 'one',
                    'two' => 'two',
                    0 => 'three',
                ]
                S,
            ],
            'object with references'               => [
                $objectWithReferences1,
                <<<S
                unserialize('O:8:"stdClass":1:{s:6:"object";O:8:"stdClass":1:{s:6:"object";r:1;}}')
                S,
            ],
            'utf8 supported'                       => [
                '🤣',
                "'🤣'",
            ],
        ];
    }

    /**
     * @dataProvider exportWithoutFormattingDataProvider
     *
     * @param mixed $var
     * @param string $expectedResult
     *
     * @throws ReflectionException
     */
    public function testExportWithoutFormatting($var, string $expectedResult): void
    {
        $exportResult = VarDumper::create($var)->export(false);
        $this->assertEqualsWithoutLE($expectedResult, $exportResult);
    }

    public function exportWithoutFormattingDataProvider(): array
    {
        return [
            'function'                             => [
                function () {
                    return 1;
                },
                'function () {
                    return 1;
                }',
            ],
            'static function'                      => [
                static function () {
                    return 1;
                },
                'static function () {
                    return 1;
                }',
            ],
            'string'                               => [
                'Hello, Yii!',
                "'Hello, Yii!'",
            ],
            'empty string'                         => [
                '',
                "''",
            ],
            'null'                                 => [
                null,
                'null',
            ],
            'integer'                              => [
                1,
                '1',
            ],
            'integer with separator'               => [
                1_23_456,
                '123456',
            ],
            'boolean'                              => [
                true,
                'true',
            ],
            'resource'                             => [
                fopen('php://input', 'rb'),
                'NULL',
            ],
            'empty array'                          => [
                [],
                '[]',
            ],
            'array of 3 elements'                  => [
                [
                    'one',
                    'two',
                    'three',
                ],
                "['one','two','three']",
            ],
            'array of 3 elements, custom keys'     => [
                [
                    2     => 'one',
                    'two' => 'two',
                    0     => 'three',
                ],
                "[2 => 'one','two' => 'two',0 => 'three']",
            ],
        ];
    }

    /**
     * @dataProvider exportWithObjectSerializationFailDataProvider
     *
     * @param object $object
     * @param string $expectedResult
     *
     * @throws ReflectionException|\ReflectionException
     */
    public function testExportWithObjectSerializationFail(object $object, string $expectedResult): void
    {
        $exportResult = VarDumper::create($object)->export();
        $this->assertEqualsWithoutLE($expectedResult, $exportResult);
    }

    public function exportWithObjectSerializationFailDataProvider(): array
    {
        return [
            'Anonymous-instance' => [
                $object = new class () {
                },
                var_export(VarDumper::create($object)->asString(), true),
            ],
        ];
    }


    public function testExportClosureWithAnImmutableInstanceOfClosureExporter(): void
    {
        $varDumper1       = VarDumper::create(fn(): int => 1);
        $reflection1      = new ReflectionClass($varDumper1);
        $closureExporter1 = $reflection1->getStaticPropertyValue('closureExporter');

        $this->assertInstanceOf(ClosureExporter::class, $closureExporter1);
        $this->assertSame(
            $closureExporter1,
            (new ReflectionClass($varDumper1))->getStaticPropertyValue('closureExporter'),
        );

        $varDumper2       = VarDumper::create(fn(): int => 2);
        $reflection2      = new ReflectionClass($varDumper2);
        $closureExporter2 = $reflection2->getStaticPropertyValue('closureExporter');

        $this->assertInstanceOf(ClosureExporter::class, $closureExporter2);
        $this->assertSame($closureExporter1, $closureExporter2);
    }


    public function testDFunction(): void
    {
        d($variable = 'content');
        $this->expectOutputString("'{$variable}'" . PHP_EOL);
    }

    public function testDFunctionWithMultipleVariables(): void
    {
        d([], 123, true);
        $this->expectOutputString('[]' . PHP_EOL . '123' . PHP_EOL . 'true' . PHP_EOL);
    }

    public function testDumpWithHighlight(): void
    {
        $var    = 'content';
        $result = highlight_string("<?php\n'{$var}'", true);
        $output = preg_replace('/&lt;\\?php<br \\/>/', '', $result, 1);
        VarDumper::dump($var);
        $this->expectOutputString($output);
    }

    public function testDumpWithOutHighlight(): void
    {
        $var = 'content';
        VarDumper::dump($var, 10, false);
        $this->expectOutputString("'{$var}'");
    }

    public function testDumpWithoutDepthForArray(): void
    {
        VarDumper::dump(['content'], 0, false);
        $this->expectOutputString('[...]');
    }

    /**
     * Asserting two strings equality ignoring line endings.
     *
     * @param string $expected
     * @param string $actual
     * @param string $message
     */
    private function assertEqualsWithoutLE(string $expected, string $actual, string $message = ''): void
    {
        $expected = str_replace(["\r\n", '\r\n'], ["\n", '\n'], $expected);
        $actual   = str_replace(["\r\n", '\r\n'], ["\n", '\n'], $actual);
        $this->assertEquals($expected, $actual, $message);
    }
}