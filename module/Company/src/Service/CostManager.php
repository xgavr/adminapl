<?php
namespace Company\Service;

use Company\Entity\Cost;
use Company\Entity\CostMutual;
use Stock\Entity\Movement;
use Stock\Entity\St;
use Cash\Entity\CashDoc;
use Stock\Entity\Ptu;
use Bank\Entity\Statement;
use Company\Entity\BankAccount;
use Stock\Entity\Register;
use Stock\Entity\Vtp;

/**
 * This service is responsible for adding/editing roles.
 */
class CostManager
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
     * Adds a new cost.
     * @param array $data
     */
    public function addCost($data)
    {
        $existingCost = $this->entityManager->getRepository(Cost::class)
                ->findOneByName($data['name']);
        if ($existingCost!=null) {
            throw new \Exception('Статья с таким наименованием уже есть');
        }
        
        $cost = new Cost();
        $cost->setName($data['name']);
        $cost->setAplId($data['aplId']);
        $cost->setStatus($data['status']);
        $cost->setKind($data['kind']);
        $cost->setKindFin($data['kindFin']);
        
        $this->entityManager->persist($cost);
        
        // Apply changes to database.
        $this->entityManager->flush();
        
    }
    
    /**
     * Updates an existing cost.
     * @param Cost $cost
     * @param array $data
     */
    public function updateCost($cost, $data)
    {
        $existingCost = $this->entityManager->getRepository(Cost::class)
                ->findOneByName($data['name']);
        if ($existingCost!=null && $existingCost!=$cost) {
            throw new \Exception('Другая статья с таким наименованием уже есть');
        }
        
        $cost->setName($data['name']);
        $cost->setAplId($data['aplId']);
        $cost->setStatus($data['status']);
        $cost->setKind($data['kind']);
        $cost->setKindFin($data['kindFin']);
        
        $this->entityManager->persist($cost);
        $this->entityManager->flush();
        
    }
    
    /**
     * Deletes the given cost.
     * @param Cost $cost
     */
    public function deleteCost($cost)
    {
        $this->entityManager->remove($cost);
        $this->entityManager->flush();
        
    }    
    
    /**
     * Удалить расчет
     * 
     * @param integer $docType
     * @param integer $docId
     */
    public function removeCostMutual($docType, $docId)
    {
       $costMutuals = $this->entityManager->getRepository(CostMutual::class)
               ->findBy(['docType' => $docType, 'docId' => $docId]);
       
       foreach ($costMutuals as $costMutual){
           $this->entityManager->remove($costMutual);
       }
       
       $this->entityManager->flush();
       
       return;
    }
    
    /**
     * Провести расчет
     * @param St $st
     * @param float $docStamp
     * 
     * @return CostMutual
     */
    public function repostSt($st, $docStamp)
    {
        $this->removeCostMutual(Movement::DOC_ST, $st->getId());
        
        $costMutual = null;
        
        switch ($st->getWriteOff()){

            case St::WRITE_COST:

                $amount = $this->entityManager->getRepository(St::class)
                    ->findMovementBaseAmount($st);

//                $costMutual = new CostMutual();
//                $costMutual->setAmount(abs($amount));
//                $costMutual->setCompany($st->getCompany());
//                $costMutual->setDateOper($st->getDocDate());
//                $costMutual->setDocId($st->getId());
//                $costMutual->setDocKey($st->getLogKey());
//                $costMutual->setDocStamp($docStamp);
//                $costMutual->setDocType(Movement::DOC_ST);
//                $costMutual->setStatus(CostMutual::getStatusFromSt($st));
//                $costMutual->setCost($st->getCost());

//                $this->entityManager->persist($costMutual);
                
                $this->entityManager->getConnection()->insert('cost_mutual', [
                    'amount' => abs($amount),
                    'company_id' => $st->getCompany()->getId(),
                    'date_oper' => $st->getDocDate(),
                    'doc_id' => $st->getId(),
                    'doc_key' => $st->getLogKey(),
                    'doc_stamp' => $docStamp,
                    'doc_type' => Movement::DOC_ST,
                    'status' => CostMutual::getStatusFromSt($st),
                    'cost_id' => $st->getCost()->getId(),
                ]);

                break;
        }    
        
//        $this->entityManager->flush();
        
        return $costMutual;
    }    
    
    /**
     * Провести расчет
     * @param Ptu $ptu
     * @param float $docStamp
     * 
     * @return CostMutual
     */
    public function repostPtu($ptu, $docStamp)
    {
        $this->removeCostMutual(Movement::DOC_PTU, $ptu->getId());
        
        $costMutual = null;
        
        foreach ($ptu->getPtuCosts() as $ptuCost){

//            $costMutual = new CostMutual();
//            $costMutual->setAmount($ptuCost->getAmount());
//            $costMutual->setCompany($ptu->getContract()->getCompany());
//            $costMutual->setDateOper($ptu->getDocDate());
//            $costMutual->setDocId($ptu->getId());
//            $costMutual->setDocKey($ptu->getLogKey());
//            $costMutual->setDocStamp($docStamp);
//            $costMutual->setDocType(Movement::DOC_PTU);
//            $costMutual->setStatus(CostMutual::getStatusFromPtu($ptu));
//            $costMutual->setCost($ptuCost->getCost());
//
//            $this->entityManager->persist($costMutual);
            
            $this->entityManager->getConnection()->insert('cost_mutual', [
                'amount' => $ptuCost->getAmount(),
                'company_id' => $ptu->getContract()->getCompany()->getId(),
                'date_oper' => $ptu->getDocDate(),
                'doc_id' => $ptu->getId(),
                'doc_key' => $ptu->getLogKey(),
                'doc_stamp' => $docStamp,
                'doc_type' => Movement::DOC_PTU,
                'status' => CostMutual::getStatusFromPtu($ptu),
                'cost_id' => $ptuCost->getCost()->getId(),
            ]);
        }    
        
//        $this->entityManager->flush();
        
        return $costMutual;
    }  
    
    /**
     * Провести расчет
     * @param Vtp $vtp
     * @param float $markdown Сумма уценки
     * @param float $docStamp
     * 
     * @return CostMutual
     */
    public function repostVtp($vtp, $markdown, $docStamp)
    {
        $this->removeCostMutual(Movement::DOC_VTP, $vtp->getId());
        
        $costMutual = null;
        
        $markdownCost = $this->entityManager->getRepository(Cost::class)
                ->findOneBy(['kind' => Cost::KIND_SUPPLIER_MARKDOWN]);
        
        if ($markdownCost && !empty($markdown)){
            $this->entityManager->getConnection()->insert('cost_mutual', [
                'amount' => round($markdown, 2),
                'company_id' => $vtp->getPtu()->getContract()->getCompany()->getId(),
                'date_oper' => $vtp->getDocDate(),
                'doc_id' => $vtp->getId(),
                'doc_key' => $vtp->getLogKey(),
                'doc_stamp' => $docStamp,
                'doc_type' => Movement::DOC_VTP,
                'status' => CostMutual::getStatusFromVtp($vtp),
                'cost_id' => $markdownCost->getId(),
            ]);
        }    

        return $costMutual;
    }    
    
    /**
     * Провести расчет
     * @param CashDoc $cashDoc
     * @param float $docStamp
     * 
     * @return CostMutual
     */
    public function repostCashDoc($cashDoc, $docStamp)
    {
        $this->removeCostMutual(Movement::DOC_CASH, $cashDoc->getId());
        
        $costMutual = null;
        
        switch ($cashDoc->getKind()){

            case CashDoc::KIND_OUT_COST:

//                $costMutual = new CostMutual();
//                $costMutual->setAmount($cashDoc->getAmount());
//                $costMutual->setCompany($cashDoc->getCompany());
//                $costMutual->setDateOper($cashDoc->getDateOper());
//                $costMutual->setDocId($cashDoc->getId());
//                $costMutual->setDocKey($cashDoc->getLogKey());
//                $costMutual->setDocStamp($docStamp);
//                $costMutual->setDocType(Movement::DOC_CASH);
//                $costMutual->setStatus(CostMutual::getStatusFromCashDoc($cashDoc));
//                $costMutual->setCost($cashDoc->getCost());

//                $this->entityManager->persist($costMutual);
                
                $this->entityManager->getConnection()->insert('cost_mutual', [
                    'amount' => $cashDoc->getAmount(),
                    'company_id' => $cashDoc->getCompany()->getId(),
                    'date_oper' => $cashDoc->getDateOper(),
                    'doc_id' => $cashDoc->getId(),
                    'doc_key' => $cashDoc->getLogKey(),
                    'doc_stamp' => $docStamp,
                    'doc_type' => Movement::DOC_CASH,
                    'status' => CostMutual::getStatusFromCashDoc($cashDoc),
                    'cost_id' => $cashDoc->getCost()->getId(),
                ]);
        
                break;
        }    
        
//        $this->entityManager->flush();
        
        return $costMutual;
    }   
    
    /**
     * Провести расчет
     * @param Statement $statement
     * @param float $docStamp
     * 
     * @return CostMutual
     */
    public function repostStatement($statement)
    {
        $docStamp = $this->entityManager->getRepository(Register::class)
                ->statementRegister($statement);

        $this->removeCostMutual(Movement::DOC_BANK, $statement->getId());
        
        $costMutual = null;
        
        if ($statement->getStatus() == Statement::STATUS_RETIRED){
            return;
        }
        
        $cost = null; $amount = 0;
        switch ($statement->getKind()){

            case Statement::KIND_OUT_BANK_COMMISSION:
                $cost = $this->entityManager->getRepository(Cost::class)
                        ->findOneBy(['kind' => Cost::KIND_BANK_COMMISSION]);
                $amount = abs($statement->getAmount());                
                break;
            
            case Statement::KIND_OUT_CART_PAY:
                $cost = $this->entityManager->getRepository(Cost::class)
                        ->findOneBy(['kind' => Cost::KIND_BANK_CART]);
                $amount = abs($statement->getAmount());                
                break;
            case Statement::KIND_OUT_CREDIT_RETURN:
                $cost = $this->entityManager->getRepository(Cost::class)
                        ->findOneBy(['kind' => Cost::KIND_CREDIT_RETURN]);
                $amount = abs($statement->getAmount());                
                break;
            case Statement::KIND_IN_CART:
                $cost = $this->entityManager->getRepository(Cost::class)
                        ->findOneBy(['kind' => Cost::KIND_BANK_ACQUIRING]);
                $amount = abs($statement->getAmountService());                
                break;
        }    
        
        if ($cost && $amount){
            $companyAccount = $this->entityManager->getRepository(BankAccount::class)
                    ->findOneBy(['rs' => $statement->getAccount()]);
            
//            $costMutual = new CostMutual();
//            $costMutual->setAmount($amount);
//            $costMutual->setCompany($companyAccount->getLegal());
//            $costMutual->setDateOper($statement->getChargeDate());
//            $costMutual->setDocId($statement->getId());
//            $costMutual->setDocKey($statement->getLogKey());
//            $costMutual->setDocStamp($docStamp);
//            $costMutual->setDocType(Movement::DOC_BANK);
//            $costMutual->setStatus(CostMutual::getStatusFromStatement($statement));
//            $costMutual->setCost($cost);
//            
//            $this->entityManager->persist($costMutual);
//            $this->entityManager->flush();
            
            $this->entityManager->getConnection()->insert('cost_mutual', [
                'amount' => $amount,
                'company_id' => $companyAccount->getLegal()->getId(),
                'date_oper' => $statement->getChargeDate(),
                'doc_id' => $statement->getId(),
                'doc_key' => $statement->getLogKey(),
                'doc_stamp' => $docStamp,
                'doc_type' => Movement::DOC_BANK,
                'status' => CostMutual::getStatusFromStatement($statement),
                'cost_id' => $cost->getId(),
            ]);
        }
                
        return $costMutual;
    }    
}

