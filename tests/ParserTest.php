<?php

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser\Test;

use PHPUnit\Framework\TestCase;
use RenanBr\BibTexParser\Parser;
use RenanBr\BibTexParser\ParseException;

class ParserTest extends TestCase
{
    public function testFileDoesNotExist()
    {
        $parser = new Parser;

        // Keeps compatibility with phpunit 5 and 6
        $warningClass = class_exists(\PHPUnit_Framework_Error_Warning::class)
            ? \PHPUnit_Framework_Error_Warning::class
            : \PHPUnit\Framework\Error\Warning::class;
        $this->expectException($warningClass);

        $parser->parseFile(__DIR__ . '/resources/valid/does-not-exist');
    }

    public function testBasic()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/valid/basic.bib');

        $this->assertCount(4, $listener->calls);

        list($text, $context) = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $context['state']);
        $this->assertSame('basic', $text);
        $this->assertSame(1, $context['offset']);
        $this->assertSame(5, $context['length']);

        list($text, $context) = $listener->calls[1];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame('foo', $text);
        $this->assertSame(13, $context['offset']);
        $this->assertSame(3, $context['length']);

        list($text, $context) = $listener->calls[2];
        $this->assertSame(Parser::RAW_VALUE, $context['state']);
        $this->assertSame('bar', $text);
        $this->assertSame(19, $context['offset']);
        $this->assertSame(3, $context['length']);

        list($text, $context) = $listener->calls[3];
        $this->assertSame(Parser::ORIGINAL_ENTRY, $context['state']);
        $original = trim(file_get_contents(__DIR__ . '/resources/valid/basic.bib'));
        $this->assertSame($original, $text);
        $this->assertSame(0, $context['offset']);
        $this->assertSame(24, $context['length']);
    }

    public function testKeyWithoutValue()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/valid/no-value.bib');

        $this->assertCount(4, $listener->calls);

        list($text, $context) = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $context['state']);
        $this->assertSame('noValue', $text);

        list($text, $context) = $listener->calls[1];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame('foo', $text);

        list($text, $context) = $listener->calls[2];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame('bar', $text);

        list($text, $context) = $listener->calls[3];
        $this->assertSame(Parser::ORIGINAL_ENTRY, $context['state']);
        $original = trim(file_get_contents(__DIR__ . '/resources/valid/no-value.bib'));
        $this->assertSame($original, $text);
    }

    public function testValueReading()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/valid/values-basic.bib');

        $this->assertCount(14, $listener->calls);

        list($text, $context) = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $context['state']);
        $this->assertSame('valuesBasic', $text);

        list($text, $context) = $listener->calls[1];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame('kNull', $text);

        list($text, $context) = $listener->calls[2];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame('kStillNull', $text);

        list($text, $context) = $listener->calls[3];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame('kRaw', $text);

        list($text, $context) = $listener->calls[4];
        $this->assertSame(Parser::RAW_VALUE, $context['state']);
        $this->assertSame('raw', $text);

        list($text, $context) = $listener->calls[5];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame('kBraced', $text);

        list($text, $context) = $listener->calls[6];
        $this->assertSame(Parser::BRACED_VALUE, $context['state']);
        $this->assertSame(' braced value ', $text);

        list($text, $context) = $listener->calls[7];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame('kBracedEmpty', $text);

        list($text, $context) = $listener->calls[8];
        $this->assertSame(Parser::BRACED_VALUE, $context['state']);
        $this->assertSame('', $text);

        list($text, $context) = $listener->calls[9];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame('kQuoted', $text);

        list($text, $context) = $listener->calls[10];
        $this->assertSame(Parser::QUOTED_VALUE, $context['state']);
        $this->assertSame(' quoted value ', $text);

        list($text, $context) = $listener->calls[11];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame('kQuotedEmpty', $text);

        list($text, $context) = $listener->calls[12];
        $this->assertSame(Parser::QUOTED_VALUE, $context['state']);
        $this->assertSame('', $text);

        list($text, $context) = $listener->calls[13];
        $this->assertSame(Parser::ORIGINAL_ENTRY, $context['state']);
        $original = trim(file_get_contents(__DIR__ . '/resources/valid/values-basic.bib'));
        $this->assertSame($original, $text);
    }

    public function testValueScaping()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/valid/values-escaped.bib');

        $this->assertCount(6, $listener->calls);

        // we test also the "offset" and "length" because this file contains
        // values with escaped chars, which means that the value length in the
        // file is not equal to the triggered one

        list($text, $context) = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $context['state']);
        $this->assertSame('valuesEscaped', $text);
        $this->assertSame(1, $context['offset']);
        $this->assertSame(13, $context['length']);

        list($text, $context) = $listener->calls[1];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame('braced', $text);
        $this->assertSame(21, $context['offset']);
        $this->assertSame(6, $context['length']);

        list($text, $context) = $listener->calls[2];
        $this->assertSame(Parser::BRACED_VALUE, $context['state']);
        // here we have two scaped characters ("}" and "%"), then the length
        // returned in the context (21) is bigger than the $text value (18)
        $this->assertSame('the } " \\ % braced', $text);
        $this->assertSame(31, $context['offset']);
        $this->assertSame(21, $context['length']);

        list($text, $context) = $listener->calls[3];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame('quoted', $text);
        $this->assertSame(59, $context['offset']);
        $this->assertSame(6, $context['length']);

        list($text, $context) = $listener->calls[4];
        $this->assertSame(Parser::QUOTED_VALUE, $context['state']);
        // here we have two scaped characters ("}" and "%"), then the length
        // returned in the context (21) is bigger than the $text value (18)
        $this->assertSame('the } " \\ % quoted', $text);
        $this->assertSame(69, $context['offset']);
        $this->assertSame(21, $context['length']);

        list($text, $context) = $listener->calls[5];
        $this->assertSame(Parser::ORIGINAL_ENTRY, $context['state']);
        $original = trim(file_get_contents(__DIR__ . '/resources/valid/values-escaped.bib'));
        $this->assertSame($original, $text);
        $this->assertSame(0, $context['offset']);
        $this->assertSame(93, $context['length']);
    }

    public function testMultipleValues()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/valid/values-multiple.bib');

        $this->assertCount(19, $listener->calls);

        list($text, $context) = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $context['state']);
        $this->assertSame('multipleValues', $text);

        list($text, $context) = $listener->calls[1];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame('raw', $text);

        list($text, $context) = $listener->calls[2];
        $this->assertSame(Parser::RAW_VALUE, $context['state']);
        $this->assertSame('rawA', $text);

        list($text, $context) = $listener->calls[3];
        $this->assertSame(Parser::RAW_VALUE, $context['state']);
        $this->assertSame('rawB', $text);

        list($text, $context) = $listener->calls[4];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame('quoted', $text);

        list($text, $context) = $listener->calls[5];
        $this->assertSame(Parser::QUOTED_VALUE, $context['state']);
        $this->assertSame('quoted a', $text);

        list($text, $context) = $listener->calls[6];
        $this->assertSame(Parser::QUOTED_VALUE, $context['state']);
        $this->assertSame('quoted b', $text);

        list($text, $context) = $listener->calls[7];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame('braced', $text);

        list($text, $context) = $listener->calls[8];
        $this->assertSame(Parser::BRACED_VALUE, $context['state']);
        $this->assertSame('braced a', $text);

        list($text, $context) = $listener->calls[9];
        $this->assertSame(Parser::BRACED_VALUE, $context['state']);
        $this->assertSame('braced b', $text);

        list($text, $context) = $listener->calls[10];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame('misc', $text);

        list($text, $context) = $listener->calls[11];
        $this->assertSame(Parser::QUOTED_VALUE, $context['state']);
        $this->assertSame('quoted', $text);

        list($text, $context) = $listener->calls[12];
        $this->assertSame(Parser::BRACED_VALUE, $context['state']);
        $this->assertSame('braced', $text);

        list($text, $context) = $listener->calls[13];
        $this->assertSame(Parser::RAW_VALUE, $context['state']);
        $this->assertSame('raw', $text);

        list($text, $context) = $listener->calls[14];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame('noSpace', $text);

        list($text, $context) = $listener->calls[15];
        $this->assertSame(Parser::RAW_VALUE, $context['state']);
        $this->assertSame('raw', $text);

        list($text, $context) = $listener->calls[16];
        $this->assertSame(Parser::QUOTED_VALUE, $context['state']);
        $this->assertSame('quoted', $text);

        list($text, $context) = $listener->calls[17];
        $this->assertSame(Parser::BRACED_VALUE, $context['state']);
        $this->assertSame('braced', $text);

        list($text, $context) = $listener->calls[18];
        $this->assertSame(Parser::ORIGINAL_ENTRY, $context['state']);
        $original = trim(file_get_contents(__DIR__ . '/resources/valid/values-multiple.bib'));
        $this->assertSame($original, $text);
    }

    public function testValueSlashes()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/valid/values-slashes.bib');

        $this->assertCount(6, $listener->calls);

        list($text, $context) = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $context['state']);
        $this->assertSame('valuesSlashes', $text);

        list($text, $context) = $listener->calls[1];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame('braced', $text);

        list($text, $context) = $listener->calls[2];
        $this->assertSame(Parser::BRACED_VALUE, $context['state']);
        $this->assertSame('\\}\\"\\%\\', $text);

        list($text, $context) = $listener->calls[3];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame('quoted', $text);

        list($text, $context) = $listener->calls[4];
        $this->assertSame(Parser::QUOTED_VALUE, $context['state']);
        $this->assertSame('\\}\\"\\%\\', $text);

        list($text, $context) = $listener->calls[5];
        $this->assertSame(Parser::ORIGINAL_ENTRY, $context['state']);
        $original = trim(file_get_contents(__DIR__ . '/resources/valid/values-slashes.bib'));
        $this->assertSame($original, $text);
    }

    public function testValueNestedBraces()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/valid/values-nested-braces.bib');

        $this->assertCount(8, $listener->calls);

        list($text, $context) = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $context['state']);
        $this->assertSame('valuesBraces', $text);

        list($text, $context) = $listener->calls[1];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame('link', $text);

        list($text, $context) = $listener->calls[2];
        $this->assertSame(Parser::BRACED_VALUE, $context['state']);
        $this->assertSame('\url{https://github.com}', $text);

        list($text, $context) = $listener->calls[3];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame('twoLevels', $text);

        list($text, $context) = $listener->calls[4];
        $this->assertSame(Parser::BRACED_VALUE, $context['state']);
        $this->assertSame('a{b{c}d}e', $text);

        list($text, $context) = $listener->calls[5];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame('escapedBrace', $text);

        list($text, $context) = $listener->calls[6];
        $this->assertSame(Parser::BRACED_VALUE, $context['state']);
        $this->assertSame('before{}}after', $text);

        list($text, $context) = $listener->calls[7];
        $this->assertSame(Parser::ORIGINAL_ENTRY, $context['state']);
        $original = trim(file_get_contents(__DIR__ . '/resources/valid/values-nested-braces.bib'));
        $this->assertSame($original, $text);
    }

    public function testTrailingComma()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/valid/trailing-comma.bib');

        $this->assertCount(4, $listener->calls);

        list($text, $context) = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $context['state']);
        $this->assertSame('trailingComma', $text);

        list($text, $context) = $listener->calls[1];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame('foo', $text);

        list($text, $context) = $listener->calls[2];
        $this->assertSame(Parser::RAW_VALUE, $context['state']);
        $this->assertSame('bar', $text);

        list($text, $context) = $listener->calls[3];
        $this->assertSame(Parser::ORIGINAL_ENTRY, $context['state']);
        $original = trim(file_get_contents(__DIR__ . '/resources/valid/trailing-comma.bib'));
        $this->assertSame($original, $text);
    }

    public function testTagNameWithUnderscore()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/valid/tag-name-with-underscore.bib');

        $this->assertCount(4, $listener->calls);

        list($text, $context) = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $context['state']);
        $this->assertSame('tagNameWithUnderscore', $text);

        list($text, $context) = $listener->calls[1];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame('foo_bar', $text);

        list($text, $context) = $listener->calls[2];
        $this->assertSame(Parser::RAW_VALUE, $context['state']);
        $this->assertSame('fubar', $text);

        list($text, $context) = $listener->calls[3];
        $this->assertSame(Parser::ORIGINAL_ENTRY, $context['state']);
        $original = trim(file_get_contents(__DIR__ . '/resources/valid/tag-name-with-underscore.bib'));
        $this->assertSame($original, $text);
    }

    public function testMultipleEntries()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/valid/multiples-entries.bib');

        $this->assertCount(8, $listener->calls);

        list($text, $context) = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $context['state']);
        $this->assertSame('entryFooWithSpaces', $text);

        list($text, $context) = $listener->calls[1];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame('foo', $text);

        list($text, $context) = $listener->calls[2];
        $this->assertSame(Parser::RAW_VALUE, $context['state']);
        $this->assertSame('oof', $text);

        list($text, $context) = $listener->calls[3];
        $this->assertSame(Parser::ORIGINAL_ENTRY, $context['state']);
        $this->assertSame('@entryFooWithSpaces { foo = oof }', $text);

        list($text, $context) = $listener->calls[4];
        $this->assertSame(Parser::TYPE, $context['state']);
        $this->assertSame('entryBarWithoutSpaces', $text);

        list($text, $context) = $listener->calls[5];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame('bar', $text);

        list($text, $context) = $listener->calls[6];
        $this->assertSame(Parser::RAW_VALUE, $context['state']);
        $this->assertSame('rab', $text);

        list($text, $context) = $listener->calls[7];
        $this->assertSame(Parser::ORIGINAL_ENTRY, $context['state']);
        $this->assertSame('@entryBarWithoutSpaces{bar=rab}', $text);
    }

    /**
     * @dataProvider validFileProvider
     */
    public function testStringParserAndFileParserMustWorksIdentically($file)
    {
        $listenerFile = new DummyListener;
        $parserFile = new Parser;
        $parserFile->addListener($listenerFile);
        $parserFile->parseFile($file);

        $listenerString = new DummyListener;
        $parserString = new Parser;
        $parserString->addListener($listenerString);
        $parserString->parseString(file_get_contents($file));

        $this->assertSame($listenerFile->calls, $listenerString->calls);
    }

    public function validFileProvider()
    {
        $dir = __DIR__ . '/resources/valid';

        return [
            [$dir . '/abbreviation.bib'],
            [$dir . '/basic.bib'],
            [$dir . '/citation-key.bib'],
            [$dir . '/multiples-entries.bib'],
            [$dir . '/no-value.bib'],
            [$dir . '/tag-name-uppercased.bib'],
            [$dir . '/tag-name-with-underscore.bib'],
            [$dir . '/trailing-comma.bib'],
            [$dir . '/type-overriding.bib'],
            [$dir . '/values-basic.bib'],
            [$dir . '/values-escaped.bib'],
            [$dir . '/values-multiple.bib'],
            [$dir . '/values-nested-braces.bib'],
            [$dir . '/values-slashes.bib'],
        ];
    }

    /**
     * @dataProvider invalidFileProvider
     */
    public function testInvalidInputMustCauseException($file, $message)
    {
        $parser = new Parser;

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage($message);
        $parser->parseFile($file);
    }

    public function invalidFileProvider()
    {
        $dir = __DIR__ . '/resources/invalid';

        return [
            [$dir . '/brace-missing.bib', "'\\0' at line 3 column 1"],
            [$dir . '/multiple-braced-values.bib', "'{' at line 2 column 33"],
            [$dir . '/multiple-quoted-values.bib', "'\"' at line 2 column 33"],
            [$dir . '/multiple-raw-values.bib', "'b' at line 2 column 31"],
            [$dir . '/space-after-at-sign.bib', "' ' at line 1 column 2"],
            [$dir . '/splitted-key.bib', "'k' at line 2 column 14"],
            [$dir . '/splitted-type.bib', "'T' at line 1 column 11"],
            [$dir . '/double-concat.bib', "'#' at line 2 column 20"],
        ];
    }
}
