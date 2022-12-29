<?php

namespace ApiSerwer;

use Laminas\Mvc\Controller\LazyControllerAbstractFactory;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use SoapServer\Controller\IndexController;
use SoapServer\Service\Movies;

return [
    'router' => [
        'routes' => [
            'soap-server' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/soap-server',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'default' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '[/:action]',
                            'defaults' => [
                                'controller' => IndexController::class,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            IndexController::class => LazyControllerAbstractFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Movies::class => ReflectionBasedAbstractFactory::class,
        ],
    ],
];