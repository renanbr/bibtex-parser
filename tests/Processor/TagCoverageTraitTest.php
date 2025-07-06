<?php

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser\Test\Processor;

use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\TestCase;
use RenanBr\BibTexParser\Processor\TagCoverageTrait;

#[CoversNamespace('RenanBr\\BibTexParser\\Processor')]
class TagCoverageTraitTest extends TestCase
{
    public function testZeroConfigurationMustCoverAllTags(): void
    {
        $tagCoverage = new class {
            use TagCoverageTrait { getCoveredTags as public; }
        };
        $coverage = $tagCoverage->getCoveredTags(['bbb', 'ccc']);

        $this->assertSame(['bbb', 'ccc'], $coverage);
    }

    public function testWhitelistStrategy(): void
    {
        $tagCoverage = new class {
            use TagCoverageTrait { getCoveredTags as public; }
        };
        $tagCoverage->setTagCoverage(['aaa', 'bbb'], 'whitelist');
        $coverage = $tagCoverage->getCoveredTags(['bbb', 'ccc']);

        $this->assertSame(['bbb'], $coverage);
    }

    public function testDefaultStrategyMustActAsWhitelist(): void
    {
        $tagCoverage = new class {
            use TagCoverageTrait { getCoveredTags as public; }
        };
        $tagCoverage->setTagCoverage(['aaa', 'bbb']);
        $coverage = $tagCoverage->getCoveredTags(['bbb', 'ccc']);

        $this->assertSame(['bbb'], $coverage);
    }

    public function testBlacklist(): void
    {
        $tagCoverage = new class {
            use TagCoverageTrait { getCoveredTags as public; }
        };
        $tagCoverage->setTagCoverage(['aaa', 'bbb'], 'blacklist');
        $coverage = $tagCoverage->getCoveredTags(['bbb', 'ccc']);

        $this->assertSame(['ccc'], $coverage);
    }

    public function testCaseInsensitiveMatch(): void
    {
        $tagCoverage = new class {
            use TagCoverageTrait { getCoveredTags as public; }
        };
        $tagCoverage->setTagCoverage(['aaa', 'bbb']);
        $coverage = $tagCoverage->getCoveredTags(['BBB', 'ccc']);

        $this->assertSame(['BBB'], $coverage);
    }
}
