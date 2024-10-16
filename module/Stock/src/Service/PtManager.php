<?php
namespace Stock\Service;

use Stock\Entity\Pt;
use Stock\Entity\PtGood;
use Admin\Entity\Log;
use Stock\Entity\Movement;
use Stock\Entity\Mutual;
use Stock\Entity\Retail;
use Stock\Entity\Comiss;
use Company\Entity\Office;
use Stock\Entity\Register;
use Application\Entity\SupplierOrder;
use Application\Entity\Goods;
use Stock\Entity\PtSheduler;
use Application\Entity\Order;
use Laminas\Json\Encoder;

/**
 * This service is responsible for adding/editing pt.
 */
class PtManager
{
    /**
     *Номер для автоперемещений
     * @var string 
     */
    private $autoPtDocNo = '#АГ';
    
    /**
     *Номер для перемещений руками
     * @var string 
     */
    private $handPtDocNo = '#РГ';

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
     * Order manager
     * @var \Application\Service\OrderManager
     */
    private $orderManager;
        
    /**
     * Fold manager
     * @var \GoodMap\Service\FoldManager
     */
    private $foldManager;
        
    /**
     * Дата запрета
     * @var string
     */
    private $allowDate;

    /**
     * Constructs the service.
     */
    public function __construct($entityManager, $logManager, $orderManager, 
            $adminManager, $foldManager) 
    {
        $this->entityManager = $entityManager;
        $this->logManager = $logManager;
        $this->orderManager = $orderManager;
        $this->adminManager = $adminManager;
        $this->foldManager = $foldManager;

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
    
    public function handPtDocNo()
    {
        return $this->handPtDocNo;
    }
    
    /**
     * Обновить движения документа
     * 
     * @param Pt $pt
     * @param float $docStamp
     */
    public function updatePtMovement($pt, $docStamp)
    {
        
       $this->entityManager->getRepository(Movement::class)
                ->removeDocMovements($pt->getLogKey());
        $this->entityManager->getRepository(Comiss::class)
                ->removeDocComiss($pt->getLogKey());
                
        $ptTake = $pt->getStatusAccount();
        if ($pt->getStatusAccount() == Pt::STATUS_TAKE_NO){
            $ptTake = Pt::STATUS_ACCOUNT_NO;
        }
        
        $ptGoods = $this->entityManager->getRepository(PtGood::class)
                ->findByPt($pt->getId());

        foreach ($ptGoods as $ptGood){

            if ($pt->getStatus() == Pt::STATUS_ACTIVE){
            
                $bases = $this->entityManager->getRepository(Movement::class)
                        ->findBases($ptGood->getGood()->getId(), $docStamp, $pt->getOffice()->getId(), $ptGood->getBaseKey());

                $write = $ptGood->getQuantity();

                $take = PtGood::TAKE_NO;
                
                foreach ($bases as $base){
             
                    $movement = $this->entityManager->getRepository(Movement::class)
                            ->findOneByDocKey($base['baseKey']);
                    if ($movement){
                        $quantity = min($base['rest'], $write);
                        $baseAmount = $base['basePrice']*$quantity;

                        $data = [
                            'doc_key' => $pt->getLogKey(),
                            'doc_type' => Movement::DOC_PT,
                            'doc_id' => $pt->getId(),
                            'base_key' => $movement->getBaseKey(),
                            'base_type' => $movement->getBaseType(),
                            'base_id' => $movement->getBaseId(),
                            'doc_row_key' => $ptGood->getDocRowKey(),
                            'doc_row_no' => $ptGood->getRowNo(),
                            'date_oper' => date('Y-m-d 12:00:00', strtotime($pt->getDocDate())),
                            'status' => $movement->getStatus(),
                            'quantity' => -$quantity,
                            'amount' => -$baseAmount,
                            'base_amount' => -$baseAmount,
                            'good_id' => $ptGood->getGood()->getId(),
                            'office_id' => $pt->getOffice()->getId(),
                            'company_id' => $pt->getCompany()->getId(),
                            'doc_stamp' => $docStamp,
                        ];

                        $this->entityManager->getRepository(Movement::class)
                                ->insertMovement($data);            

                        if ($movement->getStatus() == Movement::STATUS_COMMISSION){
                            $comiss = $this->entityManager->getRepository(Comiss::class)
                                    ->findOneByDocKey($base['baseKey']);
                            $data = [
                                'doc_key' => $pt->getLogKey(),
                                'doc_type' => Movement::DOC_PT,
                                'doc_id' => $pt->getId(),
                                'doc_row_key' => $ptGood->getDocRowKey(),
                                'doc_row_no' => $ptGood->getRowNo(),
                                'date_oper' => $pt->getDocDate(),
                                'status' => $movement->getStatus(),
                                'quantity' => -$quantity,
                                'amount' => -$baseAmount,
                                'good_id' => $ptGood->getGood()->getId(),
                                'office_id' => $pt->getOffice()->getId(),
                                'company_id' => $pt->getCompany()->getId(),
                                'contact_id' => $comiss->getContact()->getId(),
                                'doc_stamp' => $docStamp,
                            ];
                            $this->entityManager->getRepository(Comiss::class)
                                    ->insertComiss($data);
                        }    

                        $data2 = [
                            'doc_key' => $pt->getLogKey(),
                            'doc_type' => Movement::DOC_PT,
                            'doc_id' => $pt->getId(),
                            'base_key' => $movement->getBaseKey(),
                            'base_type' => $movement->getBaseType(),
                            'base_id' => $movement->getBaseId(),
                            'doc_row_key' => $ptGood->getDocRowKey(),
                            'doc_row_no' => $ptGood->getRowNo(),
                            'date_oper' => date('Y-m-d 12:00:00', strtotime($pt->getDocDate())),
                            'status' => $movement->getStatus(),
                            'quantity' => $quantity,
                            'amount' => $baseAmount,
                            'base_amount' => $baseAmount,
                            'good_id' => $ptGood->getGood()->getId(),
                            'office_id' => $pt->getOffice2()->getId(),
                            'company_id' => $pt->getCompany2()->getId(),
                            'doc_stamp' => $docStamp - 60*60*6, //-6 часов,
                        ];

                        $this->entityManager->getRepository(Movement::class)
                                ->insertMovement($data2);            

                        if ($movement->getStatus() == Movement::STATUS_COMMISSION){
                            $comiss = $this->entityManager->getRepository(Comiss::class)
                                    ->findOneByDocKey($base['baseKey']);
                            if ($comiss){
                                $data = [
                                    'doc_key' => $pt->getLogKey(),
                                    'doc_type' => Movement::DOC_PT,
                                    'doc_id' => $pt->getId(),
                                    'doc_row_key' => $ptGood->getDocRowKey(),
                                    'doc_row_no' => $ptGood->getRowNo(),
                                    'date_oper' => $pt->getDocDate(),
                                    'status' => $movement->getStatus(),
                                    'quantity' => $quantity,
                                    'amount' => $baseAmount,
                                    'good_id' => $ptGood->getGood()->getId(),
                                    'office_id' => $pt->getOffice2()->getId(),
                                    'company_id' => $pt->getCompany2()->getId(),
                                    'contact_id' => $comiss->getContact()->getId(),
                                    'doc_stamp' => $docStamp,
                                ];
                                $this->entityManager->getRepository(Comiss::class)
                                        ->insertComiss($data);
                            }    
                        }    
                        $write -= $quantity;
                        if ($write <= 0){
                            break;
                        }
                    }                        
                }    
                
                if ($write == 0){
                    $take = PtGood::TAKE_OK;
                } elseif ($pt->getDocDate() < date('Y-m-d', strtotime('+1 day'))) {
                    $ptTake = Pt::STATUS_TAKE_NO;
                }
                
                $this->entityManager->getConnection()
                        ->update('pt_good', ['take' => $take], ['id' => $ptGood->getId()]);
            }    
            
            $this->entityManager->getRepository(Movement::class)
                    ->updateGoodBalance($ptGood->getGood()->getId());
        }

        $this->entityManager->getConnection()
                ->update('pt', ['status_account' => $ptTake], ['id' => $pt->getId()]);        
        
        return;
    }    
    
    /**
     * Обновить взаиморасчеты розничного заказа
     * 
     * @param Pt $pt
     * @param float $docStamp
     */
    public function updatePtRetails($pt, $docStamp)
    {
        $this->entityManager->getRepository(Retail::class)
                ->removeOrderRetails($pt->getLogKey());
        if ($pt->getCompany()->getInn() != $pt->getCompany2()->getInn()){
            $contract = $this->orderManager->findDefaultContract($pt->getOffice(), $pt->getCompany2(), $pt->getDocDate(), $pt->getDocNo());
            $data = [
                'doc_key' => $pt->getLogKey(),
                'doc_type' => Movement::DOC_PT,
                'doc_id' => $pt->getId(),
                'date_oper' => $pt->getDocDate(),
                'status' => Retail::getStatusFromPt($pt),
                'revise' => Retail::REVISE_NOT,
                'amount' => $pt->getAmount(),
                'contact_id' => $pt->getOffice2()->getLegalContact()->getId(),
                'office_id' => $pt->getOffice()->getId(),
                'company_id' => $pt->getCompany()->getId(),
                'legal_id' => $pt->getCompany2()->getId(),
                'contract_id' => $contract->getId(),
                'doc_stamp' => $docStamp,
            ];

            $this->entityManager->getRepository(Retail::class)
                    ->insertRetail($data);
        }    
        
        return;
    }    
    
    /**
     * Обновить взаиморасчеты заказа
     * 
     * @param Pt $pt
     * @param float $docStamp
     */
    public function updatePtMutuals($pt, $docStamp)
    {
        $this->entityManager->getRepository(Mutual::class)
                ->removeDocMutuals($pt->getLogKey());                        
        if ($pt->getCompany()->getInn() != $pt->getCompany2()->getInn()){        
            $contract = $this->orderManager->findDefaultContract($pt->getOffice(), $pt->getCompany2(), $pt->getDocDate(), $pt->getDocNo());
            $data = [
                'doc_key' => $pt->getLogKey(),
                'doc_type' => Movement::DOC_PT,
                'doc_id' => $pt->getId(),
                'date_oper' => $pt->getDocDate(),
                'status' => Mutual::getStatusFromPt($pt),
                'revise' => Mutual::REVISE_NOT,
                'amount' => $pt->getAmount(),
                'legal_id' => $pt->getCompany2()->getId(),
                'contract_id' => $contract->getId(),
                'office_id' => $pt->getOffice()->getId(),
                'company_id' => $pt->getCompany()->getId(),
                'doc_stamp' => $docStamp,
            ];

            $this->entityManager->getRepository(Mutual::class)
                    ->insertMutual($data);
        }
        return;
    }        
    
    /**
     * Перепроведение ПТ
     * @param Pt $pt
     */
    public function repostPt($pt)
    {
        if ($pt->getDocDate() > $this->getAllowDate()){
            $docStamp = $this->entityManager->getRepository(Register::class)
                    ->ptRegister($pt);        
            $this->updatePtMovement($pt, $docStamp);            
        } else {
            $register = $this->entityManager->getRepository(Register::class)
                    ->findOneBy(['docKey' => $pt->getLogKey()]);
            $docStamp = $register->getDocStamp();
        }
        $this->updatePtRetails($pt, $docStamp);
        $this->updatePtMutuals($pt, $docStamp);
        $this->foldManager->ptFold($pt, $docStamp);
        
        return;
    }

    /**
     * Перепроведение всех ПТ
     */
    public function repostAllPt()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        
        $ptQuery = $this->entityManager->getRepository(Pt::class)
                ->queryAllPt();
        $iterable = $ptQuery->iterate();
        
        foreach ($iterable as $row){
            foreach($row as $pt){ 
                $this->repostPt($pt);
                $this->entityManager->detach($pt);
                unset($pt);
            }    
        }    
        
        return;
    }


