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
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class ConfigurationTypeEnumerationTest extends FunctionalTestCase
{
    #[Test]
    public function enumSearchValueIsCorrect(): void
    {
        self::assertSame('search', ConfigurationTypeEnumeration::SEARCH->value);
    }

    #[Test]
    public function enumReplaceValueIsCorrect(): void
    {
        self::assertSame('replace', ConfigurationTypeEnumeration::REPLACE->value);
    }

    #[Test]
    public function validEnumFromString(): void
    {
        $enum = ConfigurationTypeEnumeration::from('replace');
        self::assertSame(ConfigurationTypeEnumeration::REPLACE, $enum);
    }

    #[Test]
    public function invalidEnumFromStringThrowsException(): void
    {
        $this->expectException(\ValueError::class);
        ConfigurationTypeEnumeration::from('invalid');
    }

    #[Test]
    public function tryFromReturnsNullForInvalidValue(): void
    {
        $enum = ConfigurationTypeEnumeration::tryFrom('invalid');
        self::assertNull($enum);
    }
}
