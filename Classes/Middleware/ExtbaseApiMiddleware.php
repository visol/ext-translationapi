<?php

namespace Sinso\Translationapi\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sinso\Translationapi\Extbase\RouteHandler;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Error\Http\BadRequestException;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Middleware\TypoScriptFrontendInitialization;

class ExtbaseApiMiddleware implements MiddlewareInterface
{
    private RouteHandler $apiHandler;

    protected Context $context;

    public function __construct(RouteHandler $handler)
    {
        $this->apiHandler = $handler;
        $this->context = GeneralUtility::makeInstance(Context::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $normalizedParams = $request->getAttribute('normalizedParams');
        $uri = $normalizedParams->getRequestUri();

        if (strpos($uri, '/api/ping') === 0) {
            $pathComponents = explode('/', $uri);
            $path = parse_url($pathComponents[2]);
            $command = (string) $path['path'];

            $request = $this->initializeLanguage($request);
            $request = $this->initializeTSFE($request);

            $this->apiHandler->handle($request);

            $result = '';
            if ($command) {
                $result = 'asdf';
            }

            $body = new Stream('php://temp', 'rw');
            $body->write($result);
            return (new Response())
                ->withHeader('content-type', 'text/plain; charset=utf-8')
                ->withBody($body)
                ->withStatus(200);
        }

        //        $routeResult = $this->matcher->matchRequest($request);
        //
        //        $request = $request->withAttribute('site', $routeResult->getSite());
        //        $request = $request->withAttribute('language', $routeResult->getLanguage());

        return $handler->handle($request);
    }

    /**
     * @throws BadRequestException
     */
    protected function initializeLanguage(ServerRequestInterface $request): ServerRequestInterface
    {
        //        if (array_key_exists('language', $request->getQueryParams())) {
        //            $language = $request->getQueryParams()['language'];
        //            $isValidLanguage = false;
        //
        //            foreach ($this->siteService->getSite()->getLanguages() as $languageObject) {
        //                $languageKeyAndCountryKey = $languageObject->getTwoLetterIsoCode() . '-' . $languageObject->toArray()['bt3CountryKey'];
        //                if ($language === $languageKeyAndCountryKey) {
        //                    $isValidLanguage = true;
        //                    break;
        //                }
        //            }
        //
        //            if (!$isValidLanguage) {
        //                throw new BadRequestException('Invalid language parameter provided.', 1613143375);
        //            }
        //        } else {
        //            $languageObject = $this->siteService->getSite()->getDefaultLanguage();
        //        }
        //
        //        $this->context->setAspect(
        //            'language',
        //            GeneralUtility::makeInstance(
        //                LanguageAspect::class,
        //                $languageObject->getLanguageId(),
        //                null,
        //                LanguageAspect::OVERLAYS_ON,
        //                []
        //            )
        //        );
        //
        //        Locales::setSystemLocaleFromSiteLanguage($languageObject);

        $site = $request->getAttribute('site', null);
        $language = $site->getDefaultLanguage();

        return $request->withAttribute('language', $language);
    }

    protected function initializeTSFE(ServerRequestInterface $request): ServerRequestInterface
    {
        $request = $request->withAttribute(
            'routing',
            new PageArguments(
                (int) 1,
                (string) ($request->getQueryParams()['type'] ?? '0'),
                [],
                [],
                $request->getQueryParams(),
            ),
        );

        $tsfeInitialization = new TypoScriptFrontendInitialization($this->context);
        $tsfeInitialization->process(
            $request,
            new class implements RequestHandlerInterface {
                public function handle(ServerRequestInterface $request): ResponseInterface
                {
                    return new Response();
                }
            },
        );

        $tsfe = $this->getTypoScriptFrontendController();
        $tsfe->newCObj();
        //        $this->configurationManager->setContentObject($tsfe->cObj);

        return $request;
    }

    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
