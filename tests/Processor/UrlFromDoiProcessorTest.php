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
use RenanBr\BibTexParser\Processor\UrlFromDoiProcessor;

class UrlFromDoiProcessorTest extends TestCase
{
    public function testDoi()
    {
        $processor = new UrlFromDoiProcessor();
        $entry = $processor([
            'doi' => 'xyz',
        ]);
        $this->assertSame(['doi' => 'xyz', 'url' => 'https://doi.org/xyz'], $entry);
    }

    public function testDoiEmpty()
    {
        $processor = new UrlFromDoiProcessor();
        $entry = $processor([
            'doi' => '',
        ]);
        $this->assertSame(['doi' => ''], $entry);
        $this->assertFalse(array_key_exists('url', $entry));
    }

    public function testDoiWithUrl()
    {
        $processor = new UrlFromDoiProcessor();
        $entry = $processor([
            'doi' => 'xyz',
            'url' => 'https://doi.org/xyz'
        ]);
        $this->assertSame(['doi' => 'xyz', 'url' => 'https://doi.org/xyz'], $entry);
    }

    public function testDoiCustomUrl()
    {
        $processor = new UrlFromDoiProcessor('https://custom-doi-url.org');
        $entry = $processor([
            'doi' => 'xyz',
        ]);
        $this->assertSame(['doi' => 'xyz', 'url' => 'https://custom-doi-url.org/xyz'], $entry);
    }
}