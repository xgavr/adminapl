<?php
return [
    'session_containers' => [
        0 => \Laminas\Session\Container::class,
    ],
    'session_config' => [
        'cookie_lifetime' => 0,
        'gc_maxlifetime' => 2592000,
        'savePath' => './data/session1',
    ],
    'session_manager' => [
        'validators' => [
            0 => \Laminas\Session\Validator\RemoteAddr::class,
            1 => \Laminas\Session\Validator\HttpUserAgent::class,
        ],
    ],
    'session_storage' => [
        'type' => \Laminas\Session\Storage\SessionArrayStorage::class,
    ],
    'caches' => [
        'memcache' => [
            'adapter' => [
                'name' => 'Laminas\\Cache\\Storage\\Adapter\\Memcache',
                'options' => [
                    'ttl' => 3600,
                    'servers' => [
                        0 => [
                            0 => '127.0.0.1',
                            1 => 11211,
                        ],
                    ],
                    'namespace' => 'AdmAPL',
                ],
            ],
            'plugins' => [
                'exception_handler' => [
                    'throw_exceptions' => false,
                ],
            ],
            0 => 'serializer',
        ],
        \memcached::class => [
            'adapter' => \memcached::class,
            'options' => [
                'ttl' => 3600,
                'servers' => [
                    0 => [
                        0 => '127.0.0.1',
                        1 => 11211,
                    ],
                ],
                'namespace' => 'AdmAPL',
                'lib_options' => [
                    'COMPRESSION' => true,
                    'binary_protocol' => true,
                    'no_block' => true,
                    'connect_timeout' => 100,
                ],
            ],
        ],
        'FilesystemCache' => [
            'adapter' => \Laminas\Cache\Storage\Adapter\Filesystem::class,
            'options' => [
                'cache_dir' => './data/cache',
                'ttl' => 3600,
            ],
            'plugins' => [
                0 => [
                    'name' => 'serializer',
                    'options' => [],
                ],
            ],
        ],
    ],
    'doctrine' => [
        'configuration' => [
            'orm_default' => [
                'string_functions' => [
                    'match' => \DoctrineExtensions\Query\Mysql\MatchAgainst::class,
                    'group_concat' => \DoctrineExtensions\Query\Mysql\GroupConcat::class,
                    'match_against' => \DoctrineExtensions\Query\Mysql\MatchAgainst::class,
                    'ifnull' => \DoctrineExtensions\Query\Mysql\IfNull::class,
                    'replace' => \DoctrineExtensions\Query\Mysql\Replace::class,
                    'concat_ws' => \DoctrineExtensions\Query\Mysql\ConcatWs::class,
                ],
                'numeric_functions' => [
                    'floor' => \DoctrineExtensions\Query\Mysql\Floor::class,
                    'rand' => \DoctrineExtensions\Query\Mysql\Rand::class,
                    'round' => \DoctrineExtensions\Query\Mysql\Round::class,
                ],
                'datetime_functions' => [
                    'year' => \DoctrineExtensions\Query\Mysql\Year::class,
                    'month' => \DoctrineExtensions\Query\Mysql\Month::class,
                    'day' => \DoctrineExtensions\Query\Mysql\Day::class,
                    'now' => \DoctrineExtensions\Query\Mysql\Now::class,
                    'date_sub' => \DoctrineExtensions\Query\Mysql\DateSub::class,
                    'date_add' => \DoctrineExtensions\Query\Mysql\DateAdd::class,
                    'date_format' => \DoctrineExtensions\Query\Mysql\DateFormat::class,
                    'date' => \DoctrineExtensions\Query\Mysql\Date::class,
                    'last_day' => \DoctrineExtensions\Query\Mysql\LastDay::class,
                ],
            ],
        ],
        'migrations_configuration' => [
            'orm_default' => [
                'migrations_paths' => [
                    'Migrations' => 'data/Migrations',
                ],
                'table_storage' => [
                    'table_name' => 'migrations',
                    'version_column_name' => 'version',
                    'version_column_length' => 191,
                    'executed_at_column_name' => 'executedAt',
                    'execution_time_column_name' => 'executionTime',
                ],
            ],
        ],
    ],
];
