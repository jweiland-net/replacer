<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/replacer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Replacer\Tests\Functional\Enumeration;

use JWeiland\Replacer\Enumeration\ConfigurationTypeEnumeration;
use TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class ConfigurationTypeEnumerationTest extends FunctionalTestCase
{
    protected ConfigurationTypeEnumeration $subject;

    protected array $testExtensionsToLoad = [
        'jweiland/replacer',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new ConfigurationTypeEnumeration();
    }

    /**
     * @test
     */
    public function configurationTypeAsStringWillReturnDefaultValue(): void
    {
        self::assertSame(
            'search.',
            (string)$this->subject
        );
    }

    /**
     * @test
     */
    public function setValueToSearchWillAppendDot(): void
    {
        $subject = new ConfigurationTypeEnumeration('search');

        self::assertSame(
            'search.',
            (string)$subject
        );
    }

    /**
     * @test
     */
    public function setValueToReplaceWillAppendDot(): void
    {
        $subject = new ConfigurationTypeEnumeration('replace');

        self::assertSame(
            'replace.',
            (string)$subject
        );
    }

    /**
     * @test
     */
    public function setUnknownValueWillThrowException(): void
    {
        self::expectException(InvalidEnumerationValueException::class);

        new ConfigurationTypeEnumeration('reload');
    }
}
