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

    /**
     * @test
     */
    public function getSearchValueInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getSearchValue()
        );
    }

    /**
     * @test
     */
    public function setSearchValueWillSetSearchValue(): void
    {
        $this->subject->setSearchValue('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getSearchValue()
        );
    }

    /**
     * @test
     */
    public function getReplaceValueInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getReplaceValue()
        );
    }

    /**
     * @test
     */
    public function setReplaceValueSetsReplaceValue()
    {
        $this->subject->setReplaceValue('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getReplaceValue()
        );
    }

    /**
     * @test
     */
    public function getUseRegExpInitiallyReturnsFalse()
    {
        self::assertFalse(
            $this->subject->isUseRegExp()
        );
    }

    /**
     * @test
     */
    public function setUseRegExpSetsUseRegExp()
    {
        $this->subject->setUseRegExp(true);

        self::assertTrue(
            $this->subject->isUseRegExp()
        );
    }
}
