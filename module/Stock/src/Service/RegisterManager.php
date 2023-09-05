<?php
namespace Stock\Service;

use Stock\Entity\Movement;
use Stock\Entity\Register;
use Application\Entity\Order;
use Stock\Entity\RegisterVariable;
use Stock\Entity\Ot;
use Stock\Entity\Pt;
use Stock\Entity\Ptu;
use Stock\Entity\St;
use Stock\Entity\Vt;
use Stock\Entity\Vtp;
use Stock\Entity\Revise;
use Application\Entity\Bid;
use Stock\Entity\PtGood;
use Stock\Entity\StGood;
use Stock\Entity\VtGood;
use Stock\Entity\VtpGood;
use Company\Entity\Office;
use Application\Entity\Goods;
use Application\Entity\Article;
use Application\Entity\Producer;
use Application\Entity\UnknownProducer;
use Application\Entity\Oem;
use Cash\Entity\CashDoc;
use ApiMarketPlace\Entity\MarketSaleReport;
use ApiMarketPlace\Entity\MarketSaleReportItem;

/**
 * This service register.
 */
class RegisterManager
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
     * Ot manager
     * @var \Stock\Service\OtManager
     */
    private $otManager;
        
    /**
     * Pt manager
     * @var \Stock\Service\PtManager
     */
    private $ptManager;

    /**
     * Ptu manager
     * @var \Stock\Service\PtuManager
     */
    private $ptuManager;

    /**
     * St manager
     * @var \Stock\Service\StManager
     */
    private $stManager;

    /**
     * Vt manager
     * @var \Stock\Service\VtManager
     */
    private $vtManager;

    /**
     * Vtp manager
     * @var \Stock\Service\VtpManager
     */
    private $vtpManager;

    /**
     * Revise manager
     * @var \Stock\Service\ReviseManager
     */
    private $reviseManager;

    /**
     * Order manager
     * @var \Application\Service\OrderManager
     */
    private $orderManager;
    
    /**
     * Cash manager
     * @var \Cash\Service\CashManager
     */
    private $cashManager;
    
    /**
     * Report manager
     * @var \ApiMarketPlace\Service\ReportManager
     */
    private $reportManager;
    
    private $meDate = '2016-10-30';

    /**
     * Constructs the service.
     */
    public function __construct($entityManager, $logManager, $otManager, $ptManager,
            $ptuManager, $stManager, $vtManager, $vtpManager, $orderManager, 
            $cashMananger, $reviseManager, $reportManager) 
    {
        $this->entityManager = $entityManager;
        $this->logManager = $logManager;
        $this->otManager = $otManager;
        $this->ptManager = $ptManager;
        $this->ptuManager = $ptuManager;
        $this->stManager = $stManager;
        $this->vtManager = $vtManager;
        $this->vtpManager = $vtpManager;
        $this->orderManager = $orderManager;
        $this->cashManager = $cashMananger;
        $this->reviseManager = $reviseManager;
        $this->reportManager = $reportManager;
    }
    
    public function currentUser()
    {
        return $this->logManager->currentUser();
    }
    
    /**
     * Получить дату запрета
     * @return date
     */
    public function getAllowDate() 
    {
        return $this->vtpManager->getAllowDate();
    }
    
    /**
     * Обновить метку последовательности
     * 
     * @param Register $register
     * @return type
     */
    private function updateVar($register)
    {
        $var = $this->entityManager->getRepository(RegisterVariable::class)
                ->findOneBy([]);
        
        $var->setDateVar($register->getDateOper());
        $var->setVarId($register->getDocId());
        $var->setVarType($register->getDocType());
        $var->setVarStamp($register->getDocStamp());
        
        $this->entityManager->persist($var);
        $this->entityManager->flush($var);
        
        return;
    }
    
    /**
     * Добавить оприходование
     * @param array $data
     * @return Ot
     */
    private function oldOt($data)
    {
        $otData = [
            'apl_id' => 0,
            'doc_date' => date('Y-m-d', strtotime($data['docDate'])),
            'comment' => "Дооприходование для заказа {$data['docId']} раньше {$this->meDate}",
            'status_ex' => Ot::STATUS_EX_APL, 
            'status' => Ot::STATUS_INVENTORY,
            'office' => $data['office'],
            'company' => $data['company'],
        ];

        $ot = $this->otManager->addOt($otData);

        $i = 1;
        foreach ($data['rows'] as $row){
            $otgGoodData = [
                'quantity' => $row['quantity'],
                'amount' => $row['amount'],
                'good_id' => $row['goodId'],
            ];

            $this->otManager->addOtGood($ot->getId(), $otgGoodData, $i);
            $i++;
        }

        $this->otManager->updateOtAmount($ot);
        
        return true;
    }
    
    /**
     * Найти и поправить ПТУ с неверной датой прихода
     * @param Goods $good
     * @param date $docDate
     * @param Office $office
     * @param string $docKey
     * @param Object $doc
     * @return null
     */
    private function findNearPtu($good, $docDate, $office, $docKey, $doc)
    {
        $ptu = $this->entityManager->getRepository(Register::class)
                ->findNearPtu($good, $docDate, $office);
        if ($ptu){
            if ($docDate < '2020-01-01'){
                $oldDate = $ptu->getDocDate();
    //            var_dump($good->getId()); exit;
                $ptu->setDocDate($docDate);
                $ptu->setComment('#Поправка даты, старая дата: '.$oldDate.' '.$docKey);
                $this->entityManager->persist($ptu);
                $this->entityManager->flush($ptu);
                $this->ptuManager->repostPtu($ptu);

                return true;
            } else {
                $oldDate = $doc->getDocDate();
                $doc->setDocDate($ptu->getDocDate());
                $doc->setComment('#Поправка даты, старая дата: '.$oldDate.' '.$docKey);
                $this->entityManager->persist($doc);
                $this->entityManager->flush($doc);
                $this->entityManager->refresh($doc);
                return true;
            }
        }
        
        $pt = $this->entityManager->getRepository(Register::class)
                ->findNearPt($good, $docDate, $office);
        if ($pt){
            if ($docDate < '2022-01-01'){
                
            } else {
                $oldDate = $doc->getDocDate();
                $doc->setDocDate($pt->getDocDate());
                $doc->setComment('#Поправка даты, старая дата: '.$oldDate.' '.$docKey);
                $this->entityManager->persist($doc);
                $this->entityManager->flush($doc);
                $this->entityManager->refresh($doc);
                return true;
            }
        }
        return false;
    }
    
    /**
     * Найти и поправить ПТУ с одинаковым артикулом
     * @param Goods $good
     * @param date $docDate
     * @param Office $office
     * @return null
     */
    private function correctCodePtu($good, $docDate, $office)
    {
        $ptu = $this->entityManager->getRepository(Register::class)
                ->correctCodePtu($good, $docDate, $office, true);
        if ($ptu){
            //$ptu->setDocDate($docDate);
            $ptu->setComment('#Поправка товара, дата не менялась');
            $this->entityManager->persist($ptu);
            $this->entityManager->flush($ptu);
            $this->ptuManager->repostPtu($ptu);
            
            return true;
        }

        if ($docDate < '2022-01-01'){
            $ptu = $this->entityManager->getRepository(Register::class)
                    ->correctCodePtu($good, $docDate, $office, false);
            if ($ptu){
                $oldDate = $ptu->getDocDate();
                $ptu->setDocDate($docDate);
                $ptu->setComment('#Поправка товара и даты, старая дата: '.$oldDate);
                $this->entityManager->persist($ptu);
                $this->entityManager->flush($ptu);
                $this->ptuManager->repostPtu($ptu);

                return true;
            }
        }    
        return false;
    }
    
    /**
     * Поправить партию в возврате поставщику
     * @param Vtp $vtp
     * @param Goods $good
     * @param float $docStamp
     * @return null
     */
    private function correctVtpBase($vtp, $good, $docStamp)
    {
        $bases = $this->entityManager->getRepository(Movement::class)
                ->findBases($good->getId(), $docStamp, $vtp->getPtu()->getOffice()->getId());
        
        foreach ($bases as $base){
            $movement = $this->entityManager->getRepository(Movement::class)
                            ->findOneByBaseKey($base['baseKey']);
//            var_dump($base['baseKey']);
            if ($movement){
                $ptu = $this->entityManager->getRepository(Ptu::class)
                        ->find($movement->getBaseId());
                if ($ptu){
                    if ($ptu->getLegal()->getId() == $vtp->getPtu()->getLegal()->getId()){
                        $oldDocNum = $vtp->getPtu()->getId();
                        $vtp->setPtu($ptu);
                        $vtp->setComment('#Поправка партии, старое ПТУ id:'.$oldDocNum);
                        $this->entityManager->persist($vtp);
                        $this->entityManager->flush($vtp);
                        $this->vtpManager->repostVtp($vtp);

                        return true;
                    }    
                }    
            }    
        }                
        return false;
    }
    
    /**
     * Актализировать документ
     * @param Register $register
     * @return null
     */
    private function docActualize($register)
    {
        $flag = false;
        switch ($register->getDocType()){
            case Movement::DOC_ORDER:
                $order = $this->entityManager->getRepository(Order::class)
                    ->find($register->getDocId());
                if ($order){
                    $flag = true;
                    $this->orderManager->repostOrder($order);
                    if ($order->getStatus() == Order::STATUS_SHIPPED){
                        $takeNo = $this->entityManager->getRepository(Bid::class)
                                ->count(['order' => $order->getId(), 'take' => Bid::TAKE_NO]);
                        $flag = $takeNo == 0;
                        if (!$flag){
                            $bids = $this->entityManager->getRepository(Bid::class)
                                    ->findBy(['order' => $order->getId(), 'take' => Bid::TAKE_NO]);
                            $data = [
                                'docDate' => $order->getDocDate(),
                                'docId' => $order->getId(),
                                'office' => $order->getOffice(),
                                'company' => $order->getCompany(),
                            ];
                            $rows = [];
                            foreach ($bids as $bid){
                                if ($this->findNearPtu($bid->getGood(), $order->getDateOper(), $order->getOffice(), $order->getLogKey(), $order)){
                                    return true;
                                } 
                                if ($this->correctCodePtu($bid->getGood(), $order->getDateOper(), $order->getOffice())){
                                    return true;
                                } 
                                $rows[] = [
                                    'goodId' => $bid->getGood()->getId(),
                                    'quantity' => $bid->getNum(),
                                    'amount' => $bid->getNum()*$bid->getPrice(),
                                ];
                            }
                            $data['rows'] = $rows;
                            if ($order->getDateOper() <= $this->meDate){
                                //return $this->oldOt($data);
                            }    
                        }
                    }   
                }
                break;
            case Movement::DOC_OT:
                $ot = $this->entityManager->getRepository(Ot::class)
                    ->find($register->getDocId());
                if ($ot){
                    $this->otManager->repostOt($ot);
                    $flag = true;
                }
                break;
            case Movement::DOC_PT:
                $pt = $this->entityManager->getRepository(Pt::class)
                    ->find($register->getDocId());
                if ($pt){
                    $flag = true;
                    $this->ptManager->repostPt($pt);
                    if ($pt->getStatus() == Pt::STATUS_ACTIVE){
                        $takeNo = $this->entityManager->getRepository(PtGood::class)
                                ->count(['pt' => $pt->getId(), 'take' => PtGood::TAKE_NO]);
                        $flag = $takeNo == 0;
                        if (!$flag){
                            $ptGoods = $this->entityManager->getRepository(PtGood::class)
                                    ->findBy(['pt' => $pt->getId(), 'take' => PtGood::TAKE_NO]);
                            foreach ($ptGoods as $ptGood){
//                                if ($this->findNearPtu($ptGood->getGood(), $pt->getDocDate(), $pt->getOffice(), $pt->getLogKey(), $pt)){
//                                    return true;
//                                } 
                                if ($this->correctCodePtu($ptGood->getGood(), $pt->getDocDate(), $pt->getOffice())){
                                    return true;
                                } 
                            }
                        }
                    }    
                }
                break;
            case Movement::DOC_PTU:
                $ptu = $this->entityManager->getRepository(Ptu::class)
                    ->find($register->getDocId());
                var_dump($ptu->getDocNo()); exit;
                if ($ptu){
                    $this->ptuManager->repostPtu($ptu);
                    $flag = true;
                }
                break;
            case Movement::DOC_ST:
                $st = $this->entityManager->getRepository(St::class)
                    ->find($register->getDocId());
                if ($st){
                    $flag = true;
                    $this->stManager->repostSt($st);
                    if ($st->getStatus() == St::STATUS_ACTIVE){
                        $takeNo = $this->entityManager->getRepository(StGood::class)
                                ->count(['st' => $st->getId(), 'take' => StGood::TAKE_NO]);
                        $flag = $takeNo == 0;
                        if (!$flag){
                            $stGoods = $this->entityManager->getRepository(StGood::class)
                                    ->findBy(['st' => $st->getId(), 'take' => StGood::TAKE_NO]);
                            $stCount = $st->getStGoods()->count();
                            if (count($stGoods) ==  $stCount && $stCount == 1){
                                foreach ($stGoods as $stGood){
                                    if ($this->findNearPtu($stGood->getGood(), $st->getDocDate(), $st->getOffice(), $st->getLogKey(), $st)){
                                        return true;
                                    } 
                                    if ($this->correctCodePtu($stGood->getGood(), $st->getDocDate(), $st->getOffice())){
                                        return true;                                    
                                    } 
                                }
                            }                           
                        }
                    }    
                }
                break;
            case Movement::DOC_VT:
                $vt = $this->entityManager->getRepository(Vt::class)
                    ->find($register->getDocId());
                if ($vt){
                    $flag = true;
                    $this->vtManager->repostVt($vt);
                    if ($vt->getStatus() == Vt::STATUS_ACTIVE){
                        $takeNo = $this->entityManager->getRepository(VtGood::class)
                                ->count(['vt' => $vt->getId(), 'take' => VtGood::TAKE_NO]);
                        $flag = $takeNo == 0;
                    }   
                }
                break;
            case Movement::DOC_VTP:
                $vtp = $this->entityManager->getRepository(Vtp::class)
                    ->find($register->getDocId());
                if ($vtp){
                    $flag = true;
                    $this->vtpManager->repostVtp($vtp);
                    if ($vtp->getStatus() == Vtp::STATUS_ACTIVE && $vtp->getStatusDoc() == Vtp::STATUS_DOC_NOT_RECD){
                        $takeNo = $this->entityManager->getRepository(VtpGood::class)
                                ->count(['vtp' => $vtp->getId(), 'take' => VtpGood::TAKE_NO]);
                        $flag = $takeNo == 0;
//                        if (!$flag){                            
//                            $vtpGoods = $this->entityManager->getRepository(VtpGood::class)
//                                    ->findBy(['vtp' => $vtp->getId(), 'take' => VtpGood::TAKE_NO]);
//                            $vtpCount = $vtp->getVtpGoods()->count();
//                            if (count($vtpGoods) ==  $vtpCount && $vtpCount == 1){
//                                foreach ($vtpGoods as $vtpGood){
//                                    if ($this->correctVtpBase($vtp, $vtpGood->getGood(), $register->getDocStamp())){
//                                        return true;
//                                    } 
//                                }
//                            }    
//                        }    
                    }   
                }
                break;
            case Movement::DOC_CASH:
                $cashDoc = $this->entityManager->getRepository(CashDoc::class)
                    ->find($register->getDocId());
                if ($cashDoc){
                    $this->cashManager->updateCashTransaction($cashDoc);
                    $flag = true;
                }
                break;                
            case Movement::DOC_REVISE:
                $revise = $this->entityManager->getRepository(Revise::class)
                    ->find($register->getDocId());
                if ($revise){
                    $this->reviseManager->repostRevise($revise);
                    $flag = true;
                }
                break;                
            case Movement::DOC_MSR:
                $marketSaleReport = $this->entityManager->getRepository(MarketSaleReport::class)
                    ->find($register->getDocId());
                if ($marketSaleReport){
                    $flag = true;
                    $this->reportManager->repostMarketSaleReport($marketSaleReport);
                    if ($marketSaleReport->getStatus() == MarketSaleReport::STATUS_ACTIVE){
                        $takeNo = $this->entityManager->getRepository(MarketSaleReportItem::class)
                                ->count(['marketSaleReport' => $marketSaleReport->getId(), 'take' => MarketSaleReportItem::TAKE_NO]);
                        $flag = $takeNo == 0;
                    }   
                }
                break;
            default: $flag = false;    
        }
        
        if ($flag){
            $this->updateVar($register);
        }    
        
        return $flag;
    }
    
    /**
     *  Восстановление последовательности
     * @param integer $workTime
     */
    public function actualize($workTime = 840)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(900);
        $startTime = time();
        $stamp = null;
        
        while (true){
            $register = $this->entityManager->getRepository(Register::class)
                    ->findForActualize($stamp);
            
            $stamp = $register->getDocStamp();
            
            $allowDate = $this->getAllowDate();
            if ($allowDate > $register->getDateOper()){
                throw new \Exception('Дата документа меньше разрешенной!');                
            }
            
            if ($register){
                if ($this->docActualize($register)){
                    if ($workTime < 840){
                        var_dump($register->getDocStamp());                        
                    }
                    usleep(100);                    
                } else {
                    throw new \Exception('Документ не проведен!');
                }
            } else{
                break;
            }
            
            if (time() > $startTime + $workTime){
                break;
            }
            
            $this->entityManager->detach($register);
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
        $this->otManager->changeGood($oldGood, $newGood);
        $this->ptuManager->changeGood($oldGood, $newGood);
        $this->ptManager->changeGood($oldGood, $newGood);
        $this->orderManager->changeGood($oldGood, $newGood);
        $this->vtManager->changeGood($oldGood, $newGood);
        $this->vtpManager->changeGood($oldGood, $newGood);
        $this->stManager->changeGood($oldGood, $newGood);
    }
    
    /**
     * Заменить производителя
     * 
     * @param Goods $good
     * @param Producer $newProducer
     */
    public function changeProducer($good, $newProducer)
    {
        $newGood = $this->entityManager->getRepository(Goods::class)
                ->findOneBy(['code' => $good->getCode(), 'producer' => $newProducer->getId()]);
        if (!$newGood){
            $good->setProducer($newProducer);
            $this->entityManager->persist($good);
            $this->entityManager->flush();
            return;
        }
        
        $articles = $this->entityManager->getRepository(Article::class)
                ->findBy(['good' => $good->getId()]);
        foreach ($articles as $article){
            $article->setGood($newGood);
            $this->entityManager->persist($article);
            $this->entityManager->flush();
        }
        
        $oes = $this->entityManager->getRepository(Oem::class)
                ->findBy(['good' => $good->getId()]);
        foreach ($oes as $oe){
            $oem = $this->entityManager->getRepository(Oem::class)
                    ->findOneBy(['good' => $newGood->getId(), 'oe' => $oe->getOe()]);
            if (empty($oem)){
                $oe->setGood($newGood);
                $this->entityManager->persist($oe);
                $this->entityManager->flush();
            }    
        }
        
        $this->changeGood($good, $newGood);
        
        return;
    }
    
    /**
     * Объеденить производителей
     * @param Producer $producerDest Новый
     * @param Producer $producerSource Старый
     */
    public function uniteProducer($producerDest, $producerSource)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(1800);
        $startTime = time();
        
        $unknownProducers = $this->entityManager->getRepository(UnknownProducer::class)
                ->findBy(['producer' => $producerSource->getId()]);
        foreach ($unknownProducers as $unknownProducer){
            $unknownProducer->setProducer($producerDest);
            $this->entityManager->persist($unknownProducer);
            $this->entityManager->flush();            
        }

        $oldGoods = $this->entityManager->getRepository(Goods::class)
                ->findBy(['producer' => $producerSource->getId()]);
                
        foreach ($oldGoods as $oldGood){
            $this->changeProducer($oldGood, $producerDest);
            if (time() > $startTime + 1760){
                break;
            }
        }       
        return;
    }
    
    /**
     * Перепровести 
     * @param Register $register
     */
    public function repostDoc($register)
    {
        switch ($register->getDocType()){
            case Movement::DOC_ORDER: 
                $order = $this->entityManager->getRepository(Order::class)
                    ->find($register->getDocId());
                if ($order){
                    $this->orderManager->repostOrder($order);
                }
                break;
            case Movement::DOC_OT: 
                $ot = $this->entityManager->getRepository(Ot::class)
                    ->find($register->getDocId());
                if ($ot){
                    $this->otManager->repostOt($ot);
                }
                break;
            case Movement::DOC_PT: 
                $pt = $this->entityManager->getRepository(Pt::class)
                    ->find($register->getDocId());
                if ($pt){
                    $this->ptManager->repostPt($pt);
                }
                break;
            case Movement::DOC_PTU: 
                $ptu = $this->entityManager->getRepository(Ptu::class)
                    ->find($register->getDocId());
                if ($ptu){
                    $this->ptuManager->repostPtu($ptu);
                }
                break;
            case Movement::DOC_REVISE: 
                $revise = $this->entityManager->getRepository(Revise::class)
                    ->find($register->getDocId());
                if ($revise){
                    //
                }
                break;
            case Movement::DOC_ST: 
                $st = $this->entityManager->getRepository(St::class)
                    ->find($register->getDocId());
                if ($st){
                    $this->stManager->repostSt($st);
                }
                break;
            case Movement::DOC_VT: 
                $vt = $this->entityManager->getRepository(Vt::class)
                    ->find($register->getDocId());
                if ($vt){
                    $this->vtManager->repostVt($vt);
                }
                break;
            case Movement::DOC_VTP: 
                $vtp = $this->entityManager->getRepository(Vtp::class)
                    ->find($register->getDocId());
                if ($vtp){
                    $this->vtpManager->repostVtp($vtp);
                }
                break;
            default: break;    
        }
        
        return;
    }
}

