<?php
return [
    'controllers' => [
        'factories' => [
            \ApiReactor\Controller\IndexController::class => \ApiReactor\Controller\Factory\IndexControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            \ApiReactor\V1\Rest\ApiReactorClients\ApiReactorClientsResource::class => \ApiReactor\V1\Rest\ApiReactorClients\ApiReactorClientsResourceFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'api-reactor' => [
                'type' => \Laminas\Router\Http\Segment::class,
                'options' => [
                    'route' => '/api-reactor[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller' => \ApiReactor\Controller\IndexController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'api-reactor.rest.api-reactor-clients' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/api-reactor-clients[/:api_reactor_clients_id]',
                    'defaults' => [
                        'controller' => 'ApiReactor\\V1\\Rest\\ApiReactorClients\\Controller',
                    ],
                ],
            ],
        ],
    ],
    'access_filter' => [
        'controllers' => [
            \ApiReactor\Controller\IndexController::class => [
                0 => [
                    'actions' => '*',
                    'allow' => '*',
                ],
            ],
        ],
    ],
    'api-tools-versioning' => [
        'uri' => [
            1 => 'api-reactor.rest.api-reactor-clients',
        ],
    ],
    'api-tools-rest' => [
        'ApiReactor\\V1\\Rest\\ApiReactorClients\\Controller' => [
            'listener' => \ApiReactor\V1\Rest\ApiReactorClients\ApiReactorClientsResource::class,
            'route_name' => 'api-reactor.rest.api-reactor-clients',
            'route_identifier_name' => 'api_reactor_clients_id',
            'collection_name' => 'api_reactor_clients',
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
            'collection_query_whitelist' => [
                0 => 'out',
            ],
            'page_size' => 25,
            'page_size_param' => 'limit',
            'entity_class' => \ApiReactor\V1\Rest\ApiReactorClients\ApiReactorClientsEntity::class,
            'collection_class' => \ApiReactor\V1\Rest\ApiReactorClients\ApiReactorClientsCollection::class,
            'service_name' => 'ApiReactorClients',
        ],
    ],
    'api-tools-content-negotiation' => [
        'controllers' => [
            'ApiReactor\\V1\\Rest\\ApiReactorClients\\Controller' => 'HalJson',
        ],
        'accept_whitelist' => [
            'ApiReactor\\V1\\Rest\\ApiReactorClients\\Controller' => [
                0 => 'application/vnd.api-reactor.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ],
        ],
        'content_type_whitelist' => [
            'ApiReactor\\V1\\Rest\\ApiReactorClients\\Controller' => [
                0 => 'application/vnd.api-reactor.v1+json',
                1 => 'application/json',
            ],
        ],
    ],
    'api-tools-hal' => [
        'metadata_map' => [
            \ApiReactor\V1\Rest\ApiReactorClients\ApiReactorClientsEntity::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'api-reactor.rest.api-reactor-clients',
                'route_identifier_name' => 'api_reactor_clients_id',
                'hydrator' => \Laminas\Hydrator\ObjectPropertyHydrator::class,
            ],
            \ApiReactor\V1\Rest\ApiReactorClients\ApiReactorClientsCollection::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'api-reactor.rest.api-reactor-clients',
                'route_identifier_name' => 'api_reactor_clients_id',
                'is_collection' => true,
            ],
        ],
    ],
    'api-tools-content-validation' => [
        'ApiReactor\\V1\\Rest\\ApiReactorClients\\Controller' => [
            'input_filter' => 'ApiReactor\\V1\\Rest\\ApiReactorClients\\Validator',
        ],
    ],
    'input_filter_specs' => [
        'ApiReactor\\V1\\Rest\\Clients\\Validator' => [
            0 => [
                'required' => true,
                'validators' => [],
                'filters' => [
                    0 => [
                        'name' => \Laminas\Filter\StringTrim::class,
                        'options' => [],
                    ],
                ],
                'name' => 'out',
            ],
        ],
        'ApiReactor\\V1\\Rest\\ApiReactorPaychecks\\Validator' => [
            0 => [
                'required' => true,
                'validators' => [],
                'filters' => [
                    0 => [
                        'name' => \Laminas\Filter\StringTrim::class,
                        'options' => [],
                    ],
                ],
                'name' => 'out',
                'field_type' => 'string',
            ],
        ],
        'ApiReactor\\V1\\Rest\\ApiReactorClients\\Validator' => [
            0 => [
                'required' => true,
                'validators' => [],
                'filters' => [
                    0 => [
                        'name' => \Laminas\Filter\StringTrim::class,
                        'options' => [],
                    ],
                ],
                'name' => 'out',
                'field_type' => 'string',
            ],
        ],
    ],
    'api-tools-mvc-auth' => [
        'authorization' => [
            'ApiReactor\\V1\\Rest\\ApiReactorClients\\Controller' => [
                'collection' => [
                    'GET' => true,
                    'POST' => false,
                    'PUT' => false,
                    'PATCH' => false,
                    'DELETE' => false,
                ],
                'entity' => [
                    'GET' => false,
                    'POST' => false,
                    'PUT' => false,
                    'PATCH' => false,
                    'DELETE' => false,
                ],
            ],
        ],
    ],
];
