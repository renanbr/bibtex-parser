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
class BasicTest extends TestCase
{
    public function testBasic(): void
    {
        $listener = new DummyListener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/basic.bib');

        $this->assertCount(4, $listener->calls);

        [$text, $type, $context] = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $type);
        $this->assertSame('basic', $text);
        $this->assertSame(1, $context['offset']);
        $this->assertSame(5, $context['length']);

        [$text, $type, $context] = $listener->calls[1];
        $this->assertSame(Parser::TAG_NAME, $type);
        $this->assertSame('foo', $text);
        $this->assertSame(13, $context['offset']);
        $this->assertSame(3, $context['length']);

        [$text, $type, $context] = $listener->calls[2];
        $this->assertSame(Parser::RAW_TAG_CONTENT, $type);
        $this->assertSame('bar', $text);
        $this->assertSame(19, $context['offset']);
        $this->assertSame(3, $context['length']);

        [$text, $type, $context] = $listener->calls[3];
        $this->assertSame(Parser::ENTRY, $type);
        $original = trim(file_get_contents(__DIR__ . '/../resources/valid/basic.bib'));
        $this->assertSame($original, $text);
        $this->assertSame(0, $context['offset']);
        $this->assertSame(24, $context['length']);
    }

    /**
     * @see https://github.com/renanbr/bibtex-parser/issues/39
     */
    #[Group('regression')]
    #[Group('bug39')]
    public function testOriginalEntryTriggeringWhenLastCharClosesAnEntry(): void
    {
        $listener = new DummyListener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseString('@misc{title="findme"}');

        $this->assertCount(4, $listener->calls);

        [$text, $type] = $listener->calls[3];
        $this->assertSame('@misc{title="findme"}', $text);
        $this->assertSame(Parser::ENTRY, $type);
    }
}