    /**
     * Adds a new Pt.
     * @param array $data
     * @return integer
     */
    public function addPt($data)
    {
        if ($data['doc_date'] > $this->allowDate){
            $pt = new Pt();        
            $pt->setAplId($data['apl_id']);
            $pt->setDocDate($data['doc_date']);
            $pt->setComment(empty($data['comment']) ? '':$data['comment']);
            $pt->setStatusEx($data['status_ex']);
            $pt->setStatus($data['status']);
            $pt->setStatusDoc(Pt::STATUS_DOC_NOT_RECD);
            $pt->setOffice($data['office']);
            $pt->setCompany($data['company']);
            $pt->setOffice2($data['office2']);
            $pt->setCompany2($data['company2']);
            $pt->setAmount(0);
            $pt->setDateCreated(date('Y-m-d H:i:s'));
            if (!empty($data['doc_no'])){
                $pt->setDocNo($data['doc_no']);
            }
            $pt->setStatusAccount(Pt::STATUS_ACCOUNT_NO);

            $this->entityManager->persist($pt);        
            $this->entityManager->flush($pt);

            return $pt;        
        }
        
        return;
    }
    
    /**
     * Update pt.
     * @param Pt $pt
     * @param array $data
     * @return integer
     */
    public function updatePt($pt, $data)            
    {
        if ($data['doc_date'] > $this->allowDate){
            $pt->setAplId($data['apl_id']);
            $pt->setDocDate($data['doc_date']);
            $pt->setComment(empty($data['comment']) ? '':$data['comment']);
            $pt->setStatusEx($data['status_ex']);
            $pt->setStatus($data['status']);
            $pt->setOffice($data['office']);
            $pt->setCompany($data['company']);
            $pt->setOffice2($data['office2']);
            $pt->setCompany2($data['company2']);
            if (!empty($data['doc_no'])){
                $pt->setDocNo($data['doc_no']);
            }
            $pt->setStatusAccount(Pt::STATUS_ACCOUNT_NO);

            $this->entityManager->persist($pt);
            $this->entityManager->flush($pt);

            return $pt;
        }    
    }
    
