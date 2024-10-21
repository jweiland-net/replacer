<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/replacer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Replacer\Tests\Functional\FrontendRequest;

use JWeiland\Replacer\Tests\Functional\Traits\SetUpFrontendSiteTrait;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class ReplaceContentCachedTest extends FunctionalTestCase
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
            ],
        );
    }

    /**
     * @test
     */
    public function frontendRequestDoNotReplacesContentOnTypo3Error(): void
    {
        $response = self::executeFrontendSubRequest(
            new InternalRequest('https://website.local/site-not-found'),
        );

        $body = (string)$response->getBody();

        // If page was not found, do nothing. Let TYPO3 throw the error, but do not try to replace anything
        // which will result in an uninitialized TSFE or methods on null exception
        self::assertStringNotContainsString(
            'TSFE',
            $body,
        );
        self::assertStringNotContainsString(
            'null',
            $body,
        );
    }

    /**
     * @test
     */
    public function frontendRequestReplacesContent(): void
    {
        $response = self::executeFrontendSubRequest(
            new InternalRequest('https://website.local/'),
        );

        $body = (string)$response->getBody();

        self::assertStringContainsString(
            'I like fruits',
            $body,
        );
    }
}
