<?php

namespace Pkg6\VarDumper\Tests;

use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Pkg6\VarDumper\ClosureExporter;

class ClosureExporterTest extends TestCase
{
    public function testRegular(): void
    {
        $exporter = new ClosureExporter();
        $output = $exporter->export(function (int $test): int {
            return 42 + $test;
        });

        $this->assertEquals('function (int $test): int {
            return 42 + $test;
        }', $output);
    }
    public function testStatic(): void
    {
        $exporter = new ClosureExporter();
        $output = $exporter->export(static function (int $test): int {
            return 42 + $test;
        });

        $this->assertEquals('static function (int $test): int {
            return 42 + $test;
        }', $output);
    }
    public function testShort(): void
    {
        $exporter = new ClosureExporter();
        $output = $exporter->export(fn (int $test): int => 42 + $test);

        $this->assertEquals('fn (int $test): int => 42 + $test', $output);
    }
    public function testShortReference(): void
    {
        $exporter = new ClosureExporter();
        $fn = fn (int $test): int => 42 + $test;
        $output = $exporter->export($fn);

        $this->assertEquals('fn (int $test): int => 42 + $test', $output);
    }

    public function testShortStatic(): void
    {
        $exporter = new ClosureExporter();
        $output = $exporter->export(static fn (int $test): int => 42 + $test);

        $this->assertEquals('static fn (int $test): int => 42 + $test', $output);
    }


}