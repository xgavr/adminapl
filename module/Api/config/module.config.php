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
            'api.rest.api-account-comitent' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/api-account-comitent[/:api_account_comitent_id]',
                    'defaults' => [
                        'controller' => 'Api\\V1\\Rest\\ApiAccountComitent\\Controller',
                    ],
                ],
            ],
            'api.rest.api-comment-to-apl' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/api-comment-to-apl[/:api_comment_to_apl_id]',
                    'defaults' => [
                        'controller' => 'Api\\V1\\Rest\\ApiCommentToApl\\Controller',
                    ],
                ],
            ],
            'api.rest.api-suppliers-prices' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/api-suppliers-prices[/:api_suppliers_prices_id]',
                    'defaults' => [
                        'controller' => 'Api\\V1\\Rest\\ApiSuppliersPrices\\Controller',
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
            \Api\V1\Rest\ApiAccountComitent\ApiAccountComitentResource::class => \Api\V1\Rest\ApiAccountComitent\ApiAccountComitentResourceFactory::class,
            \Api\V1\Rest\ApiCommentToApl\ApiCommentToAplResource::class => \Api\V1\Rest\ApiCommentToApl\ApiCommentToAplResourceFactory::class,
            \Api\V1\Rest\ApiSuppliersPrices\ApiSuppliersPricesResource::class => \Api\V1\Rest\ApiSuppliersPrices\ApiSuppliersPricesResourceFactory::class,
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
            3 => 'api.rest.api-account-comitent',
            4 => 'api.rest.api-comment-to-apl',
            5 => 'api.rest.api-suppliers-prices',
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
            'Api\\V1\\Rest\\ApiAccountComitent\\Controller' => 'HalJson',
            'Api\\V1\\Rest\\ApiCommentToApl\\Controller' => 'HalJson',
            'Api\\V1\\Rest\\ApiSuppliersPrices\\Controller' => 'HalJson',
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
            'Api\\V1\\Rest\\ApiAccountComitent\\Controller' => [
                0 => 'application/vnd.api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ],
            'Api\\V1\\Rest\\ApiCommentToApl\\Controller' => [
                0 => 'application/vnd.api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ],
            'Api\\V1\\Rest\\ApiSuppliersPrices\\Controller' => [
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
            'Api\\V1\\Rest\\ApiAccountComitent\\Controller' => [
                0 => 'application/vnd.api.v1+json',
                1 => 'application/json',
            ],
            'Api\\V1\\Rest\\ApiCommentToApl\\Controller' => [
                0 => 'application/vnd.api.v1+json',
                1 => 'application/json',
            ],
            'Api\\V1\\Rest\\ApiSuppliersPrices\\Controller' => [
                0 => 'application/vnd.api.v1+json',
                1 => 'application/json',
            ],
        ],
    ],
    'api-tools-content-validation' => [
        'Api\\V1\\Rpc\\Ping\\Controller' => [
            'input_filter' => 'Api\\V1\\Rpc\\Ping\\Validator',
        ],
        'Api\\V1\\Rest\\ApiAccountComitent\\Controller' => [
            'input_filter' => 'Api\\V1\\Rest\\ApiAccountComitent\\Validator',
        ],
        'Api\\V1\\Rest\\ApiCommentToApl\\Controller' => [
            'input_filter' => 'Api\\V1\\Rest\\ApiCommentToApl\\Validator',
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
        'Api\\V1\\Rest\\ApiAccountComitent\\Validator' => [
            0 => [
                'required' => true,
                'validators' => [
                    0 => [
                        'name' => \Laminas\Validator\InArray::class,
                        'options' => [
                            'haystack' => [
                                0 => 1,
                                1 => 2,
                                2 => 3,
                            ],
                        ],
                    ],
                ],
                'filters' => [
                    0 => [
                        'name' => \Laminas\Filter\ToInt::class,
                        'options' => [],
                    ],
                ],
                'name' => 'statusAccount',
                'description' => 'Статус проведения документа в бухгалтерии',
                'error_message' => 'Не верный статус',
            ],
        ],
        'Api\\V1\\Rest\\ApiCommentToApl\\Validator' => [
            0 => [
                'required' => true,
                'validators' => [
                    0 => [
                        'name' => \Laminas\I18n\Validator\IsInt::class,
                        'options' => [],
                    ],
                ],
                'filters' => [
                    0 => [
                        'name' => \Laminas\Filter\ToInt::class,
                        'options' => [],
                    ],
                ],
                'name' => 'order',
                'description' => 'Номер заказа в Апл',
                'error_message' => 'Не верный номер заказа',
                'field_type' => 'integer',
            ],
            1 => [
                'required' => true,
                'validators' => [
                    0 => [
                        'name' => \Laminas\Validator\StringLength::class,
                        'options' => [
                            'max' => '256',
                        ],
                    ],
                ],
                'filters' => [
                    0 => [
                        'name' => \Laminas\Filter\StringTrim::class,
                        'options' => [],
                    ],
                ],
                'name' => 'message',
                'description' => 'Сообщение',
                'error_message' => 'Длина не больше 256 символов',
                'field_type' => 'string',
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
        'Api\\V1\\Rest\\ApiAccountComitent\\Controller' => [
            'listener' => \Api\V1\Rest\ApiAccountComitent\ApiAccountComitentResource::class,
            'route_name' => 'api.rest.api-account-comitent',
            'route_identifier_name' => 'api_account_comitent_id',
            'collection_name' => 'api_account_comitent',
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
            'entity_class' => \Api\V1\Rest\ApiAccountComitent\ApiAccountComitentEntity::class,
            'collection_class' => \Api\V1\Rest\ApiAccountComitent\ApiAccountComitentCollection::class,
            'service_name' => 'ApiAccountComitent',
        ],
        'Api\\V1\\Rest\\ApiCommentToApl\\Controller' => [
            'listener' => \Api\V1\Rest\ApiCommentToApl\ApiCommentToAplResource::class,
            'route_name' => 'api.rest.api-comment-to-apl',
            'route_identifier_name' => 'api_comment_to_apl_id',
            'collection_name' => 'api_comment_to_apl',
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
            'entity_class' => \Api\V1\Rest\ApiCommentToApl\ApiCommentToAplEntity::class,
            'collection_class' => \Api\V1\Rest\ApiCommentToApl\ApiCommentToAplCollection::class,
            'service_name' => 'ApiCommentToApl',
        ],
        'Api\\V1\\Rest\\ApiSuppliersPrices\\Controller' => [
            'listener' => \Api\V1\Rest\ApiSuppliersPrices\ApiSuppliersPricesResource::class,
            'route_name' => 'api.rest.api-suppliers-prices',
            'route_identifier_name' => 'api_suppliers_prices_id',
            'collection_name' => 'api_suppliers_prices',
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
            'entity_class' => \Api\V1\Rest\ApiSuppliersPrices\ApiSuppliersPricesEntity::class,
            'collection_class' => \Api\V1\Rest\ApiSuppliersPrices\ApiSuppliersPricesCollection::class,
            'service_name' => 'ApiSuppliersPrices',
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
            \Api\V1\Rest\ApiAccountComitent\ApiAccountComitentEntity::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.api-account-comitent',
                'route_identifier_name' => 'api_account_comitent_id',
                'hydrator' => \Laminas\Hydrator\ObjectProperty::class,
            ],
            \Api\V1\Rest\ApiAccountComitent\ApiAccountComitentCollection::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.api-account-comitent',
                'route_identifier_name' => 'api_account_comitent_id',
                'is_collection' => true,
            ],
            \Api\V1\Rest\ApiCommentToApl\ApiCommentToAplEntity::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.api-comment-to-apl',
                'route_identifier_name' => 'api_comment_to_apl_id',
                'hydrator' => \Laminas\Hydrator\ArraySerializable::class,
            ],
            \Api\V1\Rest\ApiCommentToApl\ApiCommentToAplCollection::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.api-comment-to-apl',
                'route_identifier_name' => 'api_comment_to_apl_id',
                'is_collection' => true,
            ],
            \Api\V1\Rest\ApiSuppliersPrices\ApiSuppliersPricesEntity::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.api-suppliers-prices',
                'route_identifier_name' => 'api_suppliers_prices_id',
                'hydrator' => \Laminas\Hydrator\ObjectProperty::class,
            ],
            \Api\V1\Rest\ApiSuppliersPrices\ApiSuppliersPricesCollection::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.api-suppliers-prices',
                'route_identifier_name' => 'api_suppliers_prices_id',
                'is_collection' => true,
            ],
        ],
    ],
];
