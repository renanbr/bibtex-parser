<?php

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser\Test\Processor\AuthorProcessor;

use PHPUnit\Framework\TestCase;
use RenanBr\BibTexParser\Parser;
use RenanBr\BibTexParser\Listener;
use RenanBr\BibTexParser\Processor\AuthorProcessor;

class IntegrationTest extends TestCase
{
    public function testUsage()
    {
        $listener = new Listener;
        $listener->setTagValueProcessor(new AuthorProcessor());

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../../resources/valid/authors-simple.bib');
        $entries = $listener->export();
    // Some sanity checks to make sure AuthorProcessor didn't screw the rest of the entry
        $this->assertCount(1, $entries);
        $this->assertCount(4, $entries[0]);
        $this->assertSame('authorssimple', $entries[0]['type']);
        $this->assertSame('simple1', $entries[0]['citation-key']);

        $this->assertCount(1, $entries[0]['author']);
        $this->assertSame('John', $entries[0]['author'][0]['first']);
        $this->assertSame('Doe', $entries[0]['author'][0]['last']);
        $this->assertSame('', $entries[0]['author'][0]['von']);
        $this->assertSame('', $entries[0]['author'][0]['jr']);
    }
}
