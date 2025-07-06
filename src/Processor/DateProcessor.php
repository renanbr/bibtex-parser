<?php

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser\Processor;

use DateTimeImmutable;
use DateTimeZone;

class DateProcessor
{
    use TagSearchTrait;

    public const TAG_NAME = '_date';

    public function __construct(
        private readonly string $tagName = self::TAG_NAME,
    ) {}

    public function __invoke(array $entry): array
    {
        $yearTag = $this->tagSearch('year', array_keys($entry));
        $monthTag = $this->tagSearch('month', array_keys($entry));
        if (null !== $yearTag && null !== $monthTag) {
            $year = (int) $entry[$yearTag];
            $monthArray = explode('~', (string) $entry[$monthTag]);
            if (2 === \count($monthArray)) {
                [$day, $month] = $monthArray;
                $day = (int) $day;
                $dateMonthNumber = date_parse($month);
                $month = $dateMonthNumber['month'] ?: 0;
                if (checkdate($month, $day, $year)) {
                    $timestamp = mktime(0, 0, 0, $month, $day, $year);
                    $entry[$this->tagName] = new DateTimeImmutable(date('Y-m-d', $timestamp), new DateTimeZone('UTC'));
                }
            }
        }

        return $entry;
    }
}
