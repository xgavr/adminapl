<?php
namespace Stock\Service;

use Stock\Entity\Vtp;
use Stock\Entity\Ptu;
use Stock\Entity\VtpGood;
use Admin\Entity\Log;
use Stock\Entity\Movement;
use Stock\Entity\Mutual;
use Stock\Entity\Register;
use Stock\Entity\Reserve;
use Laminas\Json\Json;

/**
 * This service is responsible for adding/editing ptu.
 */
class VtpManager
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
    
    /**
     * Обновить взаиморасчеты документа
     * 
     * @param Vtp $vtp
     */
    public function updateVtpMutuals($vtp)
    {
        
        $this->entityManager->getRepository(Mutual::class)
                ->removeDocMutuals($vtp->getLogKey());
        
        if ($vtp->getStatus() == Vtp::STATUS_ACTIVE && $vtp->getStatusDoc() == Vtp::STATUS_DOC_NOT_RECD){
            $data = [
                'doc_key' => $vtp->getLogKey(),
                'doc_type' => Movement::DOC_VTP,
                'doc_id' => $vtp->getId(),
                'date_oper' => $vtp->getDocDate(),
                'status' => $vtp->getStatus(),
                'revise' => Mutual::REVISE_NOT,
                'amount' => $vtp->getAmount(),
                'legal_id' => $vtp->getPtu()->getLegal()->getId(),
                'contract_id' => $vtp->getPtu()->getContract()->getId(),
                'office_id' => $vtp->getPtu()->getOffice()->getId(),
                'company_id' => $vtp->getPtu()->getContract()->getCompany()->getId(),
            ];

            $this->entityManager->getRepository(Mutual::class)
                    ->insertMutual($data);
        }    
         
        return;
    }    
    
    /**
     * Обновить движения документа
     * 
     * @param Vtp $vtp
     */
    public function updateVtpMovement($vtp)
    {
        
        $docStamp = $this->entityManager->getRepository(Register::class)
                ->vtpRegister($vtp);
        $this->entityManager->getRepository(Movement::class)
                ->removeDocMovements($vtp->getLogKey());
        $vtpTake = Vtp::STATUS_ACCOUNT_NO;
        if ($vtp->getStatus() == Vtp::STATUS_ACTIVE && $vtp->getStatusDoc() == Vtp::STATUS_DOC_NOT_RECD){
            $vtpGoods = $this->entityManager->getRepository(VtpGood::class)
                    ->findByVtp($vtp->getId());
            foreach ($vtpGoods as $vtpGood){
                $bases = $this->entityManager->getRepository(Movement::class)
                        ->findBases($vtpGood->getGood()->getId(), $docStamp, $vtp->getPtu()->getOffice()->getId(), $vtp->getPtu()->getLogKey());
                
                $write = $vtpGood->getQuantity();
                
                $take = VtpGood::TAKE_NO;
                
                foreach ($bases as $base){
                    $movement = $this->entityManager->getRepository(Movement::class)
                            ->findOneByBaseKey($base['baseKey']);
                    
                    $quantity = min($base['rest'], $write);
                    $amount = $quantity*$vtpGood->getAmount()/$vtpGood->getQuantity();
                    $baseAmount = $base['price']*$quantity;
                    
                    $data = [
                        'doc_key' => $vtp->getLogKey(),
                        'doc_type' => Movement::DOC_VTP,
                        'doc_id' => $vtp->getId(),
                        'base_type' => $movement->getBaseType(),
                        'base_key' => $movement->getBaseKey(),
                        'base_id' => $movement->getBaseId(),
                        'doc_row_key' => $vtpGood->getDocRowKey(),
                        'doc_row_no' => $vtpGood->getRowNo(),
                        'date_oper' => date('Y-m-d 23:00:00', strtotime($vtp->getDocDate())),
                        'status' => Movement::getStatusFromVtp($vtp),
                        'quantity' => -$quantity,
                        'amount' => -$amount,
                        'base_amount' => -$baseAmount,
                        'good_id' => $vtpGood->getGood()->getId(),
                        'office_id' => $vtp->getPtu()->getOffice()->getId(),
                        'company_id' => $vtp->getPtu()->getContract()->getCompany()->getId(),
                        'doc_stamp' => $docStamp,
                    ];

                    $this->entityManager->getRepository(Movement::class)
                            ->insertMovement($data);
                    
                    $write -= $quantity;
                    if ($write <= 0){
                        break;
                    }                   
                }    
                if ($write == 0){
                    $take = VtpGood::TAKE_OK;
                } else {
                    $vtpTake = Vtp::STATUS_TAKE_NO;
                }
                $this->entityManager->getConnection()
                        ->update('vtp_good', ['take' => $take], ['id' => $vtpGood->getId()]);
                $this->entityManager->getRepository(Movement::class)
                        ->updateGoodBalance($vtpGood->getGood()->getId(), $vtp->getPtu()->getOffice()->getId(), $vtp->getPtu()->getContract()->getCompany()->getId());
            }
        }    

        $this->entityManager->getConnection()
                ->update('vtp', ['status_account' => $vtpTake], ['id' => $vtp->getId()]);        
        
        return;
    }    
    
    
    /**
     * Перепроведение ВТП
     * @param Vtp $vtp
     */
    public function repostVtp($vtp)
    {
        $this->entityManager->getRepository(Reserve::class)
            ->updateReserve($vtp);
        $this->updateVtpMovement($vtp);
        $this->updateVtpMutuals($vtp);
        
        return true;
    }

    /**
     * Перепроведение всех ВТП
     */
    public function repostAllVtp()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        
        $vtpQuery = $this->entityManager->getRepository(Vtp::class)
                ->queryAllVtp();
        $iterable = $vtpQuery->iterate();
        
        foreach ($iterable as $row){
            foreach($row as $vtp){ 
                $this->repostVtp($vtp);
                $this->entityManager->detach($vtp);
                unset($vtp);
            }    
        }    
        
        return;
    }


    /**
     * Adds a new vtp.
     * @param Ptu $ptu
     * @param array $data
     * @return integer
     */
    public function addVtp($ptu, $data)
    {
        if ($data['doc_date'] > $this->allowDate){
            
            $vtp = new Vtp();        
            $vtp->setPtu($ptu);
            $vtp->setAplId($data['apl_id']);
            //$vtp->setDocNo($data['doc_no']);
            $vtp->setDocDate($data['doc_date']);
            $vtp->setComment(empty($data['comment']) ? null:$data['comment']);
            $vtp->setCause(empty($data['cause']) ? null:$data['cause']);
            $vtp->setInfo(empty($data['info']) ? null:$data['info']);
            $vtp->setStatusEx($data['status_ex']);
            $vtp->setStatusAccount(Vtp::STATUS_ACCOUNT_NO);
            $vtp->setStatus($data['status']);
            $vtp->setStatusDoc($data['statusDoc']);
            $vtp->setAmount(0);
            $vtp->setDateCreated(date('Y-m-d H:i:s'));

            $this->entityManager->persist($vtp);        
            $this->entityManager->flush();

            return $vtp;        
        }    
    }
    
    /**
     * Update vtp.
     * @param Vtp $vtp
     * @param array $data
     * @return Vtp
     */
    public function updateVtp($vtp, $data)            
    {
//        $connection = $this->entityManager->getConnection(); 
//        $connection->update('ptu', $data, ['id' => $ptu->getId()]);
//        var_dump($data); exit;
        if ($data['doc_date'] > $this->allowDate){
            $vtp->setAplId($data['apl_id']);
            $vtp->setDocNo(empty($data['doc_no']) ? null:$data['doc_no']);
            $vtp->setDocDate($data['doc_date']);
            $vtp->setComment(empty($data['comment']) ? null:$data['comment']);
            $vtp->setCause(empty($data['cause']) ? null:$data['cause']);
            $vtp->setInfo(empty($data['info']) ? null:$data['info']);
            $vtp->setStatusEx($data['status_ex']);
            $vtp->setStatusAccount(Vtp::STATUS_ACCOUNT_NO);
            $vtp->setStatus($data['status']);
            $vtp->setStatusDoc($data['statusDoc']);
            
            if (!empty($data['ptuId'])){
                $ptu = $this->entityManager->getRepository(Ptu::class)
                        ->find($data['ptuId']);
                if ($ptu){
                    $vtp->setPtu($ptu);
                }    
            }

            $this->entityManager->persist($vtp);
            $this->entityManager->flush($vtp);

            $this->repostVtp($vtp);
            $this->logManager->infoVtp($vtp, Log::STATUS_UPDATE);

            return $vtp;
        }    
    }
    
    /**
     * Update vtp status.
     * @param Vtp $vtp
     * @param integer $status
     * @return integer
     */
    public function updateVtpStatus($vtp, $status)            
    {

        if ($vtp->getDocDate() > $this->allowDate){
            $vtp->setStatus($status);
            $vtp->setStatusEx(Vtp::STATUS_EX_NEW);

            $this->entityManager->persist($vtp);
            $this->entityManager->flush($vtp);

            $this->repostVtp($vtp);
            $this->logManager->infoVtp($vtp, Log::STATUS_UPDATE);
        }    
        
        return;
    }

    /**
     * Update vtp doc status.
     * @param Vtp $vtp
     * @param integer $statusDoc
     * @return integer
     */
    public function updateVtpDocStatus($vtp, $statusDoc)            
    {

        if ($vtp->getDocDate() > $this->allowDate){
            $vtp->setStatusDoc($statusDoc);

            $this->entityManager->persist($vtp);
            $this->entityManager->flush($vtp);
            $this->entityManager->refresh($vtp);

            $this->repostVtp($vtp);
            $this->logManager->infoVtp($vtp, Log::STATUS_UPDATE);
            
            return $vtp->toLog();
        }    
        
        return;
    }

    /**
     * Update vtp comment.
     * @param Vtp $vtp
     * @param string $comment
     * @return integer
     */
    public function updateVtpComment($vtp, $comment)            
    {

        if ($vtp->getDocDate() > $this->allowDate){
            $vtp->setComment($comment);

            $this->entityManager->persist($vtp);
            $this->entityManager->flush($vtp);

            $this->logManager->infoVtp($vtp, Log::STATUS_UPDATE);
        }    
        
        return;
    }

    /**
     * Adds a new vtp-good.
     * @param integer $vtpId
     * @param array $data
     * @param integer $rowNo
     * 
     * @return integer
     */
    public function addVtpGood($vtpId, $data, $rowNo)
    {
//        var_dump($data); exit;
        $vtpGood = [
            'vtp_id' => $vtpId,
            'status' => (isset($data['status'])) ? $data['status']:VtpGood::STATUS_ACTIVE,
            'status_doc' => (isset($data['statusDoc'])) ? $data['statusDoc']:VtpGood::STATUS_DOC_RECD,
            'quantity' => $data['quantity'],
            'amount' => $data['amount'],
            'good_id' => $data['good_id'],
            'comment' => (isset($data['comment'])) ? $data['comment']:'',
//            'info' => $data['info'],
            'take' => VtpGood::TAKE_NO,
            'row_no' => $rowNo,
        ];
        
        $connection = $this->entityManager->getConnection(); 
        $connection->insert('vtp_good', $vtpGood);
        return $vtpGood;
    }
    
    /**
     * Update vtp_good.
     * @param VtpGood $vtpGood
     * @param array $data
     * @return integer
     */
    public function updateVtpGood($vtpGood, $data)            
    {
        
        $connection = $this->entityManager->getConnection(); 
        $connection->update('vtp_good', $data, ['id' => $vtpGood->getId()]);
        return;
    }
    
    /**
     * Обновить сумму ВТП
     * @param Vtp $vtp
     */
    public function updateVtpAmount($vtp)
    {
        if ($vtp->getDocDate() > $this->allowDate){        
            $preLog = $this->entityManager->getRepository(Log::class)
                    ->findOneByLogKey($vtp->getLogKey());
            if (!$preLog){
                $this->logManager->infoVtp($vtp, Log::STATUS_INFO);            
            }

            $vtpAmountTotal = $this->entityManager->getRepository(Vtp::class)
                    ->vtpAmountTotal($vtp);

            $vtp->setInfo(Json::encode($this->vtpInfo($vtp)));
            
            $vtp->setAmount($vtpAmountTotal);
            $this->entityManager->persist($vtp);
            $this->entityManager->flush($vtp);

            $this->entityManager->refresh($vtp);
            $this->repostVtp($vtp);
            $this->logManager->infoVtp($vtp, Log::STATUS_UPDATE);
        }    
        return;
    }
    
    /**
     * Удаление строк ВТП
     * @param Vtp $vtp
     */
    public function removeVtpGood($vtp)
    {
        $this->entityManager->getConnection()
                ->delete('vtp_good', ['vtp_id' => $vtp->getId()]);
        return;
    }
    
    /**
     * Обновление строк ВТП
     * 
     * @param Vtp $vtp
     * @param array $data
     */
    public function updateVtpGoods($vtp, $data)
    {
        
        if ($vtp->getDocDate() > $this->allowDate){
            $this->removeVtpGood($vtp);

            $rowNo = 1;
            foreach ($data as $row){
                $this->addVtpGood($vtp->getId(), $row, $rowNo);                
                $rowNo++;
            }

            $this->updateVtpAmount($vtp);
        }    
        return;
    }   
    
    /**
     * Получить инфо возврата
     * @param Vtp $vtp
     */
    private function vtpInfo($vtp)
    {
        $vtpGoods = $this->entityManager->getRepository(VtpGood::class)
                ->findBy(['vtp' => $vtp->getId()]);
        $info = [];
        foreach ($vtpGoods as $vtpGood){
            $info[$vtpGood->getRowNo()] = $vtpGood->toLog();
        }
        return $info;
    }
    
    /**
     * Обновить инфо возврата
     * @param Vtp $vtp
     */
    private function updateVtpInfo($vtp)
    {        
        $this->entityManager->getConnection()
                ->update('vtp', ['info' => Json::encode($this->vtpInfo($vtp))], ['id' => $vtp->getId()]);
        
        return;
    }
    
    /**
     * Обновить все инфо
     */
    public function updateAllInfo()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);
        
        $vtps = $this->entityManager->getRepository(Vtp::class)
                ->findAll();
        foreach ($vtps as $vtp){
            $this->updateVtpInfo($vtp);
            $this->entityManager->detach($vtp);
        }
        
        return;
    }
    
    /**
     * Ужаление ВТП
     * 
     * @param Vtp $vtp
     */
    public function removeVtp($vtp)
    {
        if ($vtp->getDocDate() > $this->allowDate){
            $this->logManager->infoVtp($vtp, Log::STATUS_DELETE);
            $this->entityManager->getRepository(Mutual::class)
                    ->removeDocMutuals($vtp->getLogKey());
            $this->entityManager->getRepository(Movement::class)
                    ->removeDocMovements($vtp->getLogKey());
            $this->removeVtpGood($vtp);

            $this->entityManager->getConnection()->delete('vtp', ['id' => $vtp->getId()]);
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
        $rows = $this->entityManager->getRepository(VtpGood::class)
                ->findBy(['good' => $oldGood->getId()]);
        foreach ($rows as $row){
            $row->setGood($newGood);
            $this->entityManager->persist($row);
            $this->entityManager->flush();
            $this->updateVtpMovement($row->getVtp());
        }
        
        return;
    }            
}

