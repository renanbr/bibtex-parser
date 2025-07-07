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
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use RenanBr\BibTexParser\Listener;
use RenanBr\BibTexParser\Parser;

class ReusableTest extends TestCase
{
    public function testListenerKeepWorkAmongParseCalls(): void
    {
        $parser = new Parser();
        $listener = new Listener();
        $listener->addProcessor(function (array $entry) {
            $entry['title'] .= ' processed';

            return $entry;
        });
        $parser->addListener($listener);

        $parser->parseString('@misc{title="A"}');
        $entries = $listener->export(); // <--- first call

        $this->assertSame('A processed', $entries[0]['title']);

        $parser->parseString('@misc{title="B"}');
        $entries = $listener->export(); // <--- second call

        $this->assertSame('A processed', $entries[0]['title']);
        $this->assertSame('B processed', $entries[1]['title']);
    }
}
