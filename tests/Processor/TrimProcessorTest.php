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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RenanBr\BibTexParser\Processor\TrimProcessor;

/**
 * @author Florent DESPIERRES <florent@despierres.pro>
 */
#[CoversClass(TrimProcessor::class)]
class TrimProcessorTest extends TestCase
{
    /**
     * @param array<string, mixed> $entry
     * @param array<string, mixed> $expectedEntry
     */
    #[DataProvider('entriesProvider')]
    public function testProcessEntry(array $entry, array $expectedEntry): void
    {
        $processor = new TrimProcessor();
        $this->assertSame($expectedEntry, $processor($entry));
    }

    /**
     * @return iterable<string, array<array<string, mixed>>>
     */
    public static function entriesProvider(): iterable
    {
        yield 'basicEntry' => [
            ['title' => '  Relativity: The Special and General Theory  ', 'citation-key' => '  einstein1916relativity'],
            ['title' => 'Relativity: The Special and General Theory', 'citation-key' => 'einstein1916relativity'],
        ];

        yield 'EntryWithArray' => [
            ['title' => '  Relativity...', 'keywords' => ['big data', '  data deluge', 'scientific method ']],
            ['title' => 'Relativity...', 'keywords' => ['big data', 'data deluge', 'scientific method']],
        ];

        yield 'EntryWithOtherTypeUnHandle' => [
            ['title' => '  Relativity...', 'year' => 2018],
            ['title' => 'Relativity...', 'year' => 2018],
        ];
    }

    public function testTagCoverage()
    {
        $processor = new TrimProcessor(['bar']);

        $expected = [
            'foo' => ' foo ',
            'bar' => 'bar',
        ];

        $entry = [
            'foo' => ' foo ',
            'bar' => ' bar ',
        ];

        $this->assertSame($expected, $processor($entry));
    }
}
