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
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use RenanBr\BibTexParser\Listener;
use RenanBr\BibTexParser\Parser;

#[CoversClass(Listener::class)]
class BasicTest extends TestCase
{
    public function testBasicReading(): void
    {
        $listener = new Listener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/basic.bib');

        $entries = $listener->export();
        $this->assertCount(1, $entries);

        $entry = $entries[0];
        $this->assertSame('basic', $entry['type']);
        $this->assertSame('bar', $entry['foo']);
    }

    public function testTypeOverriding(): array
    {
        $listener = new Listener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/type-overriding.bib');

        $entries = $listener->export();
        $this->assertCount(1, $entries);

        $entry = $entries[0];
        $this->assertSame('new type value', $entry['type']);
        $this->assertSame('bar', $entry['foo']);

        return $entry;
    }

    #[Depends('testTypeOverriding')]
    public function testOriginalTypeIsAvailable(array $entry): void
    {
        $this->assertSame('typeOverriding', $entry['_type']);
    }
}