    /**
     * Adds a new pt-good.
     * @param integer $ptId
     * @param array $data
     * @param integer $rowNo
     * 
     * @return integer
     */
    public function addPtGood($ptId, $data, $rowNo)
    {
//        var_dump($data); exit;
        $ptGood = [
            'pt_id' => $ptId,
            'status' => (isset($data['status'])) ? $data['status']:PtGood::STATUS_ACTIVE,
            'status_doc' => (isset($data['statusDoc'])) ? $data['statusDoc']:PtGood::STATUS_DOC_RECD,
            'quantity' => $data['quantity'],
            'amount' => $data['amount'],
            'good_id' => $data['good_id'],
            'comment' => (isset($data['comment'])) ? $data['comment']:'',
            'base_key' => (isset($data['baseKey'])) ? $data['baseKey']:null,
//            'info' => $data['info'],
            'row_no' => $rowNo,
            'take' => PtGood::TAKE_NO,
        ];
        //var_dump($ptGood); exit;
        
        $connection = $this->entityManager->getConnection(); 
        $connection->insert('pt_good', $ptGood);
        return;
    }
    
    /**
     * Update pt_good.
     * @param PtGood $ptGood
     * @param array $data
     * @return integer
     */
    public function updatePtGood($ptGood, $data)            
    {
        
        $connection = $this->entityManager->getConnection(); 
        $connection->update('pt_good', $data, ['id' => $ptGood->getId()]);
        return;
    }
    
