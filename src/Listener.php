<?php

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser;

class Listener implements ListenerInterface
{
    private array $entries = [];

    /**
     * Current tag name.
     *
     * Indicates where to save contents when triggered by the parser.
     */
    private string $currentTagName;

    private array $processors = [];

    private array $processed = [];

    /**
     * @return array all entries found during parsing process
     */
    public function export(): array
    {
        $offset = \count($this->processed);
        $missing = \array_slice($this->entries, $offset);
        foreach ($this->processors as $processor) {
            $missing = array_filter(array_map($processor, $missing));
        }
        $this->processed = array_merge($this->processed, $missing);

        return $this->processed;
    }

    /**
     * @param (callable(array): array) $processor Function to be applied to every BibTeX entry.
     *                                            The processor given must return the modified entry.
     *                                            Processors will be applied in the same order in which they were added.
     */
    public function addProcessor(callable $processor): void
    {
        $this->processors[] = $processor;
    }

    public function bibTexUnitFound($text, $type, array $context): void
    {
        switch ($type) {
            case Parser::TYPE:
                // Starts a new entry
                $this->entries[] = [
                    '_type' => $text,
                    'type' => $text, // compatibility
                ];
                break;

            case Parser::CITATION_KEY:
                $index = \count($this->entries) - 1;
                $this->entries[$index]['citation-key'] = $text;
                break;

            case Parser::TAG_NAME:
                // Saves tag into the current entry
                $index = \count($this->entries) - 1;
                $this->currentTagName = $text;
                $this->entries[$index][$this->currentTagName] = null;
                break;

            case Parser::RAW_TAG_CONTENT:
                // Searches for an abbreviation
                foreach ($this->entries as $entry) {
                    if ('string' === $entry['type'] && \array_key_exists($text, $entry)) {
                        $text = $entry[$text];
                        break;
                    }
                }
                // no break

            case Parser::BRACED_TAG_CONTENT:
            case Parser::QUOTED_TAG_CONTENT:
                // Appends content into the current tag
                if (null !== $text) {
                    $index = \count($this->entries) - 1;
                    $this->entries[$index][$this->currentTagName] .= $text;
                }
                break;

            case Parser::ENTRY:
                $index = \count($this->entries) - 1;
                $this->entries[$index]['_original'] = $text;
                break;
        }
    }
}
