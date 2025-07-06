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

use DateTime;
use DateTimeImmutable;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RenanBr\BibTexParser\Processor\DateProcessor;

#[CoversClass(DateProcessor::class)]
class DateProcessorTest extends TestCase
{
    public function testDateYearAndMonth(): void
    {
        $processor = new DateProcessor();
        $entry = $processor([
            'month' => '1~oct',
            'year' => '2000',
        ]);
        $this->assertSame('2000', $entry['year']);
        $this->assertSame('1~oct', $entry['month']);
        /** @var DateTimeImmutable $dateTime */
        $dateTime = $entry['_date'];
        $this->assertSame('2000-10-01T00:00:00+00:00', $dateTime->format(DateTime::ATOM));
        $this->assertSame('UTC', $dateTime->getTimezone()->getName());
    }

    public function testMissingDayInMonthShouldNotCreateDateTag(): void
    {
        $processor = new DateProcessor();
        $entry = $processor(['month' => 'Jul', 'year' => '2000']);
        $this->assertSame('Jul', $entry['month']);
        $this->assertSame('2000', $entry['year']);
        $this->assertArrayNotHasKey('_date', $entry);
    }

    /**
     * @throws Exception
     */
    public function testMissingMonthInMonthShouldNotCreateDateTag(): void
    {
        $processor = new DateProcessor();
        $entry = $processor(['month' => '05', 'year' => '2000']);
        $this->assertSame('05', $entry['month']);
        $this->assertSame('2000', $entry['year']);
        $this->assertArrayNotHasKey('_date', $entry);
    }

    public function testDateSemiYear(): void
    {
        $processor = new DateProcessor();
        $entry = $processor(['month' => '1~jan', 'year' => '98']);
        $this->assertSame('1~jan', $entry['month']);
        $this->assertSame('98', $entry['year']);
        /** @var DateTimeImmutable $dateTime */
        $dateTime = $entry['_date'];
        $this->assertSame('1998-01-01T00:00:00+00:00', $dateTime->format(DateTime::ATOM));
        $this->assertSame('UTC', $dateTime->getTimezone()->getName());
    }

    public function testDateInvalidShouldNotCreateDateTag(): void
    {
        $processor = new DateProcessor();
        $entry = $processor(['month' => 'foo', 'year' => 'bar']);
        $this->assertSame('foo', $entry['month']);
        $this->assertSame('bar', $entry['year']);
        $this->assertArrayNotHasKey('_date', $entry);
    }

    public function testDateMissingYearShouldNotCreateDateTag(): void
    {
        $processor = new DateProcessor();
        $entry = $processor(['month' => '1~jan']);
        $this->assertSame('1~jan', $entry['month']);
        $this->assertArrayNotHasKey('_date', $entry);
    }

    public function testDateMissingMonthShouldNotCreateDateTag(): void
    {
        $processor = new DateProcessor();
        $entry = $processor(['year' => '2000']);
        $this->assertSame('2000', $entry['year']);
        $this->assertArrayNotHasKey('_date', $entry);
    }

    public function testDateMissingAllShouldNotCreateDateTag(): void
    {
        $processor = new DateProcessor();
        $entry = $processor([]);
        $this->assertArrayNotHasKey('_date', $entry);
    }

    public function testDateAlreadyExist(): void
    {
        $processor = new DateProcessor();
        $entry = $processor(['_date' => 'I do exist']);
        $this->assertSame('I do exist', $entry['_date']);
    }

    public function testInvalidMonthNameShouldNotCreateDateTag(): void
    {
        $processor = new DateProcessor();
        $entry = $processor([
            'month' => '1~foo',
            'year' => '2000',
        ]);
        $this->assertArrayNotHasKey('_date', $entry);
    }

    public function testInvalidDayShouldNotCreateDateTag(): void
    {
        $processor = new DateProcessor();
        $entry = $processor([
            'month' => '42~dec',
            'year' => '1999',
        ]);
        $this->assertArrayNotHasKey('_date', $entry);
    }

    public function testDateIsImmutable(): void
    {
        $processor = new DateProcessor();
        $entry = $processor([
            'month' => '1~oct',
            'year' => '2000',
        ]);
        $this->assertInstanceOf(DateTimeImmutable::class, $entry['_date']);
    }

    public function testCustomTagName(): void
    {
        $processor = new DateProcessor('my_tag_name');
        $entry = $processor([
            'month' => '1~oct',
            'year' => '2000',
        ]);
        $this->assertArrayNotHasKey('_date', $entry);
        $this->assertArrayHasKey('my_tag_name', $entry);
        $this->assertInstanceOf(DateTimeImmutable::class, $entry['my_tag_name']);
    }
}
