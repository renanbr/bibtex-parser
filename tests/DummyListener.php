<?php

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser\Test;

use RenanBr\BibTexParser\ListenerInterface;

class DummyListener implements ListenerInterface
{
    public array $calls = [];

    public function bibTexUnitFound($text, $type, array $context): void
    {
        $this->calls[] = [
            $text,
            $type,
            $context,
        ];
    }
}
