<?php

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser\Test\Processor\AuthorProcessor;

use PHPUnit\Framework\TestCase;
use RenanBr\BibTexParser\Processor\AuthorProcessor;

class BasicTest extends TestCase
{
    public function testSimple()
    {
        $authors = file_get_contents(__DIR__ . '/../../resources/authors/simple.txt');
        $processor = new AuthorProcessor;
        $processor($authors, "author");

        $this->assertCount(1, $authors);
        $this->assertCount(1, $authors);
        $this->assertSame('John', $authors[0]['first']);
        $this->assertSame('Doe', $authors[0]['last']);
        $this->assertSame('', $authors[0]['von']);
        $this->assertSame('', $authors[0]['jr']);
    }

    public function testMany()
    {
        $authors = file_get_contents(__DIR__ . '/../../resources/authors/many.txt');
        $processor = new AuthorProcessor;
        $processor($authors, "author");
        $this->assertCount(3, $authors);
        $this->assertSame('John', $authors[0]['first']);
        $this->assertSame('Doe', $authors[0]['last']);
        $this->assertSame('', $authors[0]['von']);
        $this->assertSame('', $authors[0]['jr']);
        $this->assertSame('Jane', $authors[1]['first']);
        $this->assertSame('', $authors[1]['von']);
        $this->assertSame('', $authors[1]['jr']);
        $this->assertSame('Dont', $authors[1]['last']);
        $this->assertSame('Jim', $authors[2]['first']);
        $this->assertSame('Doug', $authors[2]['last']);
        $this->assertSame('', $authors[2]['von']);
        $this->assertSame('', $authors[2]['jr']);
    }

    public function testLast()
    {
        $authors = file_get_contents(__DIR__ . '/../../resources/authors/last.txt');
        $processor = new AuthorProcessor;
        $processor($authors, "author");
        $this->assertCount(1, $authors);
        $this->assertSame('', $authors[0]['first']);
        $this->assertSame('ICCAN', $authors[0]['last']);
        $this->assertSame('', $authors[0]['von']);
        $this->assertSame('', $authors[0]['jr']);
    }

    public function testOrder()
    {
        $authors = file_get_contents(__DIR__ . '/../../resources/authors/order.txt');
        $processor = new AuthorProcessor;
        $processor($authors, "author");
    // Tests complex ordering of author names
    $this->assertCount(4, $authors);
        $this->assertSame('Joe', $authors[0]['first']);
        $this->assertSame('Doe', $authors[0]['last']);
        $this->assertSame('', $authors[0]['von']);
        $this->assertSame('', $authors[0]['jr']);
        $this->assertSame('John A. B.', $authors[1]['first']);
        $this->assertSame('Doug', $authors[1]['last']);
        $this->assertSame('', $authors[1]['von']);
        $this->assertSame('', $authors[1]['jr']);
        $this->assertSame('John', $authors[2]['first']);
        $this->assertSame('B. Doe', $authors[2]['last']);
        $this->assertSame('', $authors[2]['von']);
        $this->assertSame('', $authors[2]['jr']);
        $this->assertSame('Jane', $authors[3]['first']);
        $this->assertSame('Duff', $authors[3]['last']);
        $this->assertSame('', $authors[3]['von']);
        $this->assertSame('', $authors[3]['jr']);
    }

    public function testVon()
    {
        // Tests von parts and junior parts
        $authors = file_get_contents(__DIR__ . '/../../resources/authors/von1.txt');
        $processor = new AuthorProcessor;
        $processor($authors, "author");
    // von part
    $this->assertCount(2, $authors);
        $this->assertSame('Ludwig', $authors[0]['first']);
        $this->assertSame('Beethoven', $authors[0]['last']);
        $this->assertSame('von', $authors[0]['von']);
        $this->assertSame('', $authors[0]['jr']);
        $this->assertSame('Johannes Diderik', $authors[1]['first']);
        $this->assertSame('Waals', $authors[1]['last']);
        $this->assertSame('van der', $authors[1]['von']);
        $this->assertSame('', $authors[1]['jr']);
    // junior part
        $authors = file_get_contents(__DIR__ . '/../../resources/authors/von2.txt');
        $processor = new AuthorProcessor;
        $processor($authors, "author");
        $this->assertCount(2, $authors);
        $this->assertSame('Martin Luther', $authors[0]['first']);
        $this->assertSame('King', $authors[0]['last']);
        $this->assertSame('', $authors[0]['von']);
        $this->assertSame('Jr.', $authors[0]['jr']);
        $this->assertSame('Guy L.', $authors[1]['first']);
        $this->assertSame('{Steele Jr.}', $authors[1]['last']);
        $this->assertSame('', $authors[1]['von']);
        $this->assertSame('', $authors[1]['jr']);
    // both
        $authors = file_get_contents(__DIR__ . '/../../resources/authors/von3.txt');
        $processor = new AuthorProcessor;
        $processor($authors, "author");
        $this->assertCount(1, $authors);
        $this->assertSame('First', $authors[0]['first']);
        $this->assertSame('Last', $authors[0]['last']);
        $this->assertSame('von', $authors[0]['von']);
        $this->assertSame('Jr.', $authors[0]['jr']);
    }
}
