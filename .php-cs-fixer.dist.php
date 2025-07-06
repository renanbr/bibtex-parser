<?php

$header = <<<EOF
This file is part of the BibTex Parser.

(c) Renan de Lima Barbosa <renandelima@gmail.com>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS' => true,
        '@PER-CS:risky' => true,
    ])
    ->setFinder(
        (new PhpCsFixer\Finder())
            ->in([
                __DIR__ . '/src',
                __DIR__ . '/tests',
            ])
    );
