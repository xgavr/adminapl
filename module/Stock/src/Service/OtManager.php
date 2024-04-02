<?php
namespace Stock\Service;

use Stock\Entity\Vtp;
use Stock\Entity\Ot;
use Stock\Entity\Ptu;
use Stock\Entity\OtGood;
use Admin\Entity\Log;
use Stock\Entity\Movement;
use Stock\Entity\Comiss;
use Stock\Entity\Register;
use Application\Entity\Goods;
use Stock\Entity\ComissBalance;

/**
 * This service is responsible for adding/editing ptu.
 */
class OtManager
{
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;  
    
    /**
     * Log manager
     * @var \Admin\Service\LogManager
     */
    private $logManager;
        
    /**
     * Admin manager
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;
        
    /**
     * Дата запрета
     * @var string
     */
    private $allowDate;
    
    /**
     * Constructs the service.
     */
    public function __construct($entityManager, $logManager, $adminManager) 
    {
        $this->entityManager = $entityManager;
        $this->logManager = $logManager;
        $this->adminManager = $adminManager;
        
        $setting = $this->adminManager->getSettings();
        $this->allowDate = $setting['allow_date'];
        
    }
    
    /**
     * Получить дату запрета
     * @return date
     */
    public function getAllowDate()
    {
        return $this->allowDate; 
    }
            
    public function currentUser()
    {
        return $this->logManager->currentUser();
    }
    
    /**
     * Обновить движения документа
     * 
     * @param Ot $ot
     */
    public function updateOtMovement($ot)
    {
        
        $docStamp = $this->entityManager->getRepository(Register::class)
                ->otRegister($ot);

        $this->entityManager->getRepository(Movement::class)
                ->removeDocMovements($ot->getLogKey());
        
        $this->entityManager->getRepository(Comiss::class)
                ->removeDocComiss($ot->getLogKey());

              
        $otGoods = $this->entityManager->getRepository(OtGood::class)
                ->findByOt($ot->getId());

        $baseKey = $ot->getLogKey();
        $baseType = Movement::DOC_OT;
        $baseId = $ot->getId();

        foreach ($otGoods as $otGood){
            if ($ot->getStatus() != Ot::STATUS_RETIRED){  
                if ($ot->getStatus() == Ot::STATUS_ST_STORNO) {
                    $base = $this->entityManager->getRepository(Ot::class)
                        ->findStForStorno($otGood);
//                var_dump($base); exit;
                    if ($base){
                        $baseKey = $base['baseKey'];
                        $baseType = $base['baseType'];
                        $baseId = $base['baseId'];
                    }
                }

                $data = [
                    'doc_key' => $ot->getLogKey(),
                    'doc_type' => Movement::DOC_OT,
                    'doc_id' => $ot->getId(),
                    'base_key' => $baseKey,
                    'base_type' => $baseType,
                    'base_id' => $baseId,
                    'doc_row_key' => $otGood->getDocRowKey(),
                    'doc_row_no' => $otGood->getRowNo(),
                    'date_oper' => date('Y-m-d 00:00:00', strtotime($ot->getDocDate())),
                    'status' => Movement::getStatusFromOt($ot),
                    'quantity' => $otGood->getQuantity(),
                    'amount' => $otGood->getAmount(),
                    'base_amount' => $otGood->getAmount(),
                    'good_id' => $otGood->getGood()->getId(),
                    'office_id' => $ot->getOffice()->getId(),
                    'company_id' => $ot->getCompany()->getId(),
                    'doc_stamp' => $docStamp,
                    'amount_extra_type' => Movement::EXTRA_AMOUNT_UNKNOWN,
                    'amount_extra' => 0,
                ];

                $this->entityManager->getRepository(Movement::class)
                        ->insertMovement($data);

                if ($ot->getStatus() == Ot::STATUS_COMMISSION){
                    unset($data['base_key']);
                    unset($data['base_type']);
                    unset($data['base_id']);
                    unset($data['base_amount']);
                    $data['contact_id'] = $ot->getComiss()->getId();                    
                    $this->entityManager->getRepository(Comiss::class)
                            ->insertComiss($data);
                }
            }    
            
            $this->entityManager->getRepository(Movement::class)
                    ->updateGoodBalance($otGood->getGood()->getId());
            $this->entityManager->getRepository(ComissBalance::class)
                    ->updateComissBalance($otGood->getGood()->getId());            
        }    

        return;
    }    
    
    
    /**
     * Перепроведение ОТ
     * @param Ot $ot
     */
    public function repostOt($ot)
    {
        $this->updateOtMovement($ot);
        
        return;
    }

    /**
     * Перепроведение всех ОП
     */
    public function repostAllOt()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        
        $otQuery = $this->entityManager->getRepository(Ot::class)
                ->queryAllOt();
        $iterable = $otQuery->iterate();
        
        foreach ($iterable as $row){
            foreach($row as $ot){ 
                $this->repostOt($ot);
                $this->entityManager->detach($ot);
                unset($ot);
            }    
        }    
        
