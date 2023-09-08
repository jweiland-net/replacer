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
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class ReplacerHelperTest extends UnitTestCase
{
    protected ConfigurationManager $configurationManager;

    protected LoggerInterface $logger;

    protected ReplacerHelper $subject;

    protected function setUp(): void
    {
        $this->configurationManager = $this->getMockBuilder(ConfigurationManager::class)
            ->disableOriginalConstructor()->getMock();
        $this->logger = new NullLogger();

        parent::setUp();
    }

    protected function setupSubject(): void
    {
        $this->subject = new ReplacerHelper();
        $this->subject->setLogger($this->logger);
    }

    /**
     * @returnDataProvider
     */
    public function validReplacements(): array
    {
        return [
            [
                'config' => [
                    'config' => [
                        'tx_replacer.' => [
                            'search.' => [
                                'apple',
                                'coke',
                            ],
                            'replace.' => [
                                'banana',
                                'pepsi',
                            ],
                        ],
                    ],
                ],
                'contentToReplace' => 'apple and coke are great',
                'result' => 'banana and pepsi are great',
            ],
            [
                'config' => [
                    'config' => [
                        'tx_replacer.' => [
                            'enable_regex' => 1,
                            'search.' => [
                                10 => '/apple|raspberry/',
                                20 => '/coke|sinalco/',
                            ],
                            'replace.' => [
                                10 => 'banana',
                                20 => 'pepsi',
                            ],
                        ],
                    ],
                ],
                'contentToReplace' => 'Today some apples and coke. Tomorrow a raspberry cake and sinalco.',
                'result' => 'Today some bananas and pepsi. Tomorrow a banana cake and pepsi.',
            ],
            [
                'config' => [
                    'config' => [
                        'tx_replacer.' => [
                            'search.' => [
                                10 => 'value to be hashed',
                            ],
                            'replace.' => [
                                10 => 'just some text',
                                '10.' => [
                                    'stdWrap.' => [
                                        'hash' => 'md5',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'contentToReplace' => 'testing stdWrap: "value to be hashed"',
                'result' => 'testing stdWrap: "a97c9d4fc5a556d84567173f014d4fdb"',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider validReplacements
     */
    public function replaceContentWithValidSearchAndReplaceValues(array $config, string $contentToReplace, string $result): void
    {
        $this->setupSubject();
        $this->createFrontendControllerMock($config);
        $actualResult = $this->subject->replace($contentToReplace);
        self::assertSame($result, $actualResult);
    }

    public function missingSearchOrReplaceValues(): array
    {
        return [
            [
                'config' => [
                    'config' => [
                        'tx_replacer.' => [
                            'search.' => [
                                'apple',
                                'coke',
                            ],
                            'replace.' => [
                                'banana',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'config' => [
                    'config' => [
                        'tx_replacer.' => [
                            'search.' => [
                                'apple',
                            ],
                            'replace.' => [
                                'banana',
                                'pepsi',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider missingSearchOrReplaceValues
     */
    public function replaceContentWithMissingSearchOrReplaceValuesWritesLogEntry(array $config): void
    {
        $this->setupSubject();
        $this->createFrontendControllerMock($config);
        $this->subject->replace('hello world');
    }

    /**
     * Create a mock TypoScriptFrontendController instance.
     */
    protected function createFrontendControllerMock(array $config = []): void
    {
        $GLOBALS['TSFE'] = $this->getMockBuilder(TypoScriptFrontendController::class)
            ->disableOriginalConstructor()
            ->getMock();
        $GLOBALS['TSFE']->cObj = new ContentObjectRenderer($GLOBALS['TSFE']);
        // Set the configuration
        $configProperty = new \ReflectionProperty($GLOBALS['TSFE'], 'config');
        $configProperty->setAccessible(true);
        ArrayUtility::mergeRecursiveWithOverrule($GLOBALS['TSFE']->config, $config);
    }
}
