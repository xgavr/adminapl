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
            'post' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/post[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\PostController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
            'proc' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/proc[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\ProcessingController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
            'settings' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/settings',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\IndexController::class,
                        'action'        => 'settings',
                    ],
                ],
            ],
            'telegramm' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/telegramm[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\TelegrammController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
        ],
    ],
    'access_filter' => [
        'controllers' => [
            Controller\AplController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '+admin.manage']
            ],
            Controller\IndexController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '+admin.manage'],
                ['actions' => 'telegramm-hook', 'allow' => '*']
            ],
            Controller\PostController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '+admin.manage']
            ],
            Controller\ProcessingController::class => [
                // Allow access to all users.
                ['actions' => '*', 'allow' => '*']
            ],
            Controller\TelegrammController::class => [
                // Allow access to authenticated users.
                ['actions' => ['index', 'set', 'unset'], 'allow' => '+admin.manage'],
                ['actions' => ['hook'], 'allow' => '*']
            ],
        ],
    ],    
    'controllers' => [
        'factories' => [
            Controller\AplController::class => Controller\Factory\AplControllerFactory::class,
            Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
            Controller\PostController::class => Controller\Factory\PostControllerFactory::class,
            Controller\ProcessingController::class => Controller\Factory\ProcessingControllerFactory::class,
            Controller\TelegrammController::class => Controller\Factory\TelegrammControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Service\AdminManager::class => Service\Factory\AdminManagerFactory::class,
            Service\AnnManager::class => Service\Factory\AnnManagerFactory::class,
            Service\AplService::class => Service\Factory\AplServiceFactory::class,
            Service\AutoruManager::class => Service\Factory\AutoruManagerFactory::class,
            Service\FtpManager::class => Service\Factory\FtpManagerFactory::class,
            Service\PostManager::class => Service\Factory\PostManagerFactory::class,
            Service\SmsManager::class => Service\Factory\SmsManagerFactory::class,
            Service\TamTamManager::class => Service\Factory\TamTamManagerFactory::class,
            Service\TelegrammManager::class => Service\Factory\TelegrammManagerFactory::class,
        ],
    ],    
    'view_manager' => [
        'template_path_stack' => [
            'Admin' => __DIR__ . '/../view',
        ],
    ],
];
