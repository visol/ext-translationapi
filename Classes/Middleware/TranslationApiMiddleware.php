<?php

namespace Sinso\Translationapi\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sinso\Translationapi\Extbase\RouteHandler;
use Sinso\Translationapi\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Participant in processing a server request and response
 *
 * An HTTP middleware component participates in processing an HTTP message:
 * by acting on the request, generating the response, or forwarding the
 * request to a subsequent middleware and possibly acting on its response.
 */
class TranslationApiMiddleware implements MiddlewareInterface
{
    protected Context $context;

    public function __construct(RouteHandler $handler)
    {
        $this->context = GeneralUtility::makeInstance(Context::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $normalizedParams = $request->getAttribute('normalizedParams');
        $uri = $normalizedParams->getRequestUri();

        if (strpos($uri, '/t3api/translation') === 0) {
            // todo make configurable
            $pathComponents = explode('/', $uri);

            // todo improve error handling
            $languageKey = (string) $pathComponents[3];
            $extensionKey = (string) $pathComponents[4];
            $prefix = (string) $pathComponents[5];

            $labels = LocalizationUtility::getLabels($extensionKey, $prefix, $languageKey);

            $omitPrefix = GeneralUtility::_GET('omitPrefix');
            if ($omitPrefix === 'yes' && !empty($prefix)) {
                $labels = LocalizationUtility::stripPrefix($labels, $prefix);
            }

            $expand = GeneralUtility::_GET('expand');
            if ($expand === 'yes') {
                $labels = LocalizationUtility::expandKeys($labels);
            }

            $body = new Stream('php://temp', 'rw');
            $body->write(json_encode($labels));
            return (new Response())
                ->withHeader('content-type', 'application/json; charset=utf-8')
                ->withBody($body)
                ->withStatus(200);
        }

        return $handler->handle($request);
    }
}
