# BibTex Parser

_BibTex Parser_ is a PHP library that provides an API to read [.bib](http://mirrors.ctan.org/biblio/bibtex/base/btxdoc.pdf) files programmatically.

[![Build Status](https://travis-ci.org/renanbr/bibtex-parser.svg?branch=master)](https://travis-ci.org/renanbr/bibtex-parser)

## Install

~~~bash
composer require renanbr/bibtex-parser
~~~

See the [changelog](CHANGELOG.md).

## Usage

1. Create an instance of `RenanBr\BibTexParser\ListenerInterface`
    - `RenanBr\BibTexParser\Listener`, described further in this document, implements this interface and provides many options
2. Create an instance of `RenanBr\BibTexParser\Parser`
3. Attach the Listener to the Parser
4. Parse a _file_ calling `parseFile($file)`...
    - ... or a _string_ calling `parseString($string)`
5. Get data from the Listener (it depends on the interface implementation)
    - `RenanBr\BibTexParser\Listener` provides the `export()` method

```php
$listener = new RenanBr\BibTexParser\Listener;
$parser = new RenanBr\BibTexParser\Parser;
$parser->addListener($listener);
$parser->parseFile('/path/to/example.bib');
$entries = $listener->export();

$entries[0]['type'];         // article
$entries[0]['citation-key']; // Ovadia2011
$entries[0]['title'];        // Managing Citations With Cost-Free Tools
$entries[0]['journal'];      // Behavioral {\&} Social Sciences Librarian
```

Below we have the `example.bib` source file used in the sample above.

```bib
@article{Ovadia2011,
    author = {Ovadia, Steven},
    doi = {10.1080/01639269.2011.565408},
    issn = {0163-9269},
    journal = {Behavioral {\&} Social Sciences Librarian},
    month = {apr},
    number = {2},
    pages = {107--111},
    title = {Managing Citations With Cost-Free Tools},
    url = {http://www.tandfonline.com/doi/abs/10.1080/01639269.2011.565408},
    volume = {30},
    year = {2011}
}
```

## API

### RenanBr\BibTexParser\Parser

```php
class RenanBr\BibTexParser\Parser
{
    /**
     * @throws RenanBr\BibTexParser\ParseException If $file given is not a valid BibTeX.
     * @throws ErrorException If $file given is not readable.
     */
    public function parseFile(string $file): void;

    /**
     * @throws RenanBr\BibTexParser\ParseException If $string given is not a valid BibTeX.
     */
    public function parseString(string $string): void;

    public function addListener(RenanBr\BibTexParser\ListenerInterface $listener): void;
}
```

### RenanBr\BibTexParser\ListenerInterface

```php
interface RenanBr\BibTexParser\ListenerInterface
{
    /**
     * @param string $text The original content of the unit found.
     *                     Escape character will not be sent.
     * @param array $context Contains details of the unit found.
     */
    public function bibTexUnitFound(string $text, array $context): void;
}
```

The `$context` variable explained:
- The `state` key contains the current parser's state.
  It may assume:
  - `Parser::TYPE`
  - `Parser::KEY`
  - `Parser::RAW_VALUE`
  - `Parser::BRACED_VALUE`
  - `Parser::QUOTED_VALUE`
  - `Parser::ORIGINAL_ENTRY`
- `offset` contains the text beginning position.
  It may be useful, for example, to [seek](https://php.net/fseek) a file;
- `length` contains the original text length.
  It may differ from string length sent to the listener because may there are escaped characters.

#### RenanBr\BibTexParser\Listener

As you may noticed, this library provides `RenanBr\BibTexParser\Listener` as a `RenanBr\BibTexParser\ListenerInterface` implementation.
Its features are:
- `export()` returns all entries found;
- It exposes the original entry text in the `_original` key of each entry;
- It [concatenates](http://www.bibtex.org/Format/) tag values;
- It handles [abbreviations](http://www.bibtex.org/Format/);
- If the first tag has no value, the tag name is interpreted as value of `citation-key` tag instead.
- It allows to inject tag value processor through `setTagValueProcessor()`.
  Once BibTeX contain LaTeX, this method may be useful to translate them into HTML, for example.
- It handles tag name case through `setTagNameCase()`

```php
class RenanBr\BibTexParser\Listener implements RenanBr\BibTexParser\ListenerInterface
{
    /**
     * @return array All entries found during a parsing process.
     */
    public function export(): array;

    /**
     * @param int|null $case CASE_LOWER, CASE_UPPER or null (no traitement)
     */
    public function setTagNameCase(int|null $case): void;

    /**
     * @param callable|null $processor Function to be applied to every member of an BibTeX entry.
     *                                 It uses array_walk() internally.
     *                                 The suggested signature for the argument is:
     *                                 function (string &$value, string $tag);
     */
    public function setTagValueProcessor(callable|null $processor): void;

    /* Inherited and implemented methods */

    public function bibTexUnitFound(string $text, array $context): void;
}
```

### Tag Value Processors
#### RenanBr\BibTexParser\Processor\AuthorProcessor
BibTex recognizes four parts of an author's name: First Von Last Jr.
If you would like to parse the author names included in your entries, you can use the `RenanBr\BibTexParser\AuthorProcessor`
class. Before exporting the contents, add this processor:

```php
$listener->setTagValueProcessor(new RenanBr\BibTexParser\Processor\AuthorProcessor());
$entries = $listener->export();
```

The resulting `$entries[0]['author']` will then be an array with each author name separated in the four parts above.
