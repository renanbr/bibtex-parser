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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use RenanBr\BibTexParser\Parser;
use RenanBr\BibTexParser\Test\DummyListener;

#[CoversClass(Parser::class)]
class ValueParsingTest extends TestCase
{
    /**
     * Tests if parser is able to handle raw, null, braced and quoted values ate the same time.
     */
    public function testMultipleNature(): void
    {
        $listener = new DummyListener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/tag-contents-basic.bib');

        $this->assertCount(14, $listener->calls);

        [$text, $type, $context] = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $type);
        $this->assertSame('valuesBasic', $text);

        [$text, $type, $context] = $listener->calls[1];
        $this->assertSame(Parser::CITATION_KEY, $type);
        $this->assertSame('kNull', $text);

        [$text, $type, $context] = $listener->calls[2];
        $this->assertSame(Parser::TAG_NAME, $type);
        $this->assertSame('kStillNull', $text);

        [$text, $type, $context] = $listener->calls[3];
        $this->assertSame(Parser::TAG_NAME, $type);
        $this->assertSame('kRaw', $text);

        [$text, $type, $context] = $listener->calls[4];
        $this->assertSame(Parser::RAW_TAG_CONTENT, $type);
        $this->assertSame('raw', $text);

        [$text, $type, $context] = $listener->calls[5];
        $this->assertSame(Parser::TAG_NAME, $type);
        $this->assertSame('kBraced', $text);

        [$text, $type, $context] = $listener->calls[6];
        $this->assertSame(Parser::BRACED_TAG_CONTENT, $type);
        $this->assertSame(' braced value ', $text);

        [$text, $type, $context] = $listener->calls[7];
        $this->assertSame(Parser::TAG_NAME, $type);
        $this->assertSame('kBracedEmpty', $text);

        [$text, $type, $context] = $listener->calls[8];
        $this->assertSame(Parser::BRACED_TAG_CONTENT, $type);
        $this->assertSame('', $text);

        [$text, $type, $context] = $listener->calls[9];
        $this->assertSame(Parser::TAG_NAME, $type);
        $this->assertSame('kQuoted', $text);

        [$text, $type, $context] = $listener->calls[10];
        $this->assertSame(Parser::QUOTED_TAG_CONTENT, $type);
        $this->assertSame(' quoted value ', $text);

        [$text, $type, $context] = $listener->calls[11];
        $this->assertSame(Parser::TAG_NAME, $type);
        $this->assertSame('kQuotedEmpty', $text);

        [$text, $type, $context] = $listener->calls[12];
        $this->assertSame(Parser::QUOTED_TAG_CONTENT, $type);
        $this->assertSame('', $text);

