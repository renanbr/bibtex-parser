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

use RenanBr\BibTexParser\Exception\ProcessorException;

class UrlFromDoiProcessor
{
    use TagSearchTrait;

    private $urlDoiPrefix;

    public function __construct($urlDoiPrefix = 'https://doi.org')
    {
        $this->urlDoiPrefix = $urlDoiPrefix;
    }

    /**
     * @param array $entry
     *
     * @return array
     * @throws ProcessorException
     */
    public function __invoke(array $entry)
    {
        $doiTag = $this->tagSearch('doi', array_keys($entry));
        $urlTag = $this->tagSearch('url', array_keys($entry));
        if ($urlTag === null && $doiTag !== null) {
            $doiValue = $entry[$doiTag];
            if ($doiValue === '') {
                throw new ProcessorException('doi tag should not be empty');
            }
            $entry['url'] = $this->urlDoiPrefix . '/' . $doiValue;
        }
        return $entry;
    }

}