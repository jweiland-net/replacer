<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/replacer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Replacer\Tests\Functional\Middleware;

use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\ResponseContent;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class ReplaceContentTest extends FunctionalTestCase
{
    /**
     * @var array
     */
    protected array $testExtensionsToLoad = [
        'jweiland/replacer'
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/pages.csv');
        $this->setUpFrontendRootPage(
            1,
            [
                __DIR__ . '/../Fixtures/basic_template.typoscript',
                __DIR__ . '/../Fixtures/user_int.typoscript'
            ]
        );
    }

    /**
     * @test
     */
    public function frontendRequestReplacesContentAsDescribedInTypoScriptOnPageWithUserInt(): void
    {
        var_dump($this->getFrontendResponse(1)->getResponseContent());
        self::assertEquals(
            '<p>I like bananas</p><p>Hello world</p>',
            $this->getFrontendResponse(1)->getResponseContent()
        );
    }
}
