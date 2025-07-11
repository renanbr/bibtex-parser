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

use ErrorException;
use RenanBr\BibTexParser\Exception\ParserException;

class Parser
{
    public const TYPE = 'type';
    public const CITATION_KEY = 'citation_key';
    public const TAG_NAME = 'tag_name';
    public const RAW_TAG_CONTENT = 'raw_tag_content';
    public const BRACED_TAG_CONTENT = 'braced_tag_content';
    public const QUOTED_TAG_CONTENT = 'quoted_tag_content';
    public const ENTRY = 'entry';

    public const NONE = 'none';
    public const COMMENT = 'comment';
    public const FIRST_TAG_NAME = 'first_tag_name';
    public const POST_TYPE = 'post_type';
    public const POST_TAG_NAME = 'post_tag_name';
    public const PRE_TAG_CONTENT = 'pre_tag_content';

    private string $state;

    private string $buffer;

    private ?int $bufferOffset = null;

    private ?array $firstTagSnapshot = null;

    private ?string $originalEntryBuffer = null;

    private ?int $originalEntryOffset = null;

    private bool $skipOriginalEntryReading;

    private int $line;

    private int $column;

    private int $offset;

    private bool $isTagContentEscaped;

    private bool $mayConcatenateTagContent;

    private ?string $tagContentDelimiter = null;

    private int $braceLevel;

    /** @var array<ListenerInterface> */
    private array $listeners = [];

    public function addListener(ListenerInterface $listener): void
    {
        $this->listeners[] = $listener;
    }

    /**
     * @throws ParserException if $file given is not a valid BibTeX
     * @throws ErrorException  if $file given is not readable
     */
    public function parseFile(string $file): void
    {
        $handle = @fopen($file, 'r');
        if (!$handle) {
            throw new ErrorException(sprintf('Unable to open %s', $file));
        }
        try {
            $this->reset();
            while (!feof($handle)) {
                $buffer = fread($handle, 128);
                $this->parse($buffer);
            }
            $this->throwExceptionIfReadingEntry("\0");
        } finally {
            fclose($handle);
        }
    }

    /**
     * @throws ParserException if $string given is not a valid BibTeX
     */
    public function parseString(string $string): void
    {
        $this->reset();
        $this->parse($string);
        $this->throwExceptionIfReadingEntry("\0");
    }

    private function parse(string $text): void
    {
        $text = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $length = count($text);
        for ($position = 0; $position < $length; ++$position) {
            $char = $text[$position];
            $this->read($char);
            if ("\n" === $char) {
                ++$this->line;
                $this->column = 1;
            } else {
                ++$this->column;
            }
            ++$this->offset;
        }
    }

    private function reset(): void
    {
        $this->state = self::NONE;
        $this->buffer = '';
        $this->firstTagSnapshot = null;
        $this->originalEntryBuffer = null;
        $this->originalEntryOffset = null;
        $this->skipOriginalEntryReading = false;
        $this->line = 1;
        $this->column = 1;
        $this->offset = 0;
        $this->mayConcatenateTagContent = false;
        $this->isTagContentEscaped = false;
        $this->tagContentDelimiter = null;
        $this->braceLevel = 0;
    }

    // ----- Readers -----------------------------------------------------------

    private function read(string $char): void
    {
        $previousState = $this->state;

        switch ($this->state) {
            case self::NONE:
                $this->readNone($char);
                break;
            case self::COMMENT:
                $this->readComment($char);
                break;
            case self::TYPE:
                $this->readType($char);
                break;
            case self::POST_TYPE:
                $this->readPostType($char);
                break;
            case self::FIRST_TAG_NAME:
            case self::TAG_NAME:
                $this->readTagName($char);
                break;
            case self::POST_TAG_NAME:
                $this->readPostTagName($char);
                break;
            case self::PRE_TAG_CONTENT:
                $this->readPreTagContent($char);
                break;
            case self::RAW_TAG_CONTENT:
                $this->readRawTagContent($char);
                break;
            case self::QUOTED_TAG_CONTENT:
            case self::BRACED_TAG_CONTENT:
                $this->readDelimitedTagContent($char);
                break;
        }

        $this->readOriginalEntry($char, $previousState);
    }

    private function readNone(string $char): void
    {
        if ('@' === $char) {
            $this->state = self::TYPE;
        } elseif (!$this->isWhitespace($char)) {
            $this->state = self::COMMENT;
        }
    }

    private function readComment(string $char): void
    {
        if ($this->isWhitespace($char)) {
            $this->state = self::NONE;
        }
    }

