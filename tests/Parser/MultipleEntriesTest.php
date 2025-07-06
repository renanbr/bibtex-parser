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
use PHPUnit\Framework\TestCase;
use RenanBr\BibTexParser\Parser;
use RenanBr\BibTexParser\Test\DummyListener;

#[CoversClass(Parser::class)]
class MultipleEntriesTest extends TestCase
{
    public function testMultipleEntries(): void
    {
        $listener = new DummyListener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/multiples-entries.bib');

        $this->assertCount(8, $listener->calls);

        [$text, $type, $context] = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $type);
        $this->assertSame('entryFooWithSpaces', $text);

        [$text, $type, $context] = $listener->calls[1];
        $this->assertSame(Parser::TAG_NAME, $type);
        $this->assertSame('foo', $text);

        [$text, $type, $context] = $listener->calls[2];
        $this->assertSame(Parser::RAW_TAG_CONTENT, $type);
        $this->assertSame('oof', $text);

        [$text, $type, $context] = $listener->calls[3];
        $this->assertSame(Parser::ENTRY, $type);
        $this->assertSame('@entryFooWithSpaces { foo = oof }', $text);

        [$text, $type, $context] = $listener->calls[4];
        $this->assertSame(Parser::TYPE, $type);
        $this->assertSame('entryBarWithoutSpaces', $text);

        [$text, $type, $context] = $listener->calls[5];
        $this->assertSame(Parser::TAG_NAME, $type);
        $this->assertSame('bar', $text);

        [$text, $type, $context] = $listener->calls[6];
        $this->assertSame(Parser::RAW_TAG_CONTENT, $type);
        $this->assertSame('rab', $text);

        [$text, $type, $context] = $listener->calls[7];
        $this->assertSame(Parser::ENTRY, $type);
        $this->assertSame('@entryBarWithoutSpaces{bar=rab}', $text);
    }
}
