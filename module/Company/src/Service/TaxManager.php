<?php
namespace Company\Service;

use Company\Entity\Tax;
use Company\Entity\TaxMutual;
use Zp\Entity\DocCalculator;
use Stock\Entity\Movement;

/**
 * This service is responsible for adding/editing roles.
 */
class TaxManager
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
     * Adds a new tax.
     * @param array $data
     * @return Tax
     */
    public function addTax($data)
    {

        $tax = new Tax();
        $tax->setAmount($data['amount']);
        $tax->setDateStart($data['dateStart']);
        $tax->setKind($data['kind']);
        $tax->setStatus($data['status']);
        $tax->setName($data['name']);
        
        $this->entityManager->persist($tax);
        
        // Apply changes to database.
        $this->entityManager->flush();
        
        return $tax;
    }
    
    /**
     * Updates an existing tax.
     * @param Tax $tax
     * @param array $data
     */
    public function updateTax($tax, $data)
    {
        
        $tax->setAmount($data['amount']);
        $tax->setDateStart($data['dateStart']);
        $tax->setKind($data['kind']);
        $tax->setStatus($data['status']);
        $tax->setName($data['name']);
        
        $this->entityManager->persist($tax);
        $this->entityManager->flush();
        
    }
    
    /**
     * Deletes the given tax.
     */
    public function deleteTax($tax)
    {
        $this->entityManager->remove($tax);
        $this->entityManager->flush();
        
    }    
    
    /**
     * Удалить расчет
     * 
     * @param integer $docType
     * @param integer $docId
     */
    public function removeTaxMutual($docType, $docId)
    {
       $taxMutuals = $this->entityManager->getRepository(TaxMutual::class)
               ->findBy(['docType' => $docType, 'docId' => $docId]);
       
       foreach ($taxMutuals as $taxMutual){
           $this->entityManager->remove($taxMutual);
       }
       
       $this->entityManager->flush();
       
       return;
    }
    
    /**
     * Провести расчет подоходного налога
     * @param DocCalculator $docCalculator
     * @param float $docStamp
     * 
     * @return TaxMutual
     */
    public function repostDocCalculatorIncomeTax($docCalculator, $docStamp)
    {
        $this->removeTaxMutual(Movement::DOC_ZP, $docCalculator->getId());
        
        $tax = $this->entityManager->getRepository(Tax::class)
                ->currentTax(Tax::KIND_INC, $docCalculator->getDateOper());
        $amount = abs($docCalculator->getAmount())*$tax->getAmount()/100;
        
        $taxMutual = new TaxMutual();
        $taxMutual->setAmount(-$amount);
        $taxMutual->setCompany($docCalculator->getCompany());
        $taxMutual->setDateOper($docCalculator->getDateOper());
        $taxMutual->setDocId($docCalculator->getId());
        $taxMutual->setDocKey($docCalculator->getLogKey());
        $taxMutual->setDocStamp($docStamp);
        $taxMutual->setDocType(Movement::DOC_ZP);
        $taxMutual->setStatus(TaxMutual::getStatusFromDocCalculator($docCalculator));
        $taxMutual->setTax($tax);

        $this->entityManager->persist($taxMutual);
        
        $this->entityManager->flush();
        
        return $taxMutual;
    }   
    
    /**
     * Провести расчет ЕСН
     * @param DocCalculator $docCalculator
     * @param float $docStamp
     * 
     * @return TaxMutual
     */
    public function repostDocCalculatorEsn($docCalculator, $docStamp)
    {
        $this->removeTaxMutual(Movement::DOC_ZP, $docCalculator->getId());
        
        $tax = $this->entityManager->getRepository(Tax::class)
                ->currentTax(Tax::KIND_ESN, $docCalculator->getDateOper());
        $amount = abs($docCalculator->getAmount())*$tax->getAmount()/100;
        
        $taxMutual = new TaxMutual();
        $taxMutual->setAmount(-$amount);
        $taxMutual->setCompany($docCalculator->getCompany());
        $taxMutual->setDateOper($docCalculator->getDateOper());
        $taxMutual->setDocId($docCalculator->getId());
        $taxMutual->setDocKey($docCalculator->getLogKey());
        $taxMutual->setDocStamp($docStamp);
        $taxMutual->setDocType(Movement::DOC_ZP);
        $taxMutual->setStatus(TaxMutual::getStatusFromDocCalculator($docCalculator));
        $taxMutual->setTax($tax);

        $this->entityManager->persist($taxMutual);
        
        $this->entityManager->flush();
        
        return $taxMutual;
    }        
}

