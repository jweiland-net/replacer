<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/replacer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Replacer\Tests\Functional\Hooks;

use Nimut\TestingFramework\TestCase\AbstractFunctionalTestCase;

class ReplaceContentTest extends AbstractFunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/replacer'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importDataSet(__DIR__ . '/../Fixtures/pages.xml');
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
        self::assertEquals(
            'I like bananas',
            $this->getFrontendResponse(1)->getContent()
        );
    }

    // TODO: Add more tests. Check if middleware was called at least once
}
