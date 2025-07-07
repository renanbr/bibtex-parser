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
use RuntimeException;

/**
 * Translates LaTeX texts to unicode.
 */
class LatexToUnicodeProcessor
{
    use TagCoverageTrait;

    /** @var (callable(string): string)|null */
    private $converter;

    public function __invoke(array $entry): array
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
                array_walk_recursive($entry[$tag], function (&$text): void {
                    if (\is_string($text)) {
                        $text = $this->decode($text);
                    }
                });
            }
        }

        return $entry;
    }

    private function decode($text): string
    {
        try {
            return \call_user_func($this->getConverter(), $text);
        } catch (Exception $exception) {
            throw new ProcessorException(sprintf('Error while processing LaTeX to Unicode: %s', $exception->getMessage()), 0, $exception);
        }
    }

    /**
     * @return (callable(string): string)
     */
    private function getConverter(): callable
    {
        if ($this->converter) {
            return $this->converter;
        }

        if (InstalledVersions::isInstalled('ueberdosis/pandoc')) {
            return $this->converter = (static fn($text) => mb_substr((string) (new Pandoc())->input($text)->execute([
                '--from', 'latex',
                '--to', 'plain',
                '--wrap', 'none',
            ]), 0, -1));
        }

        throw new RuntimeException('Pandoc wrapper not installed. Try running "composer require ueberdosis/pandoc"');
    }
}
