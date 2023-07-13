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

use Composer\InstalledVersions;
use Exception;
use Pandoc\Pandoc;
use RenanBr\BibTexParser\Exception\ProcessorException;

/**
 * Translates LaTeX texts to unicode.
 */
class LatexToUnicodeProcessor
{
    use TagCoverageTrait;

    /** @var Pandoc|null */
    private $pandoc;

    /**
     * @return array
     */
    public function __invoke(array $entry)
    {
        $covered = $this->getCoveredTags(array_keys($entry));
        foreach ($covered as $tag) {
            // Translate string
            if (\is_string($entry[$tag])) {
                $entry[$tag] = $this->decode($entry[$tag]);
                continue;
            }

            // Translate array
            if (\is_array($entry[$tag])) {
                array_walk_recursive($entry[$tag], function (&$text) {
                    if (\is_string($text)) {
                        $text = $this->decode($text);
                    }
                });
            }
        }

        return $entry;
    }

    /**
     * Returns true if the ueberdosis/pandoc package is installed, otherwise returns false.
     *
     * @return bool
     */
    private function isUeberdosisPandocAvailable()
    {
        return InstalledVersions::isInstalled('ueberdosis/pandoc');
    }

    /**
     * @param mixed $text
     *
     * @return string
     */
    private function decode($text)
    {
        try {
            if (!$this->pandoc) {
                $this->pandoc = new Pandoc();
            }

            if ($this->isUeberdosisPandocAvailable()) {
                // use ueberdosis/pandoc
                $output = $this->pandoc->input($text)->execute([
                    '--from', 'latex',
                    '--to', 'plain',
                    '--wrap', 'none',
                ]);

                // remove newline character added by Pandoc conversion
                $output = substr($output, 0, -1);
            } else {
                // use ryakad/pandoc-php
                $output = $this->pandoc->runWith($text, [
                    'from' => 'latex',
                    'to' => 'plain',
                    'wrap' => 'none',
                ]);
            }

            return $output;
        } catch (Exception $exception) {
            throw new ProcessorException(sprintf('Error while processing LaTeX to Unicode: %s', $exception->getMessage()), 0, $exception);
        }
    }
}
