<?php

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser\Test\Parser;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RenanBr\BibTexParser\Parser;
use RenanBr\BibTexParser\Test\DummyListener;

#[CoversClass(Parser::class)]
class StringParsingTest extends TestCase
{
    #[DataProvider('validFileProvider')]
    public function testStringParserAndFileParserMustWorkIdentically($file): void
    {
        $listenerFile = new DummyListener();
        $parserFile = new Parser();
        $parserFile->addListener($listenerFile);
        $parserFile->parseFile($file);

        $listenerString = new DummyListener();
        $parserString = new Parser();
        $parserString->addListener($listenerString);
        $parserString->parseString(file_get_contents($file));

        $this->assertSame($listenerFile->calls, $listenerString->calls);
    }

    /**
     * @return array<string, array<string>>
     */
    public static function validFileProvider(): array
    {
        $dir = __DIR__ . '/../resources/valid';

        return [
            'abbreviation' => [$dir . '/abbreviation.bib'],
            'basic' => [$dir . '/basic.bib'],
            'citation key' => [$dir . '/citation-key.bib'],
            'multiples entries' => [$dir . '/multiples-entries.bib'],
            'no value' => [$dir . '/no-tag-content.bib'],
            'uppercased tag' => [$dir . '/tag-name-uppercased.bib'],
            'tag with underscore' => [$dir . '/tag-name-with-underscore.bib'],
            'trailing comma' => [$dir . '/trailing-comma.bib'],
            'type overriding' => [$dir . '/type-overriding.bib'],
            'basic values' => [$dir . '/tag-contents-basic.bib'],
            'escaped values' => [$dir . '/tag-contents-escaped.bib'],
            'multiple values' => [$dir . '/tag-contents-multiple.bib'],
            'values with nested braces' => [$dir . '/tag-contents-nested-braces.bib'],
            'values with slashs' => [$dir . '/tag-contents-slashes.bib'],
        ];
    }
}