    /**
     * Обновить сумму ПТ
     * @param Pt $pt
     * @param bool $nullUser
     */
    public function updatePtAmount($pt, $nullUser = false)
    {
        $preLog = $this->entityManager->getRepository(Log::class)
                ->findOneByLogKey($pt->getLogKey());
        if (!$preLog){
            $this->logManager->infoPt($pt, Log::STATUS_INFO);            
        }
        
        $ptAmountTotal = $this->entityManager->getRepository(Pt::class)
                ->ptAmountTotal($pt);
        $pt->setAmount($ptAmountTotal);
        $this->entityManager->persist($pt);
        $this->entityManager->flush($pt);
        
        $this->entityManager->refresh($pt);
        $this->repostPt($pt);
        $this->logManager->infoPt($pt, Log::STATUS_UPDATE, $nullUser);
        return;
    }
    
    /**
     * Удаление строк ПТ
     * @param Pt $pt
     */
    public function removePtGood($pt)
    {
        $this->entityManager->getConnection()
                ->delete('pt_good', ['pt_id' => $pt->getId()]);        
        return;
    }
    
    /**
     * Обновление строк ПТ
     * 
     * @param Pt $pt
     * @param array $data
     */
    public function updatePtGoods($pt, $data)
    {
        $this->removePtGood($pt);
        
        $rowNo = 1;
        if ($data){
            foreach ($data as $row){
                if (isset($row['baseKey'])){
                    if ($row['baseKey'] == PtGood::BASE_KEY_AUTO){
                        unset($row['baseKey']);
                    }
                }
                $this->addPtGood($pt->getId(), $row, $rowNo);
                $rowNo++;
            }
        }    
        
        $this->updatePtAmount($pt);
        return;
    }   
    
    
    /**
     * Удаление ПТ
     * 
     * @param Pt $pt
     */
    public function removePt($pt)
    {
        if ($pt->getDocDate() > $this->allowDate){
            $this->logManager->infoPt($pt, Log::STATUS_DELETE);
            $this->entityManager->getRepository(Movement::class)
                    ->removeDocMovements($pt->getLogKey());
            $this->entityManager->getRepository(Comiss::class)
                    ->removeDocComiss($pt->getLogKey());
            $this->removePtGood($pt);

            $this->entityManager->getConnection()->delete('pt', ['id' => $pt->getId()]);
        }            
        return;
    }
    
    
    /**
     * Update pt status.
     * @param Pt $pt
     * @param integer $status
     * @return integer
     */
    public function updatePtStatus($pt, $status)            
    {

        if ($pt->getDocDate() > $this->allowDate || $pt->getStatus() != Pt::STATUS_ACTIVE){
            $pt->setStatus($status);
            $pt->setStatusEx(Pt::STATUS_EX_NEW);
            $pt->setStatusAccount(Pt::STATUS_ACCOUNT_NO);
            
            if ($pt->getDocDate() < $this->getAllowDate() && $status == Pt::STATUS_RETIRED){
                $pt->setDocDate(date('Y-m-d', strtotime($this->getAllowDate().' + 1 day')));
            }

            $this->entityManager->persist($pt);
            $this->entityManager->flush();

            $this->repostPt($pt);
            $this->logManager->infoPt($pt, Log::STATUS_UPDATE);
        }    
        
        return;
    }
    
