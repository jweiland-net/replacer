<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/replacer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Replacer\Middleware;

use JWeiland\Replacer\Helper\ReplacerHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\NullResponse;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Middleware to replace content using TSFE.
 * Will be used for pages with USER_INT plugins only!
 * Otherwise Hooks\TypoScriptFrontendController will replace the content.
 */
class ReplaceContent implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if (!$GLOBALS['TSFE']->isINTincScript() || $response instanceof NullResponse) {
            return $response;
        }
        $content = GeneralUtility::makeInstance(ReplacerHelper::class)
            ->replace((string)$response->getBody(), $GLOBALS['TSFE']);

        $body = new Stream('php://temp', 'rw');
        $body->write($content);

        return $response->withBody($body);
    }
}