        return;
    }


    /**
     * Adds a new Ot.
     * @param array $data
     * @return integer
     */
    public function addOt($data)
    {
        if ($data['doc_date'] > $this->allowDate){
            $ot = new Ot();        
            $ot->setAplId($data['apl_id']);
            $ot->setDocDate($data['doc_date']);
            $ot->setComment($data['comment']);
            $ot->setStatusEx($data['status_ex']);
            $ot->setStatus($data['status']);
            $ot->setStatusDoc(Ot::STATUS_DOC_NOT_RECD);
            $ot->setStatusAccount(Ot::STATUS_ACCOUNT_NO);
            $ot->setOffice($data['office']);
            $ot->setCompany($data['company']);
            $ot->setAmount(0);
            $ot->setDateCreated(date('Y-m-d H:i:s'));
            if (!empty($data['comiss'])){
                $ot->setComiss($data['comiss']);
            }
            if (!empty($data['doc_no'])){
                //$ot->setDocNo($data['doc_no']);
            }

            $this->entityManager->persist($ot);        
            $this->entityManager->flush();
        
            return $ot;        
        }
        
        return;
    }
    
    /**
     * Update ot.
     * @param Ot $ot
     * @param array $data
     * @return integer
     */
    public function updateOt($ot, $data)            
    {
        if ($data['doc_date'] > $this->allowDate){
            $ot->setAplId($data['apl_id']);
            $ot->setDocDate($data['doc_date']);
            $ot->setComment($data['comment']);
            $ot->setStatusEx($data['status_ex']);
            $ot->setStatus($data['status']);
            $ot->setStatusAccount(Ot::STATUS_ACCOUNT_NO);
            $ot->setOffice($data['office']);
            $ot->setCompany($data['company']);
            $ot->setComiss(null);
            if (!empty($data['comiss'])){
                $ot->setComiss($data['comiss']);
            }
            if (!empty($data['doc_no'])){
//                $ot->setDocNo($data['doc_no']);
            }

            $this->entityManager->persist($ot);
            $this->entityManager->flush($ot);
        }    
        
        return;
    }
    
    /**
     * Adds a new ot-good.
     * @param integer $otId
     * @param array $data
     * @param integer $rowNo
     * 
     * @return integer
     */
    public function addOtGood($otId, $data, $rowNo)
    {
//        var_dump($data); exit;
        $otGood = [
            'ot_id' => $otId,
            'status' => (isset($data['status'])) ? $data['status']:OtGood::STATUS_ACTIVE,
            'status_doc' => (isset($data['statusDoc'])) ? $data['statusDoc']:OtGood::STATUS_DOC_RECD,
            'quantity' => $data['quantity'],
            'amount' => $data['amount'],
            'good_id' => $data['good_id'],
            'comment' => (isset($data['comment'])) ? $data['comment']:'',
//            'info' => $data['info'],
            'row_no' => $rowNo,
        ];
        //var_dump($otGood); exit;
        
        $connection = $this->entityManager->getConnection(); 
        $connection->insert('ot_good', $otGood);
        return;
    }
    
    /**
     * Update ot_good.
     * @param OtGood $otGood
     * @param array $data
     * @return integer
     */
    public function updateOtGood($otGood, $data)            
    {
        
        $connection = $this->entityManager->getConnection(); 
        $connection->update('ot_good', $data, ['id' => $otGood->getId()]);
        return;
    }
    
    /**
     * Обновить сумму ОТ
     * @param Ot $ot
     */
    public function updateOtAmount($ot)
    {
        $preLog = $this->entityManager->getRepository(Log::class)
                ->findOneByLogKey($ot->getLogKey());
        if (!$preLog){
            $this->logManager->infoOt($ot, Log::STATUS_INFO);            
        }
        
        $otAmountTotal = $this->entityManager->getRepository(Ot::class)
                ->otAmountTotal($ot);
        $ot->setAmount($otAmountTotal);
        $this->entityManager->persist($ot);
        $this->entityManager->flush($ot);
        
        $this->entityManager->refresh($ot);
        $this->repostOt($ot);
        $this->logManager->infoOt($ot, Log::STATUS_UPDATE);
        return;
    }
    
    /**
     * Удаление строк ОТ
     * @param Ot $ot
     */
    public function removeOtGood($ot)
    {
        $this->entityManager->getConnection()
                ->delete('ot_good', ['ot_id' => $ot->getId()]);
        return;
    }
    
    /**
     * Обновление строк ОТ
     * 
     * @param Ot $ot
     * @param array $data
     */
    public function updateOtGoods($ot, $data)
    {
        $this->removeOtGood($ot);
        
        $rowNo = 1;
        if ($data){
            foreach ($data as $row){
                $this->addOtGood($ot->getId(), $row, $rowNo);
                $rowNo++;
            }
        }    
        
        $this->updateOtAmount($ot);
        return;
    }       
    
    /**
     * Удаление ОТ
     * 
     * @param Ot $ot
     */
    public function removeOt($ot)
    {
        if ($data['doc_date'] > $this->allowDate){
            $this->logManager->infoOt($ot, Log::STATUS_DELETE);
            $this->entityManager->getRepository(Movement::class)
                    ->removeDocMovements($ot->getLogKey());
            $this->entityManager->getRepository(Comiss::class)
                    ->removeDocComiss($ot->getLogKey());
            $this->removeOtGood($ot);

            $this->entityManager->getConnection()->delete('ot', ['id' => $ot->getId()]);
        }    
        
        return;
    }
    
    /**
     * Заменить товар
     * @param Goods $oldGood
     * @param Goods $newGood
     */
    public function changeGood($oldGood, $newGood)
    {
        $rows = $this->entityManager->getRepository(OtGood::class)
                ->findBy(['good' => $oldGood->getId()]);
        foreach ($rows as $row){
            $row->setGood($newGood);
            $this->entityManager->persist($row);
            $this->entityManager->flush();
            $this->updateOtMovement($row->getOt());
        }
        
        return;
    }
}