    /**
     * Удалить автоперемещения за дату
     * @param Office $office
     * @param Office $office2
     * @param date $ptDate
     */
    public function deleteAutoPt($office, $office2, $ptDate)
    {
        $pts = $this->entityManager->getRepository(Pt::class)
                ->findBy(['office' => $office, 'office2' => $office2, 'docDate' => $ptDate, 'docNo' => $this->autoPtDocNo]);
        foreach ($pts as $pt){
            
            $this->removePtGood($pt);                    
            $this->updatePtAmount($pt);
            
            $pt->setStatus(Pt::STATUS_RETIRED);
            $pt->setStatusEx(Pt::STATUS_EX_NEW);
            $pt->setStatusAccount(Pt::STATUS_ACCOUNT_NO);
            $this->entityManager->persist($pt);
            $this->entityManager->flush($pt);
        }
        return;
    }
    
    /**
     * Провести автоперемещения за дату
     * @param date $ptDate
     */
    public function updateAutoPt($ptDate)
    {
        $pts = $this->entityManager->getRepository(Pt::class)
                ->findBy(['docDate' => $ptDate, 'docNo' => $this->autoPtDocNo]);
        foreach ($pts as $pt){            
            $this->updatePtAmount($pt, true);            
        }
        return;
    }
    
    /**
     * Проверка автоперемещений
     * @param Pt $pt
     */
    public function checkAutoPt($pt)
    {
        if ($pt->getStatusAccount() == Pt::STATUS_TAKE_NO && $pt->getStatus() == Pt::STATUS_ACTIVE){
            
            $this->repostPt($pt);
            
            $ptGoods = $this->entityManager->getRepository(PtGood::class)
                    ->findBy(['pt' => $pt->getId(), 'take' => PtGood::TAKE_NO]);
            foreach ($ptGoods as $ptGood){
                $movementCount = $this->entityManager->getRepository(Pt::class)
                        ->movementQuantityCount($ptGood);
                if (!$movementCount){
                    $this->entityManager->remove($ptGood);
                } else {
                    $ptGood->setQuantity($movementCount);
                    $ptGood->setAmount($movementCount*$ptGood->getGood()->getFormatMeanPrice());
                    $this->entityManager->persist($ptGood);
                }                
            }
            
            $pt->setStatusEx(Pt::STATUS_EX_NEW);
            $this->entityManager->persist($pt);
            $this->entityManager->flush();

            $ptGoodsCount = $this->entityManager->getRepository(PtGood::class)
                    ->findBy(['pt' => $pt->getId()]);
            if (!$ptGoodsCount){
                $pt->setStatus(Pt::STATUS_RETIRED);
                $this->entityManager->persist($pt);
                $this->entityManager->flush();
            }

            $this->updatePtAmount($pt);                                    
            $this->entityManager->refresh($pt);
        }
        
        return;
    }
    
