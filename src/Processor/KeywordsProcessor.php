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

/**
 * Splits tags contents as array.
 */
class KeywordsProcessor
{
    use TagCoverageTrait;

    public function __construct()
    {
        $this->setTagCoverage(['keywords']);
    }

    public function __invoke(array $entry): array
    {
        $covered = $this->getCoveredTags(array_keys($entry));
        foreach ($covered as $tag) {
            $entry[$tag] = preg_split('/, |; /', (string) $entry[$tag]);
        }

        return $entry;
    }
}
