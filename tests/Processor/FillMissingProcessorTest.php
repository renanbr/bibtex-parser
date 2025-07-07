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
use RenanBr\BibTexParser\Processor\FillMissingProcessor;

class FillMissingProcessorTest extends TestCase
{
    public function testWithMissingFields(): void
    {
        $processor = new FillMissingProcessor([
            'description' => 'this entry has no description',
        ]);

        $entry = $processor([
            'title' => 'foo',
        ]);

        $this->assertSame(
            'this entry has no description',
            $entry['description'],
        );
    }

    public function testWithoutMissingFields(): void
    {
        $processor = new FillMissingProcessor([
            'description' => 'this entry has no description',
        ]);

        $entry = $processor([
            'title' => 'foo',
            'description' => 'bar',
        ]);

        $this->assertSame(
            'bar',
            $entry['description'],
        );
    }
}
