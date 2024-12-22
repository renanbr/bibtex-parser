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
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'global_namespace_import' => true,
        'header_comment' => ['header' => $header],
        'mb_str_functions' => true,
        'ordered_imports' => true,
        'php_unit_strict' => true,
        'php_unit_test_class_requires_covers' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'visibility_required' => false,
    ])
    ->setFinder(
        (new PhpCsFixer\Finder())
            ->in([
                __DIR__ . '/src',
                __DIR__ . '/tests',
            ])
    );
