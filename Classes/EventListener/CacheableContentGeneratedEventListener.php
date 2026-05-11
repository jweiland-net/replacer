<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/replacer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Replacer\EventListener;

use JWeiland\Replacer\Helper\ReplacerHelper;
use TYPO3\CMS\Frontend\Event\AfterCacheableContentIsGeneratedEvent;

/**
 * This event listener handles the Cacheable Content Generation process
 * after the CacheableContentIsGeneratedEvent has occurred in TYPO3.
 * It allows for custom actions to be taken when cacheable content is generated.
 * The idea of this implementation is mainly for replacing the Hook implemented with 'contentPostProc-all' (It is
 * removed in TYPO3 12) the implementation is same as the ReplaceContent Middleware (which only replace for
 * USER_INT Plugins)
 */
#[AsEventListener(
    identifier: 'replacer/content-modifier',
    before: 'someIdentifier, anotherIdentifier',
)]
final readonly class CacheableContentGeneratedEventListener
{
    public function __construct(private ReplacerHelper $replacerHelper) {}

    /**
     * __invoke method for AfterCacheableContentIsGeneratedEvent
     * This event listener registered inside Configuration/Settings.yaml
     */
    public function __invoke(AfterCacheableContentIsGeneratedEvent $event): void
    {
        // Only do this when caching is enabled
        if (!$event->isCachingEnabled()) {
            return;
        }

        $event->setContent(
            $this->replacerHelper->replace(
                $event->getContent(),
                $event->getRequest(),
            ),
        );
    }
}