        [$text, $type, $context] = $listener->calls[13];
        $this->assertSame(Parser::ENTRY, $type);
        $original = trim(file_get_contents(__DIR__ . '/../resources/valid/tag-contents-basic.bib'));
        $this->assertSame($original, $text);
    }

    public function testTagContentScaping(): void
    {
        $listener = new DummyListener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/tag-contents-escaped.bib');

        $this->assertCount(6, $listener->calls);

        // we test also the "offset" and "length" because this file contains
        // values with escaped chars, which means that the value length in the
        // file is not equal to the triggered one

        [$text, $type, $context] = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $type);
        $this->assertSame('valuesEscaped', $text);
        $this->assertSame(1, $context['offset']);
        $this->assertSame(13, $context['length']);

        [$text, $type, $context] = $listener->calls[1];
        $this->assertSame(Parser::TAG_NAME, $type);
        $this->assertSame('braced', $text);
        $this->assertSame(21, $context['offset']);
        $this->assertSame(6, $context['length']);

        [$text, $type, $context] = $listener->calls[2];
        $this->assertSame(Parser::BRACED_TAG_CONTENT, $type);
        // here we have two scaped characters ("}" and "%"), then the length
        // returned in the context (21) is bigger than the $text value (18)
        $this->assertSame('the } " \\ % braced', $text);
        $this->assertSame(31, $context['offset']);
        $this->assertSame(21, $context['length']);

        [$text, $type, $context] = $listener->calls[3];
        $this->assertSame(Parser::TAG_NAME, $type);
        $this->assertSame('quoted', $text);
        $this->assertSame(59, $context['offset']);
        $this->assertSame(6, $context['length']);

        [$text, $type, $context] = $listener->calls[4];
        $this->assertSame(Parser::QUOTED_TAG_CONTENT, $type);
        // here we have two scaped characters ("}" and "%"), then the length
        // returned in the context (21) is bigger than the $text value (18)
        $this->assertSame('the } " \\ % quoted', $text);
        $this->assertSame(69, $context['offset']);
        $this->assertSame(21, $context['length']);

        [$text, $type, $context] = $listener->calls[5];
        $this->assertSame(Parser::ENTRY, $type);
        $original = trim(file_get_contents(__DIR__ . '/../resources/valid/tag-contents-escaped.bib'));
        $this->assertSame($original, $text);
        $this->assertSame(0, $context['offset']);
        $this->assertSame(93, $context['length']);
    }

    public function testMultipleTagContents(): void
    {
        $listener = new DummyListener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/tag-contents-multiple.bib');

        $this->assertCount(19, $listener->calls);

        [$text, $type, $context] = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $type);
        $this->assertSame('multipleTagContents', $text);

        [$text, $type, $context] = $listener->calls[1];
        $this->assertSame(Parser::TAG_NAME, $type);
        $this->assertSame('raw', $text);

        [$text, $type, $context] = $listener->calls[2];
        $this->assertSame(Parser::RAW_TAG_CONTENT, $type);
        $this->assertSame('rawA', $text);

        [$text, $type, $context] = $listener->calls[3];
        $this->assertSame(Parser::RAW_TAG_CONTENT, $type);
        $this->assertSame('rawB', $text);

        [$text, $type, $context] = $listener->calls[4];
        $this->assertSame(Parser::TAG_NAME, $type);
        $this->assertSame('quoted', $text);

        [$text, $type, $context] = $listener->calls[5];
        $this->assertSame(Parser::QUOTED_TAG_CONTENT, $type);
        $this->assertSame('quoted a', $text);

        [$text, $type, $context] = $listener->calls[6];
        $this->assertSame(Parser::QUOTED_TAG_CONTENT, $type);
        $this->assertSame('quoted b', $text);

        [$text, $type, $context] = $listener->calls[7];
        $this->assertSame(Parser::TAG_NAME, $type);
        $this->assertSame('braced', $text);

        [$text, $type, $context] = $listener->calls[8];
        $this->assertSame(Parser::BRACED_TAG_CONTENT, $type);
        $this->assertSame('braced a', $text);

        [$text, $type, $context] = $listener->calls[9];
        $this->assertSame(Parser::BRACED_TAG_CONTENT, $type);
        $this->assertSame('braced b', $text);

        [$text, $type, $context] = $listener->calls[10];
        $this->assertSame(Parser::TAG_NAME, $type);
        $this->assertSame('misc', $text);

        [$text, $type, $context] = $listener->calls[11];
        $this->assertSame(Parser::QUOTED_TAG_CONTENT, $type);
        $this->assertSame('quoted', $text);

        [$text, $type, $context] = $listener->calls[12];
        $this->assertSame(Parser::BRACED_TAG_CONTENT, $type);
        $this->assertSame('braced', $text);

        [$text, $type, $context] = $listener->calls[13];
        $this->assertSame(Parser::RAW_TAG_CONTENT, $type);
        $this->assertSame('raw', $text);

        [$text, $type, $context] = $listener->calls[14];
        $this->assertSame(Parser::TAG_NAME, $type);
        $this->assertSame('noSpace', $text);

        [$text, $type, $context] = $listener->calls[15];
        $this->assertSame(Parser::RAW_TAG_CONTENT, $type);
        $this->assertSame('raw', $text);

        [$text, $type, $context] = $listener->calls[16];
        $this->assertSame(Parser::QUOTED_TAG_CONTENT, $type);
        $this->assertSame('quoted', $text);

        [$text, $type, $context] = $listener->calls[17];
        $this->assertSame(Parser::BRACED_TAG_CONTENT, $type);
        $this->assertSame('braced', $text);

        [$text, $type, $context] = $listener->calls[18];
        $this->assertSame(Parser::ENTRY, $type);
        $original = trim(file_get_contents(__DIR__ . '/../resources/valid/tag-contents-multiple.bib'));
        $this->assertSame($original, $text);
    }

    public function testTagContentSlashes(): void
    {
        $listener = new DummyListener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/tag-contents-slashes.bib');

        $this->assertCount(6, $listener->calls);

        [$text, $type, $context] = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $type);
        $this->assertSame('valuesSlashes', $text);

        [$text, $type, $context] = $listener->calls[1];
        $this->assertSame(Parser::TAG_NAME, $type);
        $this->assertSame('braced', $text);

        [$text, $type, $context] = $listener->calls[2];
        $this->assertSame(Parser::BRACED_TAG_CONTENT, $type);
        $this->assertSame('\\}\\"\\%\\', $text);

        [$text, $type, $context] = $listener->calls[3];
        $this->assertSame(Parser::TAG_NAME, $type);
        $this->assertSame('quoted', $text);

        [$text, $type, $context] = $listener->calls[4];
        $this->assertSame(Parser::QUOTED_TAG_CONTENT, $type);
        $this->assertSame('\\}\\"\\%\\', $text);

        [$text, $type, $context] = $listener->calls[5];
        $this->assertSame(Parser::ENTRY, $type);
        $original = trim(file_get_contents(__DIR__ . '/../resources/valid/tag-contents-slashes.bib'));
        $this->assertSame($original, $text);
    }

    public function testTagContentNestedBraces(): void
    {
        $listener = new DummyListener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/tag-contents-nested-braces.bib');

        $this->assertCount(8, $listener->calls);

        [$text, $type, $context] = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $type);
        $this->assertSame('valuesBraces', $text);

        [$text, $type, $context] = $listener->calls[1];
        $this->assertSame(Parser::TAG_NAME, $type);
        $this->assertSame('link', $text);

        [$text, $type, $context] = $listener->calls[2];
        $this->assertSame(Parser::BRACED_TAG_CONTENT, $type);
        $this->assertSame('\url{https://github.com}', $text);

        [$text, $type, $context] = $listener->calls[3];
        $this->assertSame(Parser::TAG_NAME, $type);
        $this->assertSame('twoLevels', $text);

        [$text, $type, $context] = $listener->calls[4];
        $this->assertSame(Parser::BRACED_TAG_CONTENT, $type);
        $this->assertSame('a{b{c}d}e', $text);

        [$text, $type, $context] = $listener->calls[5];
        $this->assertSame(Parser::TAG_NAME, $type);
        $this->assertSame('escapedBrace', $text);

        [$text, $type, $context] = $listener->calls[6];
        $this->assertSame(Parser::BRACED_TAG_CONTENT, $type);
        $this->assertSame('before{}}after', $text);

        [$text, $type, $context] = $listener->calls[7];
        $this->assertSame(Parser::ENTRY, $type);
        $original = trim(file_get_contents(__DIR__ . '/../resources/valid/tag-contents-nested-braces.bib'));
        $this->assertSame($original, $text);
    }

    /**
     * @see https://github.com/renanbr/bibtex-parser/issues/62
     */
    #[Group('regression')]
    #[Group('bug62')]
    public function testStringVariableWithSpecialCharacterMustBeAccepted(): void
    {
        $listener = new DummyListener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/string-var-with-special-char-basic.bib');

        [$text, $type, $context] = $listener->calls[7];
        $this->assertSame(Parser::RAW_TAG_CONTENT, $type);
        $this->assertSame('foo-bar', $text);
    }
}
