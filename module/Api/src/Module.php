<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Api;

use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ApiTools\Provider\ApiToolsProviderInterface;
use Laminas\Stdlib\ArrayUtils;

class Module implements ApiToolsProviderInterface
{
    const VERSION = '0.0.1-dev';
    
    public function getConfig()
    {
        return ArrayUtils::merge(
            include __DIR__ . '/../config/module.config.php',
            include __DIR__ . '/../config/permission.config.php',
        );
        
        //return include __DIR__ . '/../config/module.config.php';
    }

}
