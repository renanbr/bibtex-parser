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

class DateProcessor
{
    const TAG_NAME = '_date';

    use TagSearchTrait;

    /**
     * @var string
     */
    private $tagName;

    /**
     * @param null $tagName
     */
    public function __construct($tagName = null)
    {
        $this->tagName = $tagName ?: self::TAG_NAME;
    }

    /**
     * @param array $entry
     *
     * @return array
     */
    public function __invoke(array $entry)
    {
        $yearTag = $this->tagSearch('year', array_keys($entry));
        $monthTag = $this->tagSearch('month', array_keys($entry));
        if (null !== $yearTag && null !== $monthTag) {
            $year = $entry[$yearTag];
            $monthArray = explode('~', $entry[$monthTag]);
            if (2 === \count($monthArray)) {
                list($day, $month) = $monthArray;
                $dateMonthNumber = date_parse($month);
                $month = $dateMonthNumber['month'] ?: null;
                if (checkdate($month, $day, $year)) {
                    $timestamp = mktime(0, 0, 0, $month, $day, $year);
                    $entry[$this->tagName] = new DateTimeImmutable(date('Y-m-d', $timestamp));
                }
            }
        }

        return $entry;
    }
}
