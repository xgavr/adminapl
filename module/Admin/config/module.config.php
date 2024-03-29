<?php
namespace Admin;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

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
            'log' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/log[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\LogController::class,
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
            'sms' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/sms[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\SmsController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
            'soap' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/soap[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\SoapController::class,
                        'action'        => 'index',
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
                ['actions' => '*', 'allow' => '+apl.exchange.manage'],
                ['actions' => 'asqrrn', 'allow' => '*']
            ],
            Controller\IndexController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '+admin.manage'],
                ['actions' => 'telegrammHook', 'allow' => '*'],
                ['actions' => ['smsForm', 'smsPartial', 'orderPrepay', 'sms'], 'allow' => '@']
            ],
            Controller\LogController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@'],
            ],
            Controller\PostController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\ProcessingController::class => [
                // Allow access to all users.
                ['actions' => '*', 'allow' => '*']
            ],
            Controller\SmsController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\SoapController::class => [
                // Allow access to all users.
                ['actions' => '*', 'allow' => '*']
            ],
            Controller\TelegrammController::class => [
                // Allow access to authenticated users.
                ['actions' => ['index', 'set', 'unset', 'checkProxy'], 'allow' => '+admin.manage'],
                ['actions' => ['hook', 'postpone'], 'allow' => '*']
            ],
        ],
    ],    
    'controllers' => [
        'factories' => [
            Controller\AplController::class => Controller\Factory\AplControllerFactory::class,
            Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
            Controller\LogController::class => Controller\Factory\LogControllerFactory::class,
            Controller\PostController::class => Controller\Factory\PostControllerFactory::class,
            Controller\SmsController::class => Controller\Factory\SmsControllerFactory::class,
            Controller\ProcessingController::class => Controller\Factory\ProcessingControllerFactory::class,
            Controller\SoapController::class => Controller\Factory\SoapControllerFactory::class,
            Controller\TelegrammController::class => Controller\Factory\TelegrammControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Service\AdminManager::class => Service\Factory\AdminManagerFactory::class,
            Service\AnnManager::class => Service\Factory\AnnManagerFactory::class,
            Service\AplBankService::class => Service\Factory\AplBankServiceFactory::class,
            Service\AplDocService::class => Service\Factory\AplDocServiceFactory::class,
            Service\AplCashService::class => Service\Factory\AplCashServiceFactory::class,
            Service\AplOrderService::class => Service\Factory\AplOrderServiceFactory::class,
            Service\AplService::class => Service\Factory\AplServiceFactory::class,
            Service\AutoruManager::class => Service\Factory\AutoruManagerFactory::class,
            Service\FtpManager::class => Service\Factory\FtpManagerFactory::class,
            Service\HelloManager::class => Service\Factory\HelloManagerFactory::class,
            Service\JobManager::class => Service\Factory\JobManagerFactory::class,
            Service\LogManager::class => Service\Factory\LogManagerFactory::class,
            Service\PostManager::class => Service\Factory\PostManagerFactory::class,
            Service\SettingManager::class => Service\Factory\SettingManagerFactory::class,
            Service\SmsManager::class => Service\Factory\SmsManagerFactory::class,
            Service\SoapManager::class => Service\Factory\SoapManagerFactory::class,
            Service\TamTamManager::class => Service\Factory\TamTamManagerFactory::class,
            Service\TelefonistkaManager::class => Service\Factory\TelefonistkaManagerFactory::class,
            Service\TelegrammManager::class => Service\Factory\TelegrammManagerFactory::class,
        ],
    ],    
    'view_manager' => [
        'template_path_stack' => [
            'Admin' => __DIR__ . '/../view',
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
