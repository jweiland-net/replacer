<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/replacer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Replacer\Tests\Functional\Middleware;

use JWeiland\Replacer\Tests\Functional\Traits\SetUpFrontendSiteTrait;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class ReplaceContentMiddlewareTest extends FunctionalTestCase
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
            ],
        );
    }

    #[Test]
    public function frontendRequestReplacesContentAsDescribedInTypoScriptOnPageWithUserInt(): void
    {
        $request = new InternalRequest();
        $request = $request->withPageId(1);

        $content = $this->executeFrontendSubRequest($request)->getBody();

        self::assertEquals(
            '<p>I like fruits</p><p>This is MD5 Hash Example: 0800fc577294c34e0b28ad2839435945</p><p>Hello world</p>',
            $content,
        );
    }
}
