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
class TagNameParsingTest extends TestCase
{
    public function testTagNameWithoutTagContent(): void
    {
        $listener = new DummyListener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/no-tag-content.bib');

        $this->assertCount(4, $listener->calls);

        [$text, $type, $context] = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $type);
        $this->assertSame('noTagContent', $text);

        [$text, $type, $context] = $listener->calls[1];
        $this->assertSame(Parser::CITATION_KEY, $type);
        $this->assertSame('foo', $text);

        [$text, $type, $context] = $listener->calls[2];
        $this->assertSame(Parser::TAG_NAME, $type);
        $this->assertSame('bar', $text);

        [$text, $type, $context] = $listener->calls[3];
        $this->assertSame(Parser::ENTRY, $type);
        $original = trim(file_get_contents(__DIR__ . '/../resources/valid/no-tag-content.bib'));
        $this->assertSame($original, $text);
    }
}
