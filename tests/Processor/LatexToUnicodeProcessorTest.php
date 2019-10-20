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

use PHPUnit\Framework\TestCase;
use RenanBr\BibTexParser\Listener;
use RenanBr\BibTexParser\Parser;
use RenanBr\BibTexParser\Processor\LatexToUnicodeProcessor;

/**
 * @covers \RenanBr\BibTexParser\Processor\LatexToUnicodeProcessor
 */
class LatexToUnicodeProcessorTest extends TestCase
{
    protected function setUp()
    {
        exec('which pandoc', $output, $retVal);
        if ($retVal !== 0) {
            $this->markTestSkipped(
                'Pandoc is not installed, skiping this test'
            );
        }
    }

    public function testTextAsInput()
    {
        $processor = new LatexToUnicodeProcessor();
        $entry = $processor([
            'text' => 'tr\\`{e}s bien',
        ]);

        $this->assertSame('très bien', $entry['text']);
    }

    public function testArrayAsInput()
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

    public function testThroughListener()
    {
        $listener = new Listener();
        $listener->addProcessor(new LatexToUnicodeProcessor());

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__.'/../resources/valid/tag-contents-latex.bib');

        $entries = $listener->export();

        // Some sanity checks to make sure it didn't screw the rest of the entry
        $this->assertCount(3, $entries[0]);
        $this->assertSame('tagContentLatex', $entries[0]['type']);
        $this->assertInternalType('string', $entries[0]['_original']);

        $this->assertSame('cafés', $entries[0]['consensus']);
    }
}
