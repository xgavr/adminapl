<?php
namespace User;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

return [
    'router' => [
        'routes' => [
            'login' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/login',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'login',
                    ],
                ],
            ],
            'logout' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/logout',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'logout',
                    ],
                ],
            ],
            'not-authorized' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/not-authorized',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'notAuthorized',
                    ],
                ],
            ],
            'reset-password' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/reset-password',
                    'defaults' => [
                        'controller' => Controller\UserController::class,
                        'action'     => 'resetPassword',
                    ],
                ],
            ],
            'members' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/members[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\MemberController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
            'users' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/users[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\UserController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
            'roles' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/roles[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\RoleController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
            'permissions' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/permissions[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\PermissionController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\AuthController::class => Controller\Factory\AuthControllerFactory::class,
            Controller\MemberController::class => Controller\Factory\MemberControllerFactory::class, 
            Controller\PermissionController::class => Controller\Factory\PermissionControllerFactory::class,
            Controller\RoleController::class => Controller\Factory\RoleControllerFactory::class,    
            Controller\UserController::class => Controller\Factory\UserControllerFactory::class, 
        ],
    ],
    // We register module-provided controller plugins under this key.
    'controller_plugins' => [
        'factories' => [
            Controller\Plugin\AccessPlugin::class => Controller\Plugin\Factory\AccessPluginFactory::class,
            Controller\Plugin\CurrentUserPlugin::class => Controller\Plugin\Factory\CurrentUserPluginFactory::class,
        ],
        'aliases' => [
            'access' => Controller\Plugin\AccessPlugin::class,
            'currentUser' => Controller\Plugin\CurrentUserPlugin::class,
        ],
    ],
    // The 'access_filter' key is used by the User module to restrict or permit
    // access to certain controller actions for unauthorized visitors.
    'access_filter' => [
        'options' => [
            // Фильтр доступа может работать в 'ограничительном' (рекомендуется) или 'разрешающем'
            // режиме. В ограничительном режиме все действия контроллера должны быть явно перечислены 
            // под ключом конфигурации 'access_filter', а доступ к любому не перечисленному действию
            // для неавторизованных пользователей запрещен. В разрешающем режиме, даже если действие не
            // указано под ключом 'access_filter', доступ к нему разрешен для всех (даже для  
            // неавторизованных пользователей. Рекомендуется использовать более безопасный ограничительный режим.
            'mode' => 'restrictive'
        ],
        'controllers' => [
            \Application\Controller\ClientController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            \Application\Controller\ContactController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            \Application\Controller\CurrencyController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            \Application\Controller\GoodsController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '+goods.manage']
            ],
            \Admin\Controller\IndexController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            \Application\Controller\IndexController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            \Application\Controller\OrderController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            \Application\Controller\PriceController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            \Application\Controller\PricesettingsController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            \Application\Controller\ProducerController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            \Application\Controller\RawController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '+raw.manage']
            ],
            \Application\Controller\RawpriceController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '+raw.manage']
            ],
            \Application\Controller\RbController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            \Application\Controller\SupplierController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '+supplier.manage']
            ],
            \Application\Controller\ShopController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\MemberController::class => [
                // Give access to "resetPassword", "message" and "setPassword" actions
                // to anyone.
                ['actions' => ['resetPassword', 'message', 'setPassword'], 'allow' => '*'],
                // Give access to "index", "add", "edit", "view", "changePassword" actions to users having the "user.manage" permission.
                ['actions' => ['edit', 'changePassword'], 'allow' => '@'],
                ['actions' => ['index', 'add', 'view'], 'allow' => '+member.manage'],
                ['actions' => ['clientManagerTransfer'], 'allow' => '+member.transfer.manage'],
            ],
            Controller\RoleController::class => [
                // Allow access to authenticated users having the "role.manage" permission.
                ['actions' => '*', 'allow' => '+role.manage']
            ],
            Controller\PermissionController::class => [
                // Allow access to authenticated users having "permission.manage" permission.
                ['actions' => '*', 'allow' => '+permission.manage']
            ],
            Controller\UserController::class => [
                // Give access to "resetPassword", "message" and "setPassword" actions
                // to anyone.
                ['actions' => ['resetPassword', 'message', 'setPassword'], 'allow' => '*'],
                // Give access to "index", "add", "edit", "view", "changePassword" actions to users having the "user.manage" permission.
                ['actions' => ['index', 'add', 'edit', 'view', 'changePassword'], 'allow' => '+user.manage'],
zz            ],
        ]
    ],
    'service_manager' => [
        'factories' => [
            \Zend\Authentication\AuthenticationService::class => Service\Factory\AuthenticationServiceFactory::class,
            Service\AuthAdapter::class => Service\Factory\AuthAdapterFactory::class,
            Service\AuthManager::class => Service\Factory\AuthManagerFactory::class,
            Service\PermissionManager::class => Service\Factory\PermissionManagerFactory::class,
            Service\RbacManager::class => Service\Factory\RbacManagerFactory::class,
            Service\RoleManager::class => Service\Factory\RoleManagerFactory::class,
            Service\UserManager::class => Service\Factory\UserManagerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    // We register module-provided view helpers under this key.
    'view_helpers' => [
        'factories' => [
            View\Helper\Access::class => View\Helper\Factory\AccessFactory::class,
            View\Helper\CurrentUser::class => View\Helper\Factory\CurrentUserFactory::class,
        ],
        'aliases' => [
            'access' => View\Helper\Access::class,
            'currentUser' => View\Helper\CurrentUser::class,
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
