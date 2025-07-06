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

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use RenanBr\BibTexParser\Listener;
use RenanBr\BibTexParser\Parser;
use RenanBr\BibTexParser\Processor\LatexToUnicodeProcessor;

class LatexToUnicodeProcessorTest extends TestCase
{
    #[Before]
    protected function checkPandoc(): void
    {
        exec('which pandoc', $output, $retVal);
        if (0 !== $retVal) {
            $this->markTestSkipped(
                'Pandoc is not installed, skipping this test',
            );
        }
    }

    public function testTextAsInput(): void
    {
        $processor = new LatexToUnicodeProcessor();
        $entry = $processor([
            'text' => 'tr\\`{e}s bien',
        ]);

        $this->assertSame('très bien', $entry['text']);
    }

    public function testArrayAsInput(): void
    {
        $processor = new LatexToUnicodeProcessor();
        $entry = $processor([
            'text' => [
                'foo' => "f\\'{u}",
                'bar' => 'b{\\aa}r',
            ],
        ]);

        $this->assertSame([
            'foo' => 'fú',
            'bar' => 'bår',
        ], $entry['text']);
    }

    public function testThroughListener(): void
    {
        $listener = new Listener();
        $listener->addProcessor(new LatexToUnicodeProcessor());

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/tag-contents-latex.bib');

        $entries = $listener->export();

        // Some sanity checks to make sure it didn't screw the rest of the entry
        $this->assertCount(4, $entries[0]);
        $this->assertSame('tagContentLatex', $entries[0]['type']); // @legacy
        $this->assertSame('tagContentLatex', $entries[0]['_type']);

        $this->assertSame(
            trim(file_get_contents(__DIR__ . '/../resources/valid/tag-contents-latex.bib')),
            $entries[0]['_original'],
        )
        ;

        $this->assertSame('cafés', $entries[0]['consensus']);
    }

    public function testDoNotWrapLongText(): void
    {
        $longText = '0123456789 0123456789 0123456789 0123456789 0123456789 0123456789 0123456789 0123456789';
        $processor = new LatexToUnicodeProcessor();
        $entry = $processor([
            'text' => $longText,
        ]);

        $this->assertSame($longText, $entry['text']);
    }
}
