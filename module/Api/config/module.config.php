<?php
return [
    'router' => [
        'routes' => [
            'api' => [
                'type' => \Laminas\Router\Http\Segment::class,
                'options' => [
                    'route' => '/api[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller' => \Api\Controller\IndexController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'api.rpc.ping' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/ping',
                    'defaults' => [
                        'controller' => \Api\V1\Rpc\Ping\PingController::class,
                        'action' => 'ping',
                    ],
                ],
            ],
            'api.rest.good' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/good[/:good_id]',
                    'defaults' => [
                        'controller' => 'Api\\V1\\Rest\\Good\\Controller',
                    ],
                ],
            ],
            'api.rest.good-apl' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/good-apl[/:good_apl_id]',
                    'defaults' => [
                        'controller' => 'Api\\V1\\Rest\\GoodApl\\Controller',
                    ],
                ],
            ],
        ],
    ],
    'access_filter' => [
        'controllers' => [
            \Api\Controller\IndexController::class => [
                0 => [
                    'actions' => '*',
                    'allow' => '*',
                ],
            ],
            \Api\V1\Rpc\Ping\PingController::class => [
                0 => [
                    'actions' => '*',
                    'allow' => '*',
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            \Api\Controller\IndexController::class => \Api\Controller\Factory\IndexControllerFactory::class,
            \Api\V1\Rpc\Ping\PingController::class => \Api\V1\Rpc\Ping\PingControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            \Api\V1\Rest\Good\GoodResource::class => \Api\V1\Rest\Good\GoodResourceFactory::class,
            \Api\V1\Rest\GoodApl\GoodAplResource::class => \Api\V1\Rest\GoodApl\GoodAplResourceFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'Api' => 'C:\\OpenServer\\domains\\adminapl\\module\\Api\\config/../view',
        ],
    ],
    'doctrine' => [
        'driver' => [
            'Api_driver' => [
                'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [
                    0 => 'C:\\OpenServer\\domains\\adminapl\\module\\Api\\config/../src/Entity',
                ],
            ],
            'orm_default' => [
                'drivers' => [
                    'Api\\Entity' => 'Api_driver',
                ],
            ],
        ],
    ],
    'api-tools-versioning' => [
        'uri' => [
            0 => 'api.rpc.ping',
            1 => 'api.rest.good',
            2 => 'api.rest.good-apl',
        ],
    ],
    'api-tools-rpc' => [
        'Api\\V1\\Rpc\\Ping\\Controller' => [
            'service_name' => 'Ping',
            'http_methods' => [
                0 => 'GET',
            ],
            'route_name' => 'api.rpc.ping',
        ],
    ],
    'api-tools-content-negotiation' => [
        'controllers' => [
            'Api\\V1\\Rpc\\Ping\\Controller' => 'Json',
            'Api\\V1\\Rest\\Good\\Controller' => 'HalJson',
            'Api\\V1\\Rest\\GoodApl\\Controller' => 'HalJson',
        ],
        'accept_whitelist' => [
            'Api\\V1\\Rpc\\Ping\\Controller' => [
                0 => 'application/vnd.api.v1+json',
                1 => 'application/json',
                2 => 'application/*+json',
            ],
            'Api\\V1\\Rest\\Good\\Controller' => [
                0 => 'application/vnd.api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ],
            'Api\\V1\\Rest\\GoodApl\\Controller' => [
                0 => 'application/vnd.api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ],
        ],
        'content_type_whitelist' => [
            'Api\\V1\\Rpc\\Ping\\Controller' => [
                0 => 'application/vnd.api.v1+json',
                1 => 'application/json',
            ],
            'Api\\V1\\Rest\\Good\\Controller' => [
                0 => 'application/vnd.api.v1+json',
                1 => 'application/json',
            ],
            'Api\\V1\\Rest\\GoodApl\\Controller' => [
                0 => 'application/vnd.api.v1+json',
                1 => 'application/json',
            ],
        ],
    ],
    'api-tools-content-validation' => [
        'Api\\V1\\Rpc\\Ping\\Controller' => [
            'input_filter' => 'Api\\V1\\Rpc\\Ping\\Validator',
        ],
    ],
    'input_filter_specs' => [
        'Api\\V1\\Rpc\\Ping\\Validator' => [
            0 => [
                'required' => true,
                'validators' => [],
                'filters' => [],
                'name' => 'ask',
                'description' => 'Подтвердить запрос с отметкой времени',
            ],
        ],
    ],
    'api-tools-rest' => [
        'Api\\V1\\Rest\\Good\\Controller' => [
            'listener' => \Api\V1\Rest\Good\GoodResource::class,
            'route_name' => 'api.rest.good',
            'route_identifier_name' => 'good_id',
            'collection_name' => 'good',
            'entity_http_methods' => [
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ],
            'collection_http_methods' => [
                0 => 'GET',
                1 => 'POST',
            ],
            'collection_query_whitelist' => [],
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => \Api\V1\Rest\Good\GoodEntity::class,
            'collection_class' => \Api\V1\Rest\Good\GoodCollection::class,
            'service_name' => 'Good',
        ],
        'Api\\V1\\Rest\\GoodApl\\Controller' => [
            'listener' => \Api\V1\Rest\GoodApl\GoodAplResource::class,
            'route_name' => 'api.rest.good-apl',
            'route_identifier_name' => 'good_apl_id',
            'collection_name' => 'good_apl',
            'entity_http_methods' => [
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ],
            'collection_http_methods' => [
                0 => 'GET',
                1 => 'POST',
            ],
            'collection_query_whitelist' => [],
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => \Api\V1\Rest\GoodApl\GoodAplEntity::class,
            'collection_class' => \Api\V1\Rest\GoodApl\GoodAplCollection::class,
            'service_name' => 'goodApl',
        ],
    ],
    'api-tools-hal' => [
        'metadata_map' => [
            \Api\V1\Rest\Good\GoodEntity::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.good',
                'route_identifier_name' => 'good_id',
                'hydrator' => \Laminas\Hydrator\ArraySerializable::class,
            ],
            \Api\V1\Rest\Good\GoodCollection::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.good',
                'route_identifier_name' => 'good_id',
                'is_collection' => true,
            ],
            \Api\V1\Rest\GoodApl\GoodAplEntity::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.good-apl',
                'route_identifier_name' => 'good_apl_id',
                'hydrator' => \Laminas\Hydrator\ArraySerializable::class,
            ],
            \Api\V1\Rest\GoodApl\GoodAplCollection::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.good-apl',
                'route_identifier_name' => 'good_apl_id',
                'is_collection' => true,
            ],
        ],
    ],
];