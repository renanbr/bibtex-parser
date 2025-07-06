<?php

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser\Test\Listener;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use RenanBr\BibTexParser\Listener;
use RenanBr\BibTexParser\Parser;

class ValueReadingTest extends TestCase
{
    public function testMultipleNature(): void
    {
        $listener = new Listener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/tag-contents-basic.bib');

        $entries = $listener->export();
        $this->assertCount(1, $entries);

        $entry = $entries[0];
        $this->assertSame('valuesBasic', $entry['type']);
        $this->assertSame('kNull', $entry['citation-key']);
        $this->assertNull($entry['kStillNull']);
        $this->assertSame('raw', $entry['kRaw']);
        $this->assertSame(' braced value ', $entry['kBraced']);
        $this->assertSame('', $entry['kBracedEmpty']);
        $this->assertSame(' quoted value ', $entry['kQuoted']);
        $this->assertSame('', $entry['kQuotedEmpty']);
    }

    public function testValueConcatenation(): void
    {
        $listener = new Listener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/tag-contents-multiple.bib');

        $entries = $listener->export();
        $this->assertCount(1, $entries);

        $entry = $entries[0];
        $this->assertSame('multipleTagContents', $entry['type']);
        $this->assertSame('rawArawB', $entry['raw']);
        $this->assertSame('quoted aquoted b', $entry['quoted']);
        $this->assertSame('braced abraced b', $entry['braced']);
        $this->assertSame('quotedbracedraw', $entry['misc']);
        $this->assertSame('rawquotedbraced', $entry['noSpace']);
    }

    public function testAbbreviation(): void
    {
        $listener = new Listener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/abbreviation.bib');

        $entries = $listener->export();
        $this->assertCount(3, $entries);

        $entry = $entries[0];
        $this->assertSame('string', $entry['type']);
        $this->assertSame('Renan', $entry['me']);
        $this->assertSame('', $entry['emptyAbbr']);
        $this->assertNull($entry['nullAbbr']);
        $this->assertSame('Sir Renan', $entry['meImportant']);

        $entry = $entries[1];
        $this->assertSame('string', $entry['type']);
        $this->assertSame('Glamorous Sir Renan', $entry['meAccordingToMyMother']);

        $entry = $entries[2];
        $this->assertSame('abbreviation', $entry['type']);
        $this->assertSame('Hello Glamorous Sir Renan!', $entry['message']);
        $this->assertSame('me', $entry['skip']);
        $this->assertSame('', $entry['mustEmpty']);
        $this->assertNull($entry['mustNull']);
    }

    /**
     * @see https://github.com/renanbr/bibtex-parser/issues/62
     */
    #[Group('regression')]
    #[Group('bug62')]
    public function testStringVariableWithSpecialCharacterMustBeAccepted(): void
    {
        $listener = new Listener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/string-var-with-special-char.bib');

        $entries = $listener->export();

        $entries[10]['author'];
        $this->assertSame('Balbuena and Cera and Dianez and Garcia-Vazquez', $entries[10]['author']);
    }
}
