<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/replacer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Replacer\Tests\Functional\Hooks;

use JWeiland\Replacer\Tests\Functional\Traits\SetUpFrontendSiteTrait;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class TypoScriptFrontendControllerTest extends FunctionalTestCase
{
    use SetUpFrontendSiteTrait;

    protected array $testExtensionsToLoad = [
        'jweiland/replacer',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/pages.csv');
        $this->setUpFrontendSite(1);
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:replacer/Tests/Functional/Fixtures/basic_template.typoscript',
                'EXT:replacer/Tests/Functional/Fixtures/user_int.typoscript',
            ]
        );
    }

    /**
     * @test
     */
    public function frontendRequestUsesReplacerForBasicReplacementOnPageWithoutUserInt(): void
    {
        self::assertEquals(
            '<p>I like bananas</p><p>Hello world</p>',
            $this->getFrontendResponse(1)->getContent()
        );
    }
}
