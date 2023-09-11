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
    protected $loggerMock;

    protected ReplacerHelper $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configurationManagerMock = $this->createMock(ConfigurationManager::class);
        $this->loggerMock = $this->createMock(Logger::class);

        $this->subject = new ReplacerHelper();
        $this->subject->setLogger($this->loggerMock);
    }

    /**
     * @returnDataProvider
     */
    public function validReplacements(): array
    {
        return [
            'Replace values with simple text replacement' => [
                '0',
                ['10' => 'apple', '20' => 'coke'],
                ['10' => 'banana', '20' => 'pepsi'],
                'contentToReplace' => 'Today some apples and coke. Tomorrow a raspberry cake and sinalco.',
                'result' => 'Today some bananas and pepsi. Tomorrow a raspberry cake and sinalco.',
            ],
            'Replace values with regular expressions' => [
                '1',
                ['10' => '/apple|raspberry/', '20' => '/coke|sinalco/'],
                ['10' => 'banana', '20' => 'pepsi'],
                'contentToReplace' => 'Today some apples and coke. Tomorrow a raspberry cake and sinalco.',
                'result' => 'Today some bananas and pepsi. Tomorrow a banana cake and pepsi.',
            ],
            'Replace value with another value which will be hashed with stdWrap property "hash"' => [
                '0',
                ['10' => 'value to be hashed'],
                ['10' => 'banana', '20' => 'pepsi'],
                'contentToReplace' => 'Today some apples and coke. Tomorrow a raspberry cake and sinalco.',
                'result' => 'Today some bananas and pepsi. Tomorrow a banana cake and pepsi.',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider validReplacements
     */
    public function replaceContentWithValidSearchAndReplaceValues(
        string $enableRegExp,
        array $search,
        array $replacement,
        string $contentToReplace,
        string $result
    ): void {
        $config = [
            'config' => [
                'config' => [
                    'tx_replacer.' => [
                        'enable_regex' => $enableRegExp,
                        'search.' => $search,
                        'replace.' => $replacement,
                    ],
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

    public function invalidConfigurationForSearchAndReplace(): array
    {
        return [
            'Replacement with values as array keys will not work' => [['apple', 'coke'], ['banana', 'pepsi']],
            'Replacement with missing replacement entry will not work' => [['10' => ['apple', 'coke']], ['10' => ['banana']]],
            'Replacement with missing search entry will not work' => [['10' => ['apple']], ['10' => ['banana', 'pepsi']]],
        ];
    }

    /**
     * @test
     *
     * @dataProvider invalidConfigurationForSearchAndReplace
     */
    public function replaceContentWithMissingSearchOrReplaceValuesWritesLogEntry(array $search, array $replacement): void
    {
        $this->loggerMock
            ->expects(self::atLeastOnce())
            ->method('log')
            ->with(
                self::equalTo(LogLevel::ERROR),
                self::equalTo('Each search item must have a replace item!'),
                self::isType('array')
            );

        $config = [
            'config' => [
                'config' => [
                    'tx_replacer.' => [
                        'search.' => $search,
                        'replace.' => $replacement,
                    ],
                ],
            ],
        ];

        $this->createFrontendControllerMock($config);

        $this->subject->replace('hello world');
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
