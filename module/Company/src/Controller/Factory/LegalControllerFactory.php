<?php
namespace Company\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Company\Controller\LegalController;
use Company\Service\OfficeManager;
use Application\Service\ContactManager;
use Company\Service\LegalManager;

/**
 * This is the factory for RoleController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class LegalControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $officeManager = $container->get(OfficeManager::class);
        $contactManager = $container->get(ContactManager::class);
        $legalManager = $container->get(LegalManager::class);
        
        // Instantiate the controller and inject dependencies
        return new LegalController($entityManager, $legalManager, $officeManager, $contactManager);
    }
}

