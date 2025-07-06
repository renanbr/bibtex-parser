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

use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\TestCase;
use RenanBr\BibTexParser\Processor\TagSearchTrait;

class TagSearchTraitTest extends TestCase
{
    public function testFound(): void
    {
        $tagSearch = new class {
            use TagSearchTrait { tagSearch as public; }
        };
        $found = $tagSearch->tagSearch('foo', ['foo', 'bar']);

        $this->assertSame('foo', $found);
    }

    public function testNotFound(): void
    {
        $tagSearch = new class {
            use TagSearchTrait { tagSearch as public; }
        };
        $found = $tagSearch->tagSearch('missing', ['foo', 'bar']);

        $this->assertNull($found);
    }

    public function testCaseInsensitiveMatch(): void
    {
        $tagSearch = new class {
            use TagSearchTrait { tagSearch as public; }
        };
        $found = $tagSearch->tagSearch('BAR', ['foo', 'bar']);

        $this->assertSame('bar', $found);
    }
}
