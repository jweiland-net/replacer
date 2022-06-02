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

class TypoScriptFrontendControllerTest extends AbstractFunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/replacer'
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importDataSet(__DIR__ . '/../Fixtures/pages.xml');
    }

    /**
     * @test
     */
    public function frontendRequestUsesReplacerForBasicReplacementOnPageWithoutUserInt(): void
    {
        $this->setUpFrontendRootPage(1, [__DIR__ . '/../Fixtures/basic_template.typoscript']);

        self::assertEquals(
            '<p>I like bananas</p>',
            $this->getFrontendResponse(1)->getContent()
        );
    }
}