    private function readType(string $char): void
    {
        if (preg_match('/^[a-zA-Z]$/', $char)) {
            $this->appendToBuffer($char);
        } else {
            $this->throwExceptionIfBufferIsEmpty($char);

            // Skips @comment type
            if ('comment' === mb_strtolower($this->buffer)) {
                $this->skipOriginalEntryReading = true;
                $this->buffer = '';
                $this->bufferOffset = null;
                $this->state = self::COMMENT;
                $this->readComment($char);

                return;
            }

            $this->triggerListenersWithCurrentBuffer();

            // once $char isn't a valid character
            // it must be interpreted as POST_TYPE
            $this->state = self::POST_TYPE;
            $this->readPostType($char);
        }
    }

    private function readPostType(string $char): void
    {
        if ('{' === $char) {
            $this->state = self::FIRST_TAG_NAME;
        } elseif (!$this->isWhitespace($char)) {
            throw ParserException::unexpectedCharacter($char, $this->line, $this->column);
        }
    }

    private function readTagName(string $char): void
    {
        if (preg_match('/^[a-zA-Z0-9_\+:\-\.\/\x{00C0}-\x{01FF}]$/u', $char)) {
            $this->appendToBuffer($char);
        } elseif ($this->isWhitespace($char) && empty($this->buffer)) {
            // Skips because we didn't start reading
        } elseif ('}' === $char && empty($this->buffer)) {
            // No tag name found, $char is just closing current entry
            $this->state = self::NONE;
        } else {
            $this->throwExceptionIfBufferIsEmpty($char);

            if (self::FIRST_TAG_NAME === $this->state) {
                // Takes a snapshot of current state to be triggered later as
                // tag name or citation key, see readPostTagName()
                $this->firstTagSnapshot = $this->takeBufferSnapshot();
            } else {
                // Current buffer is a simple tag name
                $this->triggerListenersWithCurrentBuffer();
            }

            // Once $char isn't a valid tag name character, it must be
            // interpreted as post tag name
            $this->state = self::POST_TAG_NAME;
            $this->readPostTagName($char);
        }
    }

    private function readPostTagName(string $char): void
    {
        if ('=' === $char) {
            // First tag name isn't a citation key, because it has content
            $this->triggerListenersWithFirstTagSnapshotAs(self::TAG_NAME);
            $this->state = self::PRE_TAG_CONTENT;
        } elseif ('}' === $char) {
            // First tag name is a citation key, because $char closes entry and
            // lets first tag without value
            $this->triggerListenersWithFirstTagSnapshotAs(self::CITATION_KEY);
            $this->state = self::NONE;
        } elseif (',' === $char) {
            // First tag name is a citation key, because $char moves to the next
            // tag and lets first tag without value
            $this->triggerListenersWithFirstTagSnapshotAs(self::CITATION_KEY);
            $this->state = self::TAG_NAME;
        } elseif (!$this->isWhitespace($char)) {
            throw ParserException::unexpectedCharacter($char, $this->line, $this->column);
        }
    }

    private function readPreTagContent(string $char): void
    {
        if (preg_match('/^[a-zA-Z0-9]$/', $char)) {
            // When concatenation is available it means there is already a
            // defined value, and parser expect a concatenator, a tag separator
            // or an entry closing char as next $char
            $this->throwExceptionAccordingToConcatenationAvailability($char, true);
            $this->state = self::RAW_TAG_CONTENT;
            $this->readRawTagContent($char);
        } elseif ('"' === $char) {
            // The exception is here for the same reason of the first case
            $this->throwExceptionAccordingToConcatenationAvailability($char, true);
            $this->tagContentDelimiter = '"';
            $this->state = self::QUOTED_TAG_CONTENT;
        } elseif ('{' === $char) {
            // The exception is here for the same reason of the first case
            $this->throwExceptionAccordingToConcatenationAvailability($char, true);
            $this->tagContentDelimiter = '}';
            $this->state = self::BRACED_TAG_CONTENT;
        } elseif ('#' === $char) {
            $this->throwExceptionAccordingToConcatenationAvailability($char, false);
            $this->mayConcatenateTagContent = false;
        } elseif (',' === $char) {
            $this->throwExceptionAccordingToConcatenationAvailability($char, false);
            $this->mayConcatenateTagContent = false;
            $this->state = self::TAG_NAME;
        } elseif ('}' === $char) {
            $this->throwExceptionAccordingToConcatenationAvailability($char, false);
            $this->mayConcatenateTagContent = false;
            $this->state = self::NONE;
        } elseif (!$this->isWhitespace($char)) {
            throw ParserException::unexpectedCharacter($char, $this->line, $this->column);
        }
    }

    private function readRawTagContent(string $char): void
    {
        if (preg_match('/^[a-zA-Z0-9_\+:\-\.\/]$/', $char)) {
            $this->appendToBuffer($char);
        } else {
            $this->throwExceptionIfBufferIsEmpty($char);
            $this->triggerListenersWithCurrentBuffer();

            // once $char isn't a valid character
            // it must be interpreted as TAG_CONTENT
            $this->mayConcatenateTagContent = true;
            $this->state = self::PRE_TAG_CONTENT;
            $this->readPreTagContent($char);
        }
    }

