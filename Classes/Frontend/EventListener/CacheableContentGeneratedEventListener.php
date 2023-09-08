<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/replacer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Replacer\Frontend\EventListener;

use JWeiland\Replacer\Helper\ReplacerHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Event\AfterCacheableContentIsGeneratedEvent;

/**
 * This event listener handles the Cacheable Content Generation process
 * after the CacheableContentIsGeneratedEvent has occurred in TYPO3.
 * It allows for custom actions to be taken when cacheable content is generated.
 * The idea of this implementation is mainly for replacing the Hook implemented with 'contentPostProc-all' (It is
 * removed in TYPO3 12)
 */
final class CacheableContentGeneratedEventListener
{
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

        $event->getController()->content = $this->getReplaceHelper()
            ->replace($event->getController()->content);
    }

    /**
     * returns ReplaceHelper Class Object
     */
    public function getReplaceHelper(): ReplacerHelper
    {
        return GeneralUtility::makeInstance(ReplacerHelper::class);
    }
}
