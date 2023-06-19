<?php
namespace Company\Service;

use Company\Entity\Office;
use Company\Entity\Region;
use Company\Entity\Commission;

/**
 * This service is responsible for adding/editing office.
 */
class OfficeManager
{
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;  
        
    /**
     * Constructs the service.
     */
    public function __construct($entityManager) 
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Adds a new office.
     * @param array $data
     */
    public function addOffice($data)
    {
        $existingOffice = $this->entityManager->getRepository(Office::class)
                ->findOneByName($data['name']);
        if ($existingOffice!=null) {
            throw new \Exception('Офис с таким наименованием уже есть');
        }
        
        $region = $this->entityManager->getRepository(Region::class)
                    ->findOneById($data['region']);
        if ($region == null){
            throw new \Exception('Не указан регион');
        }

        $office = new Office();
        $office->setName($data['name']);
        $office->setFullName($data['name']);
        $office->setAplId($data['aplId']);
        $office->setStatus($data['status']);
        $office->setShippingLimit1($data['shippingLimit1']);
        $office->setShippingLimit2($data['shippingLimit2']);
        $office->setSbCard($data['sbCard']);
        $office->setSbOwner($data['sbOwner']);
        $office->setSbpMerchantId((empty($data['sbpMerchantId'])) ? null:$data['sbpMerchantId']); 
        
        $currentDate = date('Y-m-d H:i:s');        
        $office->setDateCreated($currentDate);
        
        $office->setRegion($region);
        
        $this->entityManager->persist($office);
        
        // Apply changes to database.
        $this->entityManager->flush();
        
        return $office;
    }
    
    /**
     * Updates an existing office.
     * @param Office $office
     * @param array $data
     */
    public function updateOffice($office, $data)
    {
        $existingOffice = $this->entityManager->getRepository(Office::class)
                ->findOneByName($data['name']);
        
        if ($existingOffice!=null && $existingOffice!=$office) {
            throw new \Exception('Другой офис с таким наименованием уже есть');
        }
        
        $region = $this->entityManager->getRepository(Region::class)
                    ->findOneById($data['region']);
        
        if ($region == null){
            throw new \Exception('Не указан регион');
        }
        
        $office->setName($data['name']);
        $office->setFullName($data['name']);
        $office->setAplId($data['aplId']);
        $office->setStatus($data['status']);
        $office->setShippingLimit1($data['shippingLimit1']);
        $office->setShippingLimit2($data['shippingLimit2']);
        $office->setSbCard($data['sbCard']);
        $office->setSbOwner($data['sbOwner']);
        $office->setSbpMerchantId((empty($data['sbpMerchantId'])) ? null:$data['sbpMerchantId']); 
        
        $office->setRegion($region);
        
        $this->entityManager->persist($office);
        $this->entityManager->flush();
        
    }
    
    /**
     * Удалить офис
     * 
     * @param Office $office
     */
    public function deleteOffice($office)
    {
        foreach ($office->getContacts() as $contact){
            $this->entityManager->remove($contact);
        }
        foreach ($office->getContracts() as $contract){
            $this->entityManager->remove($contract);
        }
        foreach ($office->getRates() as $rate){
            $this->entityManager->remove($rate);
        }
        $this->entityManager->remove($office);
        $this->entityManager->flush();
        
    }

    /**
     * Добавить комиссара
     * @param Office $office
     * @param array $data
     * @return Commission 
     */
    public function addCommissar($office, $data)
    {
        $commission = new Commission();
        $commission->setName(empty($data['name'])? null:$data['name']);
        $commission->setPosition(empty($data['position'])? null:$data['position']);
        $commission->setStatus(empty($data['status'])? Commission::STATUS_MEMBER:$data['status']);
        $commission->setOffice($office);
        
        $this->entityManager->persist($commission);
        $this->entityManager->flush($commission);
        
        return $commission;
    }

    /**
     * Обновить комиссара
     * @param Commission $commission
     * @param array $data
     * @return Commission 
     */
    public function updateCommissar($commission, $data)
    {
        $commission->setName(empty($data['name'])? null:$data['name']);
        $commission->setPosition(empty($data['position'])? null:$data['position']);
        $commission->setStatus(empty($data['status'])? Commission::STATUS_MEMBER:$data['status']);
        
        $this->entityManager->persist($commission);
        $this->entityManager->flush($commission);
        
        return $commission;
    }
    
    /**
     * Удалить комиссара
     * @param Commission $commission
     * @return null
     */
    public function removeCommissar($commission)
    {        
        $this->entityManager->remove($commission);
        $this->entityManager->flush();
        
        return;
    }    
}

