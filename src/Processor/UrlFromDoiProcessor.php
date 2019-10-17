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

class UrlFromDoiProcessor
{
    use TagCoverageTrait;

    /**
     * @param array $entry
     *
     * @return array
     */
    public function __invoke(array $entry)
    {
        $covered = $this->getCoveredTags(array_keys($entry));
        foreach ($covered as $tag) {
            if ($tag === 'doi') {
                $entry['url'] = 'https://doi.org/' . $entry[$tag];
            }
        }
        return $entry;
    }

}