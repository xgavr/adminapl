<?php
namespace Company\Service;

use Company\Entity\Legal;

/**
 * This service is responsible for adding/editing roles.
 */
class LegalManager
{
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;  
        
    /**
     * Constructs the service.
     */
    public function __construct($entityManager) 
    {
        $this->entityManager = $entityManager;
    }
 
    public function addLegal($contact, $data, $flushnow = false)
    {                
        $legal = $this->entityManager->getRepository(Legal::class)
                ->findOneByInnKpp($data['inn'], $data['kpp']);

        if ($legal == null){
            $legal = new Legal();            
            $legal->setName($data['name']);            
            $legal->setInn($data['inn']);            
            $legal->setKpp($data['kpp']);            
            $legal->setOgrn($data['ogrn']);            
            $legal->setOkpo($data['okpo']);            
            $legal->setHead($data['head']);            
            $legal->setChiefAccount($data['chiefAccount']);            
            $legal->setInfo($data['info']);            
            $legal->setAddress($data['address']);            
            $legal->setStatus($data['status']);            

            $currentDate = date('Y-m-d H:i:s');
            $legal->setDateCreated($currentDate);
            
            if ($data['dateStart']){
                $legal->setDateStart($data['dateStart']);
            } else {
                $legal->setDateStart($currentDate);
            }
            
        } else {
            $legal->setName($data['name']);            
            $legal->setInn($data['inn']);            
            $legal->setKpp($data['kpp']);            
            $legal->setOgrn($data['ogrn']);            
            $legal->setOkpo($data['okpo']);            
            $legal->setHead($data['head']);            
            $legal->setChiefAccount($data['chiefAccount']);            
            $legal->setInfo($data['info']);            
            $legal->setAddress($data['address']);            
            $legal->setStatus($data['status']);            
            $legal->setDateStart($data['dateStart']);
        }   

        $this->entityManager->persist($legal);
        
        $contact->removeLegalAssociation($legal);
        $legal->addContact($contact);
            
        if ($flushnow){
            $this->entityManager->flush();                
        }
    }
    
    public function removeLegalAssociation($legal)
    {
        $contacts = $legal->getContacts();
        foreach ($contacts as $contact){
            $contact->removeLegalAssociation($legal);
        }        
        
        $this->entityManager->flush();
    }
    
    public function removeLegal($legal)
    {
        $contacts = $legal->getContacts();
        foreach ($contacts as $contact){
            $contact->removeLegalAssociation($legal);
        }
        
        $contracts = $legal->getContracts();
        foreach ($contracts as $contract){
            $this->entityManager->remove($contract);
        }
        
        $bankAccounts = $legal->getBankAccounts();
        foreach ($bankAccounts as $bankAccount){
            $this->entityManager->remove($bankAccount);
        }
        
        $this->entityManager->remove($legal);

        $this->entityManager->flush();
    }    
        
}

