<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/replacer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Replacer\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\NullResponse;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Middleware to replace content using TSFE.
 */
class ReplaceContent implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (!($response instanceof NullResponse)
        && $GLOBALS['TSFE'] instanceof TypoScriptFrontendController
        && 'text/html' == substr($response->getHeaderLine('Content-Type'), 0, 9)
        && !empty($response->getBody())
        ) {
            $content = GeneralUtility::makeInstance(\JWeiland\Replacer\Helper\ReplacerHelper::class)
                ->replace((string) $response->getBody(), $GLOBALS['TSFE']);

            $responseBody = new Stream('php://temp', 'rw');
            $responseBody->write($content);
            $response = $response->withBody($responseBody);
        }

        return $response;
    }
}