    /**
     * Проверка перемещений между офисами
     * @param PtSheduler $ptSheduler
     */
    public function ptCheckGenerator($ptSheduler)
    {
        $checkTime = $ptSheduler->getGeneratorCheckTime();
        $nowTime = date('H:i');

        if ($nowTime < $checkTime){
            return;
        }

        $office = $ptSheduler->getOffice();
        $office2 = $ptSheduler->getOffice2();
        $ptDate = date('Y-m-d');
        
        $pts = $this->entityManager->getRepository(Pt::class)
                ->findBy(['office' => $office, 'office2' => $office2, 
                    'docDate' => $ptDate, 'docNo' => $this->autoPtDocNo,
                    'statusAccount' => Pt::STATUS_TAKE_NO]);
        
        foreach ($pts as $pt){
            $this->checkAutoPt($pt);
        }
        return;        
    }
        
    /**
     * Генерация перемещений между офисами
     * @param PtSheduler $ptSheduler
     */
    public function ptGenerator($ptSheduler)
    {
        $endTime = $ptSheduler->getGeneratorTime();
        $nowTime = date('H:i');

        if ($nowTime > $endTime){
            return;
        }

        $office = $ptSheduler->getOffice();
        $office2 = $ptSheduler->getOffice2();
        
        $ptDate = date('Y-m-d');
        $maxDateOper = $this->entityManager->getRepository(Order::class)
                ->findMaxDateOper($office2);
        
//        var_dump($maxDateOper);
        while ($ptDate <= $maxDateOper){
            
            $this->deleteAutoPt($office, $office2, $ptDate);

            $soDate = $ptDate;
            if ($ptSheduler->getGeneratorDay() == PtSheduler::GENERATOR_DAY_TOMORROW){
                $soDate = date('Y-m-d', strtotime($ptDate.' +1 day'));
            }

            $supplierOrders = $this->entityManager->getRepository(SupplierOrder::class)
                    ->findForPt($office, $office2, $soDate);

            $i = 1;
            foreach ($supplierOrders as $supplierOrder){
                $company = $this->entityManager->getRepository(Office::class)
                        ->findDefaultCompany($office);
                $company2 = $this->entityManager->getRepository(Office::class)
                        ->findDefaultCompany($office2);

                $pt = $this->entityManager->getRepository(Pt::class)
                        ->findOneBy(['office' => $office->getId(), 'office2' => $office2->getId(), 
                            'docDate' => $ptDate, 'docNo' => $this->autoPtDocNo]);
                $upd = [
                    'apl_id' => 0,
                    'doc_date' => $ptDate,
                    'comment' => 'Автоперемещение',
                    'status_ex' => Pt::STATUS_EX_NEW,
                    'status' => Pt::STATUS_ACTIVE,
                    'office' => $office,
                    'company' => $company,
                    'office2' => $office2,
                    'company2' => $company2,
                    'doc_no' => $this->autoPtDocNo,
                ];

                if (!$pt){                    
                    $pt = $this->addPt($upd);
                } else {
                    $pt->setStatus(Pt::STATUS_ACTIVE);
                    $this->entityManager->persist($pt);
                    $this->entityManager->flush($pt);
                }

                $good = $this->entityManager->getRepository(Goods::class)
                        ->find($supplierOrder['goodId']);

                $ptGood = [
                    'quantity' => $supplierOrder['quantity'],
                    'amount' => $good->getFormatMeanPrice()*$supplierOrder['quantity'],
                    'good_id' => $supplierOrder['goodId'],
                    'comment' => $supplierOrder['orderAplId'].' '.$supplierOrder['supplierName'],
                ];

                $this->addPtGood($pt->getId(), $ptGood, $i);
                $i++;
            }

            $this->updateAutoPt($ptDate);
            
            $ptDate = date('Y-m-d', strtotime($ptDate.' +1 day'));
        }    
        
        return;
    }
        
