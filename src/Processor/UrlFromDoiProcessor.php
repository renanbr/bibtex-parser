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
    use TagSearchTrait;

    public const FORMAT = 'https://doi.org/%s';

    public function __construct(
        private readonly string $urlFormat = self::FORMAT,
    ) {}

    public function __invoke(array $entry): array
    {
        $doiTag = $this->tagSearch('doi', array_keys($entry));
        $urlTag = $this->tagSearch('url', array_keys($entry));
        if (null === $urlTag && null !== $doiTag) {
            $doiValue = $entry[$doiTag];
            if (\is_string($doiValue) && '' !== $doiValue) {
                $entry['url'] = sprintf($this->urlFormat, $doiValue);
            }
        }

        return $entry;
    }
}
