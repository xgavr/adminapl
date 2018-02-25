<?php
namespace Admin;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'admin' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/admin[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\IndexController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
            'apl' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/apl[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\AplController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
        ],
    ],
    'access_filter' => [
        'controllers' => [
            \Admin\Controller\AplController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '+company.manage']
            ],
            \Admin\Controller\IndexController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '+company.manage']
            ],
        ],
    ],    
    'controllers' => [
        'factories' => [
            Controller\AplController::class => Controller\Factory\AplControllerFactory::class,
            Controller\IndexController::class => InvokableFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Service\AplService::class => Service\Factory\AplServiceFactory::class,
        ],
    ],    
    'view_manager' => [
        'template_path_stack' => [
            'Admin' => __DIR__ . '/../view',
        ],
    ],
];
