<?php
namespace Bankapi;

use Zend\Router\Http\Segment;


return [
    'router' => [
        'routes' => [
            'bankapi' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/bankapi[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
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
        ],
    ],
    'access_filter' => [
        'controllers' => [
            Controller\IndexController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '*'],
            ],
        ],
    ],    
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
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
