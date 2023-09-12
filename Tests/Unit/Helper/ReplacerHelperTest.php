<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/replacer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Replacer\Tests\Unit\Helper;

use JWeiland\Replacer\Helper\ReplacerHelper;
use JWeiland\Replacer\Helper\TypoScriptHelper;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class ReplacerHelperTest extends UnitTestCase
{
    /**
     * @var ConfigurationManager|MockObject
     */
    protected $configurationManagerMock;

    /**
     * @var LoggerInterface|MockObject
     */

    protected ReplacerHelper $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configurationManagerMock = $this->createMock(ConfigurationManager::class);

        $this->subject = new ReplacerHelper(new TypoScriptHelper());
    }

    /**
     * @returnDataProvider
     */
    public function validReplacements(): array
    {
        return [
            'Replace values with simple text replacement' => [
                ['10' => 'apple', '20' => 'coke'],
                ['10' => 'banana', '20' => 'pepsi'],
                'contentToReplace' => 'Today some apples and coke. Tomorrow a raspberry cake and coke.',
                'result' => 'Today some bananas and pepsi. Tomorrow a raspberry cake and pepsi.',
            ],
            'Replace values with regular expressions' => [
                [
                    '10' => '/apple|raspberry/',
                    '10.' => [ 'enable_regex' => '1'],
                    '20' => '/coke|sinalco/',
                    '20.' => [ 'enable_regex' => '1'],
                ],
                ['10' => 'banana', '20' => 'pepsi'],
                'contentToReplace' => 'Today some apple and coke. Tomorrow a raspberry cake and sinalco.',
                'result' => 'Today some banana and pepsi. Tomorrow a banana cake and pepsi.',
            ],
            'Replace value with another value which will be hashed with stdWrap property "hash"' => [
                ['10' => 'value to be replaced by a hashed value'],
                ['10' => 'value to be hashed', '10.' => ['hash' => 'md5']],
                'contentToReplace' => 'Test stdWrap "hash" property: "3074b9e3a338bda3e97c9f60263e308f"',
                'result' => 'Test stdWrap "hash" property: "3074b9e3a338bda3e97c9f60263e308f"',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider validReplacements
     */
    public function replaceContentWithValidSearchAndReplaceValues(
        array $search,
        array $replacement,
        string $contentToReplace,
        string $result
    ): void {
        $config = [
            'config' => [
                'tx_replacer.' => [
                    'search.' => $search,
                    'replace.' => $replacement,
                ],
            ],
        ];

        $this->createFrontendControllerMock($config);

        $actualResult = $this->subject->replace($contentToReplace);

        self::assertSame(
            $result,
            $actualResult
        );
    }

    /**
     * Create a TypoScriptFrontendController mock instance.
     */
    protected function createFrontendControllerMock(array $config = []): void
    {
        $GLOBALS['TSFE'] = $this->createMock(TypoScriptFrontendController::class);
        $GLOBALS['TSFE']->cObj = new ContentObjectRenderer($GLOBALS['TSFE']);

        // Set the configuration
        $configProperty = new \ReflectionProperty($GLOBALS['TSFE'], 'config');
        $configProperty->setAccessible(true);
        ArrayUtility::mergeRecursiveWithOverrule($GLOBALS['TSFE']->config, $config);
    }
}
