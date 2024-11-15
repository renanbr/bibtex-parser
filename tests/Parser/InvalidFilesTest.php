<?php

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser\Test\Parser;

use ErrorException;
use PHPUnit\Framework\TestCase;
use RenanBr\BibTexParser\Exception\ParserException;
use RenanBr\BibTexParser\Parser;

/**
 * @covers \RenanBr\BibTexParser\Parser
 */
class InvalidFilesTest extends TestCase
{
    public function testInexistentFileMustTriggerWarning()
    {
        $parser = new Parser();

        $this->expectException(ErrorException::class);

        $parser->parseFile(__DIR__.'/../resources/valid/does-not-exist');
    }

    /** @dataProvider invalidFileProvider */
    public function testInvalidInputMustCauseException($file, $message)
    {
        $parser = new Parser();

        $this->expectException(ParserException::class);
        $this->expectExceptionMessage($message);
        $parser->parseFile($file);
    }

    public static function invalidFileProvider()
    {
        $dir = __DIR__.'/../resources/invalid';

        return [
            'brace missing' => [
                $dir.'/brace-missing.bib',
                "'\\0' at line 3 column 1",
            ],
            'multiple braced values' => [
                $dir.'/multiple-braced-tag-contents.bib',
                "'{' at line 2 column 33",
            ],
            'multiple quoted values' => [
                $dir.'/multiple-quoted-tag-contents.bib',
                "'\"' at line 2 column 33",
            ],
            'multiple raw values' => [
                $dir.'/multiple-raw-tag-contents.bib',
                "'b' at line 2 column 31",
            ],
            'space after @' => [
                $dir.'/space-after-at-sign.bib',
                "' ' at line 1 column 2",
            ],
            'splitted tag name' => [
                $dir.'/splitted-tag-name.bib',
                "'t' at line 2 column 14",
            ],
            'splitted type' => [
                $dir.'/splitted-type.bib',
                "'T' at line 1 column 11",
            ],
            'double concat' => [
                $dir.'/double-concat.bib',
                "'#' at line 2 column 20",
            ],
        ];
    }

    /**
     * @group regression
     * @group bug40
     *
     * @see https://github.com/renanbr/bibtex-parser/issues/40
     */
    public function testInvalidCharBeforeTagContentMustThrowAnException()
    {
        $parser = new Parser();

        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("')' at line 1 column 11");
        $parser->parseString('@misc{foo=)"bar"}');
    }
}
