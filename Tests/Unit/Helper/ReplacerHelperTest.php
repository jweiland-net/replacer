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
use ReflectionException;
use ReflectionProperty;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ReplacerHelperTest extends UnitTestCase
{
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
        $frontendController = $this->createFrontendControllerMock($config);
        $replacerHelper = new ReplacerHelper();
        $actualResult = $replacerHelper->replace($contentToReplace, $frontendController);
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
        $this->resetSingletonInstances = true;
        $frontendController = $this->createFrontendControllerMock($config);
        $logger = $this->createMock(Logger::class);
        $logger
            ->expects(self::once())
            ->method('log')
            ->with(
                LogLevel::ERROR,
                'Each search item must have a replace item!',
                self::anything()
            );

        $logManager = $this->createMock(LogManager::class);
        $logManager->method('getLogger')->with(ReplacerHelper::class)->willReturn($logger);
        GeneralUtility::setSingletonInstance(LogManager::class, $logManager);
        $replacerHelper = new ReplacerHelper();
        $replacerHelper->replace('hello world', $frontendController);
    }

    /**
     * Create a mock TypoScriptFrontendController instance.
     *
     * @param array $config Configuration array to set.
     *
     * @return TypoScriptFrontendController
     * @throws ReflectionException
     */
    protected function createFrontendControllerMock(array $config = []): TypoScriptFrontendController
    {
        $frontendController = $this->getMockBuilder(TypoScriptFrontendController::class)
            ->disableOriginalConstructor()
            ->getMock();
        $frontendController->cObj = new ContentObjectRenderer($frontendController);
        // Set the configuration
        $configProperty = new ReflectionProperty($frontendController, 'config');
        $configProperty->setAccessible(true);
        ArrayUtility::mergeRecursiveWithOverrule($frontendController->config, $config);
        return $frontendController;
    }
}
