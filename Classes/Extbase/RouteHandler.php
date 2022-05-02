<?php

declare(strict_types=1);

namespace Sinso\Translationapi\Extbase;

use LMS\Routes\Support\Response;
use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Core\Bootstrap;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use LMS\Routes\Support\{ErrorBuilder, ServerRequest};
use Symfony\Component\Routing\Exception\NoConfigurationException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Psr\Http\{Message\ResponseInterface, Message\ServerRequestInterface};
use LMS\Routes\{Controller\ManagementController, Domain\Model\Middleware, Domain\Model\Route, Service\RouteService};

class RouteHandler
{
    private Response $response;
    private ErrorBuilder $error;
    private Bootstrap $bootstrap;
    private RouteService $routeService;

    /**
     * Basically will contain the response text
     * which is generated after execution of the extbase action.
     */
    private string $output = '';

    /**
     * Contains the response status code.
     */
    private int $status = 200;

    public function __construct(RouteService $service, Bootstrap $bootstrap, Response $response, ErrorBuilder $error)
    {
        $this->error = $error;
        $this->response = $response;
        $this->bootstrap = $bootstrap;
        $this->routeService = $service;
    }

    /**
     * @throws NoConfigurationException
     * @throws ResourceNotFoundException
     * @throws PropagateResponseException
     */
    public function handle(ServerRequestInterface $request)
    {
        $slug = $request->getUri()->getPath();

        try {
            $this->processRoute($request, $this->routeService->findRouteFor($slug));
        } catch (MethodNotAllowedException $exception) {
            $this->output = $this->error->messageFor($exception);
            $this->status = (int) $exception->getCode() ?: 200;
        }
    }

    /**
     * Creates the PSR7 Response based on output that was retrieved from FrontendRequestHandler
     */
    public function generateResponse(): ResponseInterface
    {
        return $this->response->createWith($this->output, $this->status);
    }

    /**
     * @throws MethodNotAllowedException
     * @throws PropagateResponseException
     */
    private function processRoute(ServerRequestInterface $request, Route $route): void
    {
        $GLOBALS['TSFE']->set_no_cache();
        $GLOBALS['TSFE']->determineId($request);
        $GLOBALS['TSFE']->getConfigArray();

        $this->processMiddleware($request->withQueryParams($route->getArguments()));

        $this->createActionArgumentsFrom($route);

        // https://stackoverflow.com/questions/21139769/typo3-extbase-ajax-without-page-typenum
        // https://www.weberino.com/ajax-endpoint-in-typo3-using-middleware-in-version-10/
        // http://localhost:8419/api/ping
        // https://www.sgalinski.de/en/typo3-agentur/technical-documentation/extensions-in-use/sg-ajax/
        // https://github.com/bnf/typo3-middleware (to be tested)https://www.youtube.com/watch?v=au75HNe8EoE
        // continue me...
        $this->bootstrap([
            'pluginName' => $route->getPlugin(),
            'vendorName' => $route->getController()->getVendor(),
            'extensionName' => $route->getController()->getExtension(),
            // todo uncomment this code to test
            //            'controller' => ManagementController::class,
            //            'action' => 'ping',
            //            'mvc' => array (
            //                'requestHandlers' => array (
            //                    'TYPO3\CMS\Extbase\Mvc\Web\FrontendRequestHandler' => 'TYPO3\CMS\Extbase\Mvc\Web\FrontendRequestHandler'
            //                )
            //            ),
            //            'settings' => [],
            //            'persistence' => array (
            //                'storagePid' => 1
            //            ),
        ]);
    }

    /**
     * Check whether a route has any middleware and run them if any.
     *
     * @throws MethodNotAllowedException
     * @throws PropagateResponseException
     */
    private function processMiddleware(ServerRequestInterface $request): void
    {
        $debugMode = $GLOBALS['TYPO3_CONF_VARS']['FE']['disableRoutesMiddleware'] ?? false;

        if ($debugMode) {
            return;
        }

        $slug = $request->getUri()->getPath();

        foreach ($this->routeService->findMiddlewareFor($slug) as $middlewareRoute) {
            $middleware = GeneralUtility::makeInstance(Middleware::class);
            $middleware->setRoute($middlewareRoute);

            $middleware->process($request);
        }
    }

    /**
     * Mainly parse the current server request and bind existing request parameters
     * as extbase action arguments.
     */
    private function createActionArgumentsFrom(Route $route): void
    {
        $plugin = $route->getPluginNamespace();

        ServerRequest::withParameter('action', $route->getAction(), $plugin);
        ServerRequest::withParameter('format', $route->getFormat(), $plugin);

        foreach ($route->getArguments() as $name => $value) {
            if (is_string($value) && ServerRequest::isJson($value)) {
                $value = json_decode($value, true);
            }

            ServerRequest::withParameter($name, $value, $plugin);
        }

        if (ServerRequest::isFormSubmit()) {
            foreach (ServerRequest::formBody() as $name => $value) {
                ServerRequest::withParameter($name, (string) $value, $plugin);
            }
        }
    }

    /**
     * Runs the Extbase Framework by resolving an appropriate Request Handler and passing control to it.
     *
     * @param array<string, string> $config
     */
    private function bootstrap(array $config): void
    {
        $this->output = $this->bootstrap->run('', $config);
    }
}
