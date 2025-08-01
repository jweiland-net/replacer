<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/replacer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Replacer\Tests\Functional\Helper;

use JWeiland\Replacer\Helper\TypoScriptHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class TypoScriptHelperTest extends FunctionalTestCase
{
    protected TypoScriptHelper $subject;
    protected ServerRequestInterface $request;

    protected array $testExtensionsToLoad = [
        'jweiland/replacer',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new TypoScriptHelper();

        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setSetupArray([]);
        $this->request = (new ServerRequest())
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('frontend.typoscript', $frontendTypoScript);

        $GLOBALS['TYPO3_REQUEST'] = $this->request;
    }

    public static function hasStdWrapPropertiesDataProvider(): array
    {
        return [
            'Check TS config with int key' => [['10.' => ['wrap' => '<b>|</b>']], 10, true],
            'Check TS config with string key' => [['10.' => ['wrap' => '<b>|</b>']], '10', true],
            'Check TS config with string key and trailing dot' => [['10.' => ['wrap' => '<b>|</b>']], '10.', true],
            'Check TS config incl. enable_regex' => [['10.' => ['enable_regex' => '1']], 10, false],
            'Check TS config incl. stdwrap and incl. enable_regex' => [['10.' => ['wrap' => '<b>|</b>', 'enable_regex' => '1']], 10, true],
            'Check TS config without stdwrap, but with enable_regex' => [['10.' => ['enable_regex' => '1']], 10, false],
            'Check TS config without any properties' => [['10' => 'TEXT'], 10, false],
        ];
    }

    #[Test]
    #[DataProvider('hasStdWrapPropertiesDataProvider')]
    public function hasStdWrapPropertiesWillReturnFalse(array $config, $key, bool $expected): void
    {
        self::assertSame(
            $expected,
            $this->subject->hasStdWrapProperties($config, $key),
        );
    }

    public static function getStdWrapPropertiesDataProvider(): array
    {
        return [
            'TS config with int key' => [['10.' => ['wrap' => '<b>|</b>']], 10, ['wrap' => '<b>|</b>']],
            'TS config with string key' => [['10.' => ['wrap' => '<b>|</b>']], '10', ['wrap' => '<b>|</b>']],
            'TS config with string key and trailing dot' => [['10.' => ['wrap' => '<b>|</b>']], '10.', ['wrap' => '<b>|</b>']],
            'TS config with removed enable_regex' => [['10.' => ['wrap' => '<b>|</b>'], 'enable_regex' => '1'], 10, ['wrap' => '<b>|</b>']],
            'TS config with enable_regex will return empty string' => [['10.' => ['enable_regex' => '1']], 10, []],
            'TS config without stdwrap properties will return empty string' => [['10' => 'TEXT'], 10, []],
        ];
    }

    #[Test]
    #[DataProvider('getStdWrapPropertiesDataProvider')]
    public function getStdWrapPropertiesWillReturnFalse(array $config, $key, array $expected): void
    {
        self::assertSame(
            $expected,
            $this->subject->getStdWrapProperties($config, $key),
        );
    }

    #[Test]
    public function findValueOrConfigurationWillReturnEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->findValueOrConfiguration(
                [
                    20 => 'TEXT',
                ],
                10,
            ),
        );
    }

    #[Test]
    public function findValueOrConfigurationWillReturnBaseNode(): void
    {
        self::assertSame(
            'TEXT',
            $this->subject->findValueOrConfiguration(
                [
                    10 => 'TEXT',
                    '10.' => [
                        'value' => 'foo bar',
                    ],
                ],
                10,
            ),
        );
    }

    #[Test]
    public function findValueOrConfigurationWillReturnProperties(): void
    {
        self::assertSame(
            [
                'value' => 'foo bar',
            ],
            $this->subject->findValueOrConfiguration(
                [
                    '10.' => [
                        'value' => 'foo bar',
                    ],
                ],
                '10.',
            ),
        );
    }

    #[Test]
    public function isRegExpEnabledOnBaseNodeWillReturnFalse(): void
    {
        self::assertFalse(
            $this->subject->isRegExpEnabled(
                [
                    10 => 'TEXT',
                ],
                '10.',
            ),
        );
    }

    #[Test]
    public function isRegExpEnabledWithDisabledRegExWillReturnFalse(): void
    {
        self::assertFalse(
            $this->subject->isRegExpEnabled(
                [
                    10 => 'TEXT',
                    '10.' => [
                        'enable_regex' => '0',
                    ],
                ],
                '10.',
            ),
        );
    }

    #[Test]
    public function isRegExpEnabledWithoutRegExPropertyWillReturnFalse(): void
    {
        self::assertFalse(
            $this->subject->isRegExpEnabled(
                [
                    10 => 'TEXT',
                    '10.' => [
                        'wrap' => '<b>|</b>',
                    ],
                ],
                '10.',
            ),
        );
    }

    #[Test]
    public function isRegExpEnabledWithActivatedRegExWillReturnTrue(): void
    {
        self::assertTrue(
            $this->subject->isRegExpEnabled(
                [
                    10 => 'TEXT',
                    '10.' => [
                        'enable_regex' => '1',
                    ],
                ],
                '10.',
            ),
        );
    }

    #[Test]
    public function hasBaseEntryWillReturnTrue(): void
    {
        self::assertTrue(
            $this->subject->hasBaseEntry(
                [
                    10 => 'TEXT',
                    '10.' => [
                        'enable_regex' => '1',
                    ],
                ],
                '10.',
            ),
        );
    }

    #[Test]
    public function hasBaseEntryWillReturnFalse(): void
    {
        self::assertFalse(
            $this->subject->hasBaseEntry(
                [
                    '10.' => [
                        'wrap' => '<b>|</b>',
                    ],
                ],
                '10.',
            ),
        );
    }

    #[Test]
    public function hasReplaceEntryWithBaseNodeWillReturnTrue(): void
    {
        self::assertTrue(
            $this->subject->hasReplaceEntry(
                [
                    10 => 'TEXT',
                    '10.' => [
                        'wrap' => '<b>|</b>',
                    ],
                ],
                '10.',
            ),
        );
    }

    #[Test]
    public function hasReplaceEntryWithoutBaseNodeWillReturnTrue(): void
    {
        self::assertTrue(
            $this->subject->hasReplaceEntry(
                [
                    '10.' => [
                        'wrap' => '<b>|</b>',
                    ],
                ],
                '10.',
            ),
        );
    }

    #[Test]
    public function hasReplaceEntryWillReturnFalse(): void
    {
        self::assertFalse(
            $this->subject->hasReplaceEntry(
                [
                    10 => 'TEXT',
                    '10.' => [
                        'wrap' => '<b>|</b>',
                    ],
                ],
                20,
            ),
        );
    }

    #[Test]
    public function applyStdWrapProperties(): void
    {
        $contentObjectRendererMock = self::createMock(ContentObjectRenderer::class);
        $contentObjectRendererMock
            ->expects(self::atLeastOnce())
            ->method('stdWrap')
            ->with(self::equalTo('apple'), self::isType('array'))
            ->willReturn('<b>apple</b>');

        $controllerMock = self::createMock(TypoScriptFrontendController::class);
        $controllerMock->cObj = $contentObjectRendererMock;

        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setSetupArray([]);
        $this->request = (new ServerRequest())
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('frontend.controller', $controllerMock)
            ->withAttribute('frontend.typoscript', $frontendTypoScript);

        self::assertSame(
            '<b>apple</b>',
            $this->subject->applyStdWrapProperties(
                'apple',
                [
                    'wrap' => '<b>|</b>',
                ],
                $this->request
            ),
        );
    }
}