    private function readDelimitedTagContent(string $char): void
    {
        if ($this->isTagContentEscaped) {
            $this->isTagContentEscaped = false;
            if ($this->tagContentDelimiter !== $char && '\\' !== $char && '%' !== $char) {
                $this->appendToBuffer('\\');
            }
            $this->appendToBuffer($char);
        } elseif ('}' === $this->tagContentDelimiter && '{' === $char) {
            ++$this->braceLevel;
            $this->appendToBuffer($char);
        } elseif ($this->tagContentDelimiter === $char) {
            if (0 === $this->braceLevel) {
                $this->triggerListenersWithCurrentBuffer();
                $this->mayConcatenateTagContent = true;
                $this->state = self::PRE_TAG_CONTENT;
            } else {
                --$this->braceLevel;
                $this->appendToBuffer($char);
            }
        } elseif ('\\' === $char) {
            $this->isTagContentEscaped = true;
        } else {
            $this->appendToBuffer($char);
        }
    }

    private function readOriginalEntry(string $char, string $previousState): void
    {
        if ($this->skipOriginalEntryReading) {
            $this->originalEntryBuffer = '';
            $this->originalEntryOffset = null;
            $this->skipOriginalEntryReading = false;

            return;
        }

        // Checks whether we are reading an entry character or not
        $isPreviousStateEntry = $this->isEntryState($previousState);
        $isCurrentStateEntry = $this->isEntryState($this->state);
        $isEntry = $isPreviousStateEntry || $isCurrentStateEntry;
        if (!$isEntry) {
            return;
        }

        // Appends $char to the original entry buffer
        if (empty($this->originalEntryBuffer)) {
            $this->originalEntryOffset = $this->offset;
        }
        $this->originalEntryBuffer .= $char;

        // Sends original entry to the listeners when $char closes an entry
        $isClosingEntry = $isPreviousStateEntry && !$isCurrentStateEntry;
        if ($isClosingEntry) {
            $this->triggerListeners($this->originalEntryBuffer, self::ENTRY, [
                'offset' => $this->originalEntryOffset,
                'length' => $this->offset - $this->originalEntryOffset + 1,
            ]);
            $this->originalEntryBuffer = '';
            $this->originalEntryOffset = null;
        }
    }

    // ----- Listener triggers -------------------------------------------------

    private function triggerListeners(string $text, string $type, array $context): void
    {
        foreach ($this->listeners as $listener) {
            $listener->bibTexUnitFound($text, $type, $context);
        }
    }

    private function triggerListenersWithCurrentBuffer(): void
    {
        $snapshot = $this->takeBufferSnapshot();
        $text = $snapshot['text'];
        $context = $snapshot['context'];
        $this->triggerListeners($text, $this->state, $context);
    }

    private function triggerListenersWithFirstTagSnapshotAs(string $type): void
    {
        if (empty($this->firstTagSnapshot)) {
            return;
        }
        $text = $this->firstTagSnapshot['text'];
        $context = $this->firstTagSnapshot['context'];
        $this->firstTagSnapshot = null;
        $this->triggerListeners($text, $type, $context);
    }

    // ----- Buffer tools ------------------------------------------------------

    private function appendToBuffer(string $char): void
    {
        if (empty($this->buffer)) {
            $this->bufferOffset = $this->offset;
        }
        $this->buffer .= $char;
    }

    private function takeBufferSnapshot(): array
    {
        $snapshot = [
            'text' => $this->buffer,
            'context' => [
                'offset' => $this->bufferOffset,
                'length' => $this->offset - $this->bufferOffset,
            ],
        ];
        $this->bufferOffset = null;
        $this->buffer = '';

        return $snapshot;
    }

    // ----- Exception throwers ------------------------------------------------

    private function throwExceptionAccordingToConcatenationAvailability(string $char, bool $availability): void
    {
        if ($availability === $this->mayConcatenateTagContent) {
            throw ParserException::unexpectedCharacter($char, $this->line, $this->column);
        }
    }

    private function throwExceptionIfBufferIsEmpty(string $char): void
    {
        if (empty($this->buffer)) {
            throw ParserException::unexpectedCharacter($char, $this->line, $this->column);
        }
    }

    private function throwExceptionIfReadingEntry(string $char): void
    {
        if ($this->isEntryState($this->state)) {
            throw ParserException::unexpectedCharacter($char, $this->line, $this->column);
        }
    }

    // ----- Auxiliaries -------------------------------------------------------

    private function isEntryState(string $state): bool
    {
        return self::NONE !== $state && self::COMMENT !== $state;
    }

    private function isWhitespace(string $char): bool
    {
        return ' ' === $char || "\t" === $char || "\n" === $char || "\r" === $char;
    }
}
