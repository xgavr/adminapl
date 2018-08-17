<?php
namespace Bankapi;

use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Router\Http\Segment;


return [
    'router' => [
        'routes' => [
            'bankapi' => [
                'type'    => 'Literal',
                'options' => [
                    // Change this to something specific to your module
                    'route'    => '/',
                    'defaults' => [
                        'controller'    => Controller\IndexController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    // You can place additional routes that match under the
                    // route defined above here.
                ],
            ],
            'tochka' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/tochka[/:action]',
                    'defaults' => [
                        'controller' => Controller\TochkaController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
            Controller\TochkaController::class => Controller\Factory\TochkaControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Service\TochkaApi::class => Service\Factory\TochkaApiFactory::class,
        ],
    ],    
    'view_manager' => [
        'template_path_stack' => [
            'bankapi' => __DIR__ . '/../view',
        ],
    ],
];
