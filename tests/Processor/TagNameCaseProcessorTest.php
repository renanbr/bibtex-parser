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
use RenanBr\BibTexParser\Processor\TagNameCaseProcessor;

/**
 * @covers \RenanBr\BibTexParser\Processor\TagNameCaseProcessor
 */
class TagNameCaseProcessorTest extends TestCase
{
    public function testLower()
    {
        $listener = new Listener();
        $listener->addProcessor(new TagNameCaseProcessor(\CASE_LOWER));

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__.'/../resources/valid/tag-name-uppercased.bib');

        $entries = $listener->export();

        $this->assertArrayHasKey('foo', $entries[0]);
        $this->assertArrayNotHasKey('FoO', $entries[0]);
        $this->assertSame('bAr', $entries[0]['foo']);
    }

    public function testUpper()
    {
        $listener = new Listener();
        $listener->addProcessor(new TagNameCaseProcessor(\CASE_UPPER));

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__.'/../resources/valid/tag-name-uppercased.bib');

        $entries = $listener->export();

        $this->assertArrayHasKey('FOO', $entries[0]);
        $this->assertArrayNotHasKey('FoO', $entries[0]);
        $this->assertSame('bAr', $entries[0]['FOO']);
    }
}
