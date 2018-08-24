<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

use Zend\Session\Storage\SessionArrayStorage;
use Zend\Session\Validator\RemoteAddr;
use Zend\Session\Validator\HttpUserAgent;
use Zend\Cache\Storage\Adapter\Filesystem;
use Zend\Cache\Storage\Adapter\Memcached;
use Zend\Cache\Storage\Adapter\Memcache;

return [
    // Настройка сессии.
    'session_config' => [
        // Срок действия cookie сессии истечет через 1 час.
        'cookie_lifetime' => 60*60*1,     
        // Данные сессии будут храниться на сервере до 30 дней.
        'gc_maxlifetime'     => 60*60*24*30, 
    ],
    // Настройка менеджера сессий.
    'session_manager' => [
        // Валидаторы сессии (используются для безопасности).
        'validators' => [
            RemoteAddr::class,
            HttpUserAgent::class,
        ]
    ],
    // Настройка хранилища сессий.
    'session_storage' => [
        'type' => SessionArrayStorage::class
    ],
    'caches' => [
        'memcache' => [
            'adapter' =>[
                'name' => Memcache::class,
                'options' => [
                    'ttl' => 60*60*1,
                    'servers' => [
                        [
                            '127.0.0.1', 11211
                        ]
                    ],
                    'namespace' => 'AdmAPL',
//                    'liboptions' => [
//                        'COMPRESSION' => true,
//                        'binary_protocol' => true,
//                        'no_block' => true,
//                        'connect_timeout' => 100,
//                    ],
                ],
            ],
            'plugins' => [
                'exception_handler' => [
                    'throw_exceptions' => false,
                ],
            ],
            'serializer',
        ],
        'memcached' => [
            'adapter' =>[
                'name' => Memcached::class,
                'options' => [
                    'ttl' => 60*60*1,
                    'servers' => [
                        [
                            '127.0.0.1', 11211
                        ]
                    ],
                    'namespace' => 'AdmAPL',
                    'liboptions' => [
                        'COMPRESSION' => true,
                        'binary_protocol' => true,
                        'no_block' => true,
                        'connect_timeout' => 100,
                    ],
                ],
            ],
            'plugins' => [
                'exception_handler' => [
                    'throw_exceptions' => false,
                ],
            ],
            'serializer',
        ],
        'FilesystemCache' => [
            'adapter' => [
                'name'    => Filesystem::class,
                'options' => [
                    // Store cached data in this directory.
                    'cache_dir' => './data/cache',
                    // Store cached data for 1 hour.
                    'ttl' => 60*60*1 
                ],
            ],
            'plugins' => [
                [
                    'name' => 'serializer',
                    'options' => [                        
                    ],
                ],
            ],
        ],
    ],
    'doctrine' => [        
        // расширение функций DQL
        'configuration' => [
            'orm_default' => [
                'string_functions' => [
                    'match' => 'DoctrineExtensions\Query\Mysql\MatchAgainst',
                ],
                'numeric_functions' => [
                    'round' => 'DoctrineExtensions\Query\Mysql\Round',
                    'floor' => 'DoctrineExtensions\Query\Mysql\Floor',
                ],
            ],
        ],
        // настройка миграций
        'migrations_configuration' => [
            'orm_default' => [
                'directory' => 'data/Migrations',
                'name'      => 'Doctrine Database Migrations',
                'namespace' => 'Migrations',
                'table'     => 'migrations',
            ],
        ],
    ],
];
