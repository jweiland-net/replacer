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
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class ReplacerHelperTest extends UnitTestCase
{
    /**
     * @var TypoScriptFrontendController
     */
    private $typoscriptFrontendController;

    /**
     * @var ReplacerHelper
     */
    private $replacerHelper;

    protected function setUp(): void
    {
        $this->typoscriptFrontendController = $this
            ->getMockBuilder(TypoScriptFrontendController::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->typoscriptFrontendController->cObj = new ContentObjectRenderer($this->typoscriptFrontendController);
        $this->replacerHelper = new ReplacerHelper();
    }

    public function validReplacements(): array
    {
        return [
            [
                'config' => [
                    'config' => [
                        'tx_replacer.' => [
                            'search.' => [
                                'apple',
                                'coke'
                            ],
                            'replace.' => [
                                'banana',
                                'pepsi'
                            ]
                        ]
                    ]
                ],
                'contentToReplace' => 'apple and coke are great',
                'result' => 'banana and pepsi are great'
            ],
            [
                'config' => [
                    'config' => [
                        'tx_replacer.' => [
                            'enable_regex' => 1,
                            'search.' => [
                                10 => '/apple|raspberry/',
                                20 => '/coke|sinalco/'
                            ],
                            'replace.' => [
                                10 => 'banana',
                                20 => 'pepsi'
                            ]
                        ]
                    ],
                ],
                'contentToReplace' => 'Today some apples and coke. Tomorrow a raspberry cake and sinalco.',
                'result' => 'Today some bananas and pepsi. Tomorrow a banana cake and pepsi.'
            ],
            [
                'config' => [
                    'config' => [
                        'tx_replacer.' => [
                            'search.' => [
                                10 => 'value to be hashed'
                            ],
                            'replace.' => [
                                10 => 'just some text',
                                '10.' => [
                                    'stdWrap.' => [
                                        'hash' => 'md5'
                                    ],
                                ]
                            ]
                        ]
                    ]
                ],
                'contentToReplace' => 'testing stdWrap: "value to be hashed"',
                'result' => 'testing stdWrap: "a97c9d4fc5a556d84567173f014d4fdb"'
            ]
        ];
    }

    /**
     * @test
     * @dataProvider validReplacements
     */
    public function replaceContentWithValidSearchAndReplaceValues(array $config, string $contentToReplace, string $result): void
    {
        ArrayUtility::mergeRecursiveWithOverrule($this->typoscriptFrontendController->config, $config);
        self::assertSame(
            $result,
            $this->replacerHelper->replace($contentToReplace, $this->typoscriptFrontendController)
        );
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
                                'coke'
                            ],
                            'replace.' => [
                                'banana'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'config' => [
                    'config' => [
                        'tx_replacer.' => [
                            'search.' => [
                                'apple'
                            ],
                            'replace.' => [
                                'banana',
                                'pepsi'
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }

    /**
     * @test
     * @dataProvider missingSearchOrReplaceValues
     */
    public function replaceContentWithMissingSearchOrReplaceValuesWritesLogEntry(array $config): void
    {
        ArrayUtility::mergeRecursiveWithOverrule($this->typoscriptFrontendController->config, $config);

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

        $this->replacerHelper->replace('hello world', $this->typoscriptFrontendController);
    }
}
