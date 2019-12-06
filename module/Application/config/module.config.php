<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

return [
    'router' => [
        'routes' => [
            'home' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'application' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/application[/:action]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'car' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/car[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ],
                    'defaults' => [
                        'controller'    => Controller\CarController::class,
                        'action'        => 'index',
                    ],
                ],
            ],        
            'cross' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/cross[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ],
                    'defaults' => [
                        'controller'    => Controller\CrossController::class,
                        'action'        => 'index',
                    ],
                ],
            ],        
            'goods' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/goods[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ],
                    'defaults' => [
                        'controller'    => Controller\GoodsController::class,
                        'action'        => 'index',
                    ],
                ],
            ],        
            'image' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/image[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ],
                    'defaults' => [
                        'controller'    => Controller\ImageController::class,
                        'action'        => 'index',
                    ],
                ],
            ],        
            'group' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/group[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ],
                    'defaults' => [
                        'controller'    => Controller\GroupController::class,
                        'action'        => 'index',
                    ],
                ],
            ],        
            'order' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/order[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ],
                    'defaults' => [
                        'controller'    => Controller\OrderController::class,
                        'action'        => 'index',
                    ],
                ],
            ],        
            'client' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/client[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ],
                    'defaults' => [
                        'controller'    => Controller\ClientController::class,
                        'action'        => 'index',
                    ],
                ],
            ],        
            'currency' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/currency[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ],
                    'defaults' => [
                        'controller'    => Controller\CurrencyController::class,
                        'action'        => 'index',
                    ],
                ],
            ],        
            'supplier' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/supplier[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ],
                    'defaults' => [
                        'controller'    => Controller\SupplierController::class,
                        'action'        => 'index',
                    ],
                ],
            ],        
            'shop' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/shop[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ],
                    'defaults' => [
                        'controller'    => Controller\ShopController::class,
                        'action'        => 'index',
                    ],
                ],
            ],        
            'make' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/make[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ],
                    'defaults' => [
                        'controller'    => Controller\MakeController::class,
                        'action'        => 'index',
                    ],
                ],
            ],        
            'ml' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/ml[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ],
                    'defaults' => [
                        'controller'    => Controller\MlController::class,
                        'action'        => 'index',
                    ],
                ],
            ],        
            'ext' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/ml[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ],
                    'defaults' => [
                        'controller'    => Controller\ExternalController::class,
                        'action'        => 'index',
                    ],
                ],
            ],        
            'name' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/name[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ],
                    'defaults' => [
                        'controller'    => Controller\NameController::class,
                        'action'        => 'index',
                    ],
                ],
            ],        
            'oem' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/oem[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ],
                    'defaults' => [
                        'controller'    => Controller\OemController::class,
                        'action'        => 'index',
                    ],
                ],
            ],        
            'price' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/price[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ],
                    'defaults' => [
                        'controller'    => Controller\PriceController::class,
                        'action'        => 'index',
                    ],
                ],
            ],        
            'pricesettings' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/pricesettings[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ],
                    'defaults' => [
                        'controller'    => Controller\PricesettingsController::class,
                        'action'        => 'index',
                    ],
                ],
            ],        
            'producer' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/producer[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ],
                    'defaults' => [
                        'controller'    => Controller\ProducerController::class,
                        'action'        => 'index',
                    ],
                ],
            ],        
            'raw' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/raw[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ],
                    'defaults' => [
                        'controller'    => Controller\RawController::class,
                        'action'        => 'index',
                    ],
                ],
            ],        
            'rawprice' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/rawprice[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ],
                    'defaults' => [
                        'controller'    => Controller\RawpriceController::class,
                        'action'        => 'index',
                    ],
                ],
            ],        
            'contact' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/contact[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ],
                    'defaults' => [
                        'controller'    => Controller\ContactController::class,
                        'action'        => 'index',
                    ],
                ],
            ],        
            'ext' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/ext[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ],
                    'defaults' => [
                        'controller'    => Controller\ExternalController::class,
                        'action'        => 'index',
                    ],
                ],
            ],    
            'rb' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/rb[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ],
                    'defaults' => [
                        'controller'    => Controller\RbController::class,
                        'action'        => 'index',
                    ],
                ],
            ],    
            'about' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/about',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'about',
                    ],
                ],
            ], 
        ],
    ],
    'rbac_manager' => [
        'assertions' => [Service\RbacAssertionManager::class],
    ],      
    'controllers' => [
        'factories' => [
            Controller\CarController::class => Controller\Factory\CarControllerFactory::class,
            Controller\ClientController::class => Controller\Factory\ClientControllerFactory::class,
            Controller\ContactController::class => Controller\Factory\ContactControllerFactory::class,
            Controller\CrossController::class => Controller\Factory\CrossControllerFactory::class,
            Controller\CurrencyController::class => Controller\Factory\CurrencyControllerFactory::class,
            Controller\ExternalController::class => Controller\Factory\ExternalControllerFactory::class,
            Controller\GoodsController::class => Controller\Factory\GoodsControllerFactory::class,
            Controller\GroupController::class => Controller\Factory\GroupControllerFactory::class,
            Controller\ImageController::class => Controller\Factory\ImageControllerFactory::class,
            Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
            Controller\MakeController::class => Controller\Factory\MakeControllerFactory::class,
            Controller\MlController::class => Controller\Factory\MlControllerFactory::class,
            Controller\NameController::class => Controller\Factory\NameControllerFactory::class,
            Controller\OemController::class => Controller\Factory\OemControllerFactory::class,
            Controller\OrderController::class => Controller\Factory\OrderControllerFactory::class,
            Controller\PriceController::class => Controller\Factory\PriceControllerFactory::class,
            Controller\PricesettingsController::class => Controller\Factory\PricesettingsControllerFactory::class,
            Controller\ProducerController::class => Controller\Factory\ProducerControllerFactory::class,
            Controller\RawController::class => Controller\Factory\RawControllerFactory::class,
            Controller\RawpriceController::class => Controller\Factory\RawpriceControllerFactory::class,
            Controller\RbController::class => Controller\Factory\RbControllerFactory::class,
            Controller\SupplierController::class => Controller\Factory\SupplierControllerFactory::class,
            Controller\ShopController::class => Controller\Factory\ShopControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Service\ArticleManager::class => Service\Factory\ArticleManagerFactory::class,
            Service\AssemblyManager::class => Service\Factory\AssemblyManagerFactory::class,
            Service\CarManager::class => Service\Factory\CarManagerFactory::class,
            Service\ClientManager::class => Service\Factory\ClientManagerFactory::class,
            Service\ContactManager::class => Service\Factory\ContactManagerFactory::class,
            Service\CrossManager::class => Service\Factory\CrossManagerFactory::class,
            Service\CurrencyManager::class => Service\Factory\CurrencyManagerFactory::class,
            Service\ExternalManager::class => Service\Factory\ExternalManagerFactory::class,
            Service\ExternalDB\AbcpManager::class => Service\Factory\ExternalDB\AbcpManagerFactory::class,
            Service\ExternalDB\AutodbManager::class => Service\Factory\ExternalDB\AutodbManagerFactory::class,
            Service\ExternalDB\PartsApiManager::class => Service\Factory\ExternalDB\PartsApiManagerFactory::class,
            Service\GoodsManager::class => Service\Factory\GoodsManagerFactory::class,
            Service\ImageManager::class => Service\Factory\ImageManagerFactory::class,
            Service\MakeManager::class => Service\Factory\MakeManagerFactory::class,
            Service\MlManager::class => Service\Factory\MlManagerFactory::class,
            Service\NameManager::class => Service\Factory\NameManagerFactory::class,
            Service\NavManager::class => Service\Factory\NavManagerFactory::class,
            Service\OemManager::class => Service\Factory\OemManagerFactory::class,
            Service\OrderManager::class => Service\Factory\OrderManagerFactory::class,
            Service\ParseManager::class => Service\Factory\ParseManagerFactory::class,
            Service\PriceManager::class => Service\Factory\PriceManagerFactory::class,
            Service\ProducerManager::class => Service\Factory\ProducerManagerFactory::class,
            Service\RawManager::class => Service\Factory\RawManagerFactory::class,
            Service\RbacAssertionManager::class => Service\Factory\RbacAssertionManagerFactory::class,  
            Service\RbManager::class => Service\Factory\RbManagerFactory::class,
            Service\ShopManager::class => Service\Factory\ShopManagerFactory::class,
            Service\SupplierManager::class => Service\Factory\SupplierManagerFactory::class,
            'doctrine.cache.doctrine_cache' => Service\Factory\DoctrineCacheFactory::class,
        ],
    ],    
    'session_containers' => [
        'ContainerNamespace'
    ],
    'access_filter' => [
        'controllers' => [
            Controller\CarController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\ClientController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\ContactController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\CrossController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\CurrencyController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\GoodsController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\GroupController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\ExternalController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\ImageController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\IndexController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\MakeController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\MlController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\NameController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\OemController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\OrderController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\PriceController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\PricesettingsController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\ProducerController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\RawController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '+raw.manage']
            ],
            Controller\RawpriceController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '+raw.manage']
            ],
            Controller\RbController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\SupplierController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '+supplier.manage']
            ],
            Controller\ShopController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
        ],
    ],    
    'view_helpers' => [
        'factories' => [
            View\Helper\Menu::class => View\Helper\Factory\MenuFactory::class,
            View\Helper\Breadcrumbs::class => InvokableFactory::class,
        ],
        'aliases' => [
            'mainMenu' => View\Helper\Menu::class,
            'pageBreadcrumbs' => View\Helper\Breadcrumbs::class,
        ],
    ],    
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
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
    'providers' => [
        cijic\phpMorphy\MorphyServiceProvider::class,
    ],
    'aliases' => [
        'Morphy'    => cijic\phpMorphy\Facade\Morphy::class,
    ],    
];
