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
use RenanBr\BibTexParser\Processor\TrimProcessor;

/**
 * @covers \RenanBr\BibTexParser\Processor\TrimProcessor
 */
class TrimProcessorTest extends TestCase
{
    public function testTrimLeftRight()
    {
        $processor = new TrimProcessor();
        $entry = $processor([
            'title' => '  foo  ',
        ]);
        $this->assertSame('foo', $entry['title']);
    }

    public function testTrimWithSpaceBetweenTwoString()
    {
        $processor = new TrimProcessor();
        $entry = $processor([
            'title' => '  foo bar  ',
        ]);
        $this->assertSame('foo bar', $entry['title']);
    }
}