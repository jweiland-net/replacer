<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/replacer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Replacer\Tests\Functional\Helper;

use JWeiland\Replacer\Helper\ReplacerHelper;
use JWeiland\Replacer\Helper\TypoScriptHelper;
use JWeiland\Replacer\Tests\Functional\Traits\SetUpFrontendSiteTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class ReplacerHelperTest extends FunctionalTestCase
{
    use SetUpFrontendSiteTrait;

    /**
     * @var ConfigurationManager|MockObject
     */
    protected MockObject $configurationManagerMock;

    protected ReplacerHelper $subject;

    protected ServerRequestInterface $request;

    protected array $testExtensionsToLoad = [
        'jweiland/replacer',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('https://www.example.com/'));

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/pages.csv');
        $this->setUpFrontendSite(1);

        $this->configurationManagerMock = $this->createMock(ConfigurationManager::class);

        $this->subject = new ReplacerHelper(new TypoScriptHelper());

        $contentObjectRendererMock = $this->createMock(ContentObjectRenderer::class);
        $controllerMock = $this->createMock(TypoScriptFrontendController::class);
        $controllerMock->cObj = $contentObjectRendererMock;

        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setSetupArray([]);

        $this->request = (new ServerRequest())
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('frontend.controller', $controllerMock)
            ->withAttribute('frontend.typoscript', $frontendTypoScript);
    }

    /**
     * @returnDataProvider
     */
    public static function validReplacements(): array
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
                    '10.' => ['enable_regex' => '1'],
                    '20' => '/coke|sinalco/',
                    '20.' => ['enable_regex' => '1'],
                ],
                ['10' => 'banana', '20' => 'pepsi'],
                'contentToReplace' => 'Today some apple and coke. Tomorrow a raspberry cake and sinalco.',
                'result' => 'Today some banana and pepsi. Tomorrow a banana cake and pepsi.',
            ],
            'Replace value with another value which will be hashed with stdWrap property "hash"' => [
                ['10' => 'value to be replaced by a hashed value'],
                ['10' => 'value to be hashed', '10.' => ['hash' => 'md5']],
                'contentToReplace' => 'Test stdWrap "hash" property: "value to be replaced by a hashed value"',
                'result' => 'Test stdWrap "hash" property: "3074b9e3a338bda3e97c9f60263e308f"',
            ],
            'Use the search value, hash it and replace it' => [
                ['10' => 'value to be hashed', '10.' => ['setContentToCurrent' => '1']],
                ['10.' => ['current' => '1', 'hash' => 'md5']],
                'contentToReplace' => 'Test stdWrap "hash" property: "value to be hashed"',
                'result' => 'Test stdWrap "hash" property: "3074b9e3a338bda3e97c9f60263e308f"',
            ],
            'Search for page title and replace it with the nav_title' => [
                ['10.' => ['field' => 'title']],
                ['10.' => ['data' => 'FIELD:nav_title', 'wrap' => '<em>|</em>']],
                'contentToReplace' => 'This is my first Startpage',
                'result' => 'This is my first <em>Car</em>',
            ],
            'Replace ContentObject with replacement' => [
                ['10.' => ['cObject' => 'TEXT', 'cObject.' => ['value' => 'WordPress']]],
                ['10' => 'TYPO3'],
                'contentToReplace' => 'WordPress is the best CMS',
                'result' => 'TYPO3 is the best CMS',
            ],
        ];
    }

    #[Test]
    #[DataProvider('validReplacements')]
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

        $actualResult = $this->subject->replace($contentToReplace, $this->request);

        self::assertSame(
            $result,
            $actualResult,
        );
    }

    /**
     * Create a TypoScriptFrontendController mock instance.
     */
    protected function createFrontendControllerMock(array $config = []): void
    {
        $controllerMock = $this->createMock(TypoScriptFrontendController::class);
        $controllerMock->cObj = new ContentObjectRenderer($controllerMock);
        $controllerMock->cObj->data = [
            'uid' => 1,
            'pid' => 0,
            'title' => 'Startpage',
            'nav_title' => 'Car',
        ];

        // Set the configuration
        $configProperty = new \ReflectionProperty($controllerMock, 'config');
        $configProperty->setAccessible(true);
        ArrayUtility::mergeRecursiveWithOverrule($controllerMock->config, $config);

        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setSetupArray([]);

        $controllerMock->config = $config;

        $this->request = (new ServerRequest())
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('frontend.controller', $controllerMock)
            ->withAttribute('frontend.typoscript', $frontendTypoScript);
    }
}
