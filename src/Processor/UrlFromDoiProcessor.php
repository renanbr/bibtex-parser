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

use RenanBr\BibTexParser\Constant;

class UrlFromDoiProcessor
{
    use TagSearchTrait;

    private $urlDoiPrefix;

    public function __construct($urlDoiPrefix = null)
    {
        $this->urlDoiPrefix = $urlDoiPrefix;
    }

    /**
     * @param array $entry
     *
     * @return array
     */
    public function __invoke(array $entry)
    {
        $doiTag = $this->tagSearch('doi', array_keys($entry));
        $urlTag = $this->tagSearch('url', array_keys($entry));
        if ($urlTag === null && $doiTag !== null) {
            $doiValue = $entry[$doiTag];
            if ($doiValue !== '') {
                if ($this->urlDoiPrefix !== null) {
                    $entry['url'] = $this->urlDoiPrefix . '/' . $doiValue;
                } else {
                    $entry['url'] = sprintf(Constant::$URL_DOI_FORMAT, $doiValue);
                }
            }
        }
        return $entry;
    }

}