<?php

namespace Pkg6\VarDumper\Tests;

use PHPUnit\Framework\TestCase;
use Pkg6\VarDumper\UseStatementParser;
use RuntimeException;

class UseStatementParserTest extends TestCase
{
    public function incorrectFileProvider(): array
    {
        return [
            'non-exists-file' => ['non-exists-file'],
            'directory'       => [__DIR__],
        ];
    }

    /**
     * @dataProvider incorrectFileProvider
     *
     * @param string $file
     */
    public function testIncorrectFile(string $file): void
    {
        $parser = new UseStatementParser();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("File \"{$file}\" does not exist.");
        $parser->fromFile($file);
    }

    public function testNotReadable(): void
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            self::markTestSkipped('Skip on OS Windows');
        }

        $parser   = new UseStatementParser();
        $file     = tmpfile();
        $filename = stream_get_meta_data($file)['uri'];
        chmod($filename, 0333);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("File \"{$filename}\" is not readable.");
        $parser->fromFile($filename);
    }
}