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
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Middleware to replace content using TSFE.
 * Will be used for pages with USER_INT plugins only!
 * Otherwise, TypoScriptFrontendControllerHook will replace the content.
 */
class ReplaceContentMiddleware implements MiddlewareInterface
{
    private ReplacerHelper $replacerHelper;

    public function __construct(ReplacerHelper $replacerHelper)
    {
        $this->replacerHelper = $replacerHelper;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if ($response instanceof NullResponse || $this->getContentObjectRenderer($request) === null) {
            return $response;
        }

        $content = $this->replacerHelper->replace((string)$response->getBody(), $request);
        $body = new Stream('php://temp', 'rw');
        $body->write($content);

        return $response->withBody($body);
    }

    protected function getContentObjectRenderer(ServerRequestInterface $request): ?ContentObjectRenderer
    {
        $tsfeController = $request->getAttribute('frontend.controller');
        if (isset($tsfeController->cObj) && $tsfeController->cObj instanceof ContentObjectRenderer) {
            return $tsfeController->cObj;
        }

        return null;
    }
}
