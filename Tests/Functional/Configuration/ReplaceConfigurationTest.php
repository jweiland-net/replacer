<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/replacer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Replacer\Tests\Functional\Configuration;

use JWeiland\Replacer\Configuration\ReplaceConfiguration;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class ReplaceConfigurationTest extends FunctionalTestCase
{
    protected ReplaceConfiguration $subject;

    protected array $testExtensionsToLoad = [
        'jweiland/replacer',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new ReplaceConfiguration();
    }

    #[Test]
    public function getSearchValueInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getSearchValue(),
        );
    }

    #[Test]
    public function setSearchValueWillSetSearchValue(): void
    {
        $this->subject->setSearchValue('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getSearchValue(),
        );
    }

    #[Test]
    public function getReplaceValueInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getReplaceValue(),
        );
    }

    #[Test]
    public function setReplaceValueSetsReplaceValue(): void
    {
        $this->subject->setReplaceValue('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getReplaceValue(),
        );
    }

    #[Test]
    public function getUseRegExpInitiallyReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->isUseRegExp(),
        );
    }

    #[Test]
    public function setUseRegExpSetsUseRegExp(): void
    {
        $this->subject->setUseRegExp(true);

        self::assertTrue(
            $this->subject->isUseRegExp(),
        );
    }
}
