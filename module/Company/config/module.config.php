<?php
namespace Company;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

return [
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => InvokableFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'company' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/company[/:action[/:id]]',
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
            'regions' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/regions[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\RegionController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
            'offices' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/offices[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\OfficeController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
        ],
    ],
    'access_filter' => [
        'controllers' => [
            \Company\Controller\IndexController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '+company.manage']
            ],
            \Company\Controller\RegionController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '+company.manage']
            ],
            \Company\Controller\OfficeController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '+company.manage']
            ],
        ],
    ],    
    'controllers' => [
        'factories' => [
            Controller\RegionController::class => Controller\Factory\RegionControllerFactory::class,
            Controller\OfficeController::class => Controller\Factory\OfficeControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Service\RegionManager::class => Service\Factory\RegionManagerFactory::class,
            Service\OfficeManager::class => Service\Factory\OfficeManagerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'doctrine' => [
        'driver' => [
            __NAMESPACE__ . '_driver' => [
                'class' => AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [__DIR__ . '/../src/Entity']
            ],
            'orm_default' => [
                'drivers' => [
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                ]
            ]
        ]
    ],
    
];
