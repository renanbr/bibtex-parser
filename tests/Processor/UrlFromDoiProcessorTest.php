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

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use RenanBr\BibTexParser\Processor\UrlFromDoiProcessor;

#[CoversNothing]
class UrlFromDoiProcessorTest extends TestCase
{
    public function testDoi(): void
    {
        $processor = new UrlFromDoiProcessor();
        $entry = $processor([
            'doi' => 'xyz',
        ]);
        $this->assertSame(['doi' => 'xyz', 'url' => 'https://doi.org/xyz'], $entry);
    }

    public function testDoiEmpty(): void
    {
        $processor = new UrlFromDoiProcessor();
        $entry = $processor([
            'doi' => '',
        ]);
        $this->assertSame(['doi' => ''], $entry);
        $this->assertFalse(\array_key_exists('url', $entry));
    }

    public function testDoiWithUrl(): void
    {
        $processor = new UrlFromDoiProcessor();
        $entry = $processor([
            'doi' => 'xyz',
            'url' => 'https://doi.org/abc',
        ]);
        $this->assertSame(['doi' => 'xyz', 'url' => 'https://doi.org/abc'], $entry);
    }

    public function testDoiCustomUrl(): void
    {
        $processor = new UrlFromDoiProcessor('https://custom-doi-url.org/%s');
        $entry = $processor([
            'doi' => 'xyz',
        ]);
        $this->assertSame(['doi' => 'xyz', 'url' => 'https://custom-doi-url.org/xyz'], $entry);
    }
}
