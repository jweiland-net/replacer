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
use JWeiland\Replacer\Traits\GetTypoScriptFrontendControllerTrait;
use MongoDB\Driver\Server;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\NullResponse;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Stream;

/**
 * Middleware to replace content using TSFE.
 * Will be used for pages with USER_INT plugins only!
 * Otherwise, TypoScriptFrontendControllerHook will replace the content.
 */
class ReplaceContentMiddleware implements MiddlewareInterface
{
    use GetTypoScriptFrontendControllerTrait;

    private ReplacerHelper $replacerHelper;

    private ServerRequestInterface $request;

    public function __construct(ReplacerHelper $replacerHelper, ServerRequest $request)
    {
        $this->replacerHelper = $replacerHelper;
        $this->request = $request;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if ($response instanceof NullResponse || $this->getContentObjectRenderer() === null) {
            return $response;
        }

        $content = $this->replacerHelper->replace((string)$response->getBody());
        $body = new Stream('php://temp', 'rw');
        $body->write($content);

        return $response->withBody($body);
    }

    protected function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }
}
