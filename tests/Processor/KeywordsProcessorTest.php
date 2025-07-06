<?php

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser\Test\Processor;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RenanBr\BibTexParser\Listener;
use RenanBr\BibTexParser\Parser;
use RenanBr\BibTexParser\Processor\KeywordsProcessor;

#[CoversClass(KeywordsProcessor::class)]
class KeywordsProcessorTest extends TestCase
{
    public function testCommaAsSeparator(): void
    {
        $processor = new KeywordsProcessor();
        $entry = $processor([
            'keywords' => 'foo, bar',
        ]);

        $this->assertSame(['foo', 'bar'], $entry['keywords']);
    }

    public function testSemicolonAsSeparator(): void
    {
        $processor = new KeywordsProcessor();
        $entry = $processor([
            'keywords' => 'foo; bar',
        ]);

        $this->assertSame(['foo', 'bar'], $entry['keywords']);
    }

    /** @see https://github.com/retorquere/zotero-better-bibtex/issues/361 */
    public function testCommaAsTagContent(): void
    {
        $processor = new KeywordsProcessor();
        $entry = $processor([
            'keywords' => '1,2-diol, propargyl alcohol, reaction of, triphosgene',
        ]);

        $this->assertSame([
            '1,2-diol',
            'propargyl alcohol',
            'reaction of',
            'triphosgene',
        ], $entry['keywords']);
    }

    public function testThroughListener(): void
    {
        $listener = new Listener();
        $listener->addProcessor(new KeywordsProcessor());

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/keywords-simple.bib');

        $entries = $listener->export();

        // Some sanity checks to make sure it didn't screw the rest of the entry
        $this->assertCount(4, $entries[0]);
        $this->assertSame('keywordsSimple', $entries[0]['type']); // @legacy
        $this->assertSame('keywordsSimple', $entries[0]['_type']);

        $this->assertSame(
            trim(file_get_contents(__DIR__ . '/../resources/valid/keywords-simple.bib')),
            $entries[0]['_original'],
        )
        ;

        $this->assertSame(['foo', 'bar'], $entries[0]['keywords']);
    }
}
