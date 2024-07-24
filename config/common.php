<?php

declare(strict_types=1);

/**
 * @var array $params
 */

use Botasis\Client\Telegram\Client\ClientInterface;
use Botasis\Client\Telegram\Client\ClientPsr;
use Botasis\Runtime\Application;
use Botasis\Runtime\Event\IgnoredErrorHandler;
use Botasis\Runtime\Event\RequestTagsHandler;
use Botasis\Runtime\Middleware\Implementation\EnsureCallbackResponseMiddleware;
use Botasis\Runtime\Middleware\Implementation\RouterMiddleware;
use Botasis\Runtime\Middleware\MiddlewareDispatcher;
use Botasis\Runtime\Middleware\MiddlewareFactory;
use Botasis\Runtime\Middleware\MiddlewareFactoryInterface;
use Botasis\Runtime\Router\Router;
use Http\Message\MultipartStream\MultipartStreamBuilder;
use Psr\Http\Message\StreamFactoryInterface;
use Yiisoft\Definitions\DynamicReference;
use Yiisoft\Definitions\Reference;
use Yiisoft\Injector\Injector;

return [
    MiddlewareFactoryInterface::class => MiddlewareFactory::class,
    ClientInterface::class => [
        'class' => ClientPsr::class,
        '__construct()' => [
            'token' => $params['botasis/runtime']['bot token'],
        ],
    ],
    IgnoredErrorHandler::class => [
        '__construct()' => [
            'ignoredErrors' => $params['botasis/runtime']['errors to ignore'],
        ],
    ],
    RequestTagsHandler::class => [
        '__construct()' => [
            'tagsSuccess' => $params['botasis/runtime']['request tags']['success'],
            'tagsError' => $params['botasis/runtime']['request tags']['error'],
        ],
    ],
    Application::class => [
        '__construct()' => [
            'fallbackHandler' => Reference::to($params['botasis/runtime']['fallback handler']),
            'dispatcher' => DynamicReference::to(static function (Injector $injector): MiddlewareDispatcher {
                return ($injector->make(MiddlewareDispatcher::class))
                    ->withMiddlewares(
                        EnsureCallbackResponseMiddleware::class,
                        RouterMiddleware::class,
                    );
            }),
        ],
    ],
    Router::class => [
        '__construct()' => ['routes' => $params['botasis/runtime']['routes']],
    ],
    MultipartStreamBuilder::class => [
        '__construct()' => [
            'streamFactory' => Reference::to(StreamFactoryInterface::class),
        ]
    ],
];