    /**
     * Генерация всех перемещений
     */
    public function ptGenerators()
    {
        $shedulers = $this->entityManager->getRepository(PtSheduler::class)
                ->findBy(['status' => PtSheduler::STATUS_ACTIVE]);
        foreach ($shedulers as $ptSheduler){
            $this->ptGenerator($ptSheduler);
            $this->ptCheckGenerator($ptSheduler);
        }
        
        return;
    }
    
    /**
     * Добавить расписание генерации перемещения
     * @param array $data
     * @return PtSheduler
     */
    public function addPtSheduler($data)
    {
        $ptSheduler = new PtSheduler();
        $ptSheduler->setGeneratorDay($data['generatorDay']);
        $ptSheduler->setGeneratorTime($data['generatorTime']);
        $ptSheduler->setOffice($data['office']);
        $ptSheduler->setOffice2($data['office2']);
        $ptSheduler->setStatus($data['status']);
        
        $this->entityManager->persist($ptSheduler);
        $this->entityManager->flush($ptSheduler);
        
        return $ptSheduler;
    }

    /**
     * Обновить расписание генерации перемещения
     * @param PtSheduler $ptSheduler
     * @param array $data
     * @return PtSheduler
     */
    public function updatePtSheduler($ptSheduler, $data)
    {
        $ptSheduler->setGeneratorDay($data['generatorDay']);
        $ptSheduler->setGeneratorTime($data['generatorTime']);
        $ptSheduler->setOffice($data['office']);
        $ptSheduler->setOffice2($data['office2']);
        $ptSheduler->setStatus($data['status']);
        
        $this->entityManager->persist($ptSheduler);
        $this->entityManager->flush($ptSheduler);
        
        return $ptSheduler;
    }
    
    /**
     * Удалить расписание генерации перемещения
     * @param PtSheduler $ptSheduler
     */
    public function removePtSheduler($ptSheduler)
    {
        $this->entityManager->remove($ptSheduler);
        $this->entityManager->flush();
        
        return;
    }    
    
    /**
     * Заменить товар
     * @param Goods $oldGood
     * @param Goods $newGood
     */
    public function changeGood($oldGood, $newGood)
    {
        $rows = $this->entityManager->getRepository(PtGood::class)
                ->findBy(['good' => $oldGood->getId()]);
        foreach ($rows as $row){
            $row->setGood($newGood);
            $this->entityManager->persist($row);
            $this->entityManager->flush();
            $this->updatePtMovement($row->getPt());
        }
        
        return;
    }    
}

