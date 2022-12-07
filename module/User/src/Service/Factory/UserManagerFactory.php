<?php
namespace User\Service\Factory;

use Interop\Container\ContainerInterface;
use User\Service\UserManager;
use User\Service\RoleManager;
use User\Service\PermissionManager;
use Admin\Service\PostManager;
use Admin\Service\SmsManager;
use Admin\Service\AdminManager;
use User\Service\RbacManager;

/**
 * This is the factory class for UserManager service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 */
class UserManagerFactory
{
    /**
     * This method creates the UserManager service and returns its instance. 
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {        
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $roleManager = $container->get(RoleManager::class);
        $permissionManager = $container->get(PermissionManager::class);
        $postManager = $container->get(PostManager::class);
        $smsManager = $container->get(SmsManager::class);
        $adminManager = $container->get(AdminManager::class);
        $rbacManager = $container->get(RbacManager::class);
        
        return new UserManager($entityManager, $roleManager, $permissionManager, 
                $postManager, $smsManager, $adminManager, $rbacManager);
    }
}
