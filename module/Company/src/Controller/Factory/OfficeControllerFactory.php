<?php
namespace Company\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Company\Controller\OfficeController;
use Company\Service\OfficeManager;
use Application\Service\ContactManager;

/**
 * This is the factory for RoleController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class OfficeControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $officeManager = $container->get(OfficeManager::class);
        $contactManager = $container->get(ContactManager::class);
        
        // Instantiate the controller and inject dependencies
        return new OfficeController($entityManager, $officeManager, $contactManager);
    }
}

