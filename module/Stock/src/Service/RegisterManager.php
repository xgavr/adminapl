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
use Application\Entity\Bid;
use Stock\Entity\PtGood;
use Stock\Entity\StGood;
use Stock\Entity\VtGood;
use Stock\Entity\VtpGood;

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
     * Order manager
     * @var \Application\Service\OrderManager
     */
    private $orderManager;
    
    
    private $meDate = '2015-01-31';

    /**
     * Constructs the service.
     */
    public function __construct($entityManager, $logManager, $otManager, $ptManager,
            $ptuManager, $stManager, $vtManager, $vtpManager, $orderManager) 
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
    }
    
    public function currentUser()
    {
        return $this->logManager->currentUser();
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
        
        $otRegister = $this->entityManager->getRepository(Register::class)
                ->findOneBy(['docType' => Movement::DOC_OT, 'docId' => $ot->getId()]);
        $this->docActualize($otRegister);

        return;
    }
    
    /**
     * Найти и поправить ПТУ с неверной датой прихода
     * @param Goods $good
     * @param date $docDate
     * @return null
     */
    private function findNearPtu($good, $docDate)
    {
        $ptu = $this->entityManager->getRepository(Register::class)
                ->findNearPtu($good, $docDate);
        if ($ptu){
            var_dump($good->getId()); exit;
            $ptu->setDocDate($docDate);
            $ptu->setComment('#Поправка даты, старая дата: '.$ptu->getDocdate());
            $this->entityManager->persist($ptu);
            $this->entityManager->flush($ptu);
            $this->ptuManager->repostPtu($ptu);
            
            $ptuRegister = $this->entityManager->getRepository(Register::class)
                            ->findOneBy(['docType' => Movement::DOC_PTU, 'docId' => $ptu->getId()]);
            $this->docActualize($ptuRegister);
            return true;
        }
        return false;
    }
    
    /**
     * Найти и поправить ПТУ с одинаковым артикулом
     * @param Goods $good
     * @param date $docDate
     * @return null
     */
    private function correctCodePtu($good, $docDate)
    {
        $ptu = $this->entityManager->getRepository(Register::class)
                ->correctCodePtu($good, $docDate);
        if ($ptu){
            $ptu->setDocDate($docDate);
            $ptu->setComment('#Поправка даты и товара, старая дата: '.$ptu->getDocdate());
            $this->entityManager->persist($ptu);
            $this->entityManager->flush($ptu);
            $this->ptuManager->repostPtu($ptu);
            
            $ptuRegister = $this->entityManager->getRepository(Register::class)
                            ->findOneBy(['docType' => Movement::DOC_PTU, 'docId' => $ptu->getId()]);
            $this->docActualize($ptuRegister);
            return true;
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
        $flag = true;
        switch ($register->getDocType()){
            case Movement::DOC_ORDER:
                $order = $this->entityManager->getRepository(Order::class)
                    ->find($register->getDocId());
                if ($order){
                    $this->orderManager->repostOrder($order);
                    if ($order->getStatus() == Order::STATUS_SHIPPED){
                        $takeNo = $this->entityManager->getRepository(Bid::class)
                                ->count(['order' => $order->getId(), 'take' => Bid::TAKE_NO]);
                        $flag = $takeNo == 0;
                        if (!$flag && $order->getDateOper() <= $this->meDate){
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
                                if ($this->findNearPtu($bid->getGood(), $order->getDateOper())){
                                    var_dump(11); exit;
                                    return $this->docActualize($register);
                                } 
                                if ($this->correctCodePtu($bid->getGood(), $order->getDateOper())){
                                    var_dump($order->getId()); exit;
                                    return $this->docActualize($register);                                    
                                } 
                                $rows[] = [
                                    'goodId' => $bid->getGood()->getId(),
                                    'quantity' => $bid->getNum(),
                                    'amount' => $bid->getNum()*$bid->getPrice(),
                                ];
                            }
                            $data['rows'] = $rows;
                            $this->oldOt($data);
                            $flag = $this->docActualize($register);
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
                    $this->ptManager->repostPt($pt);
                    if ($pt->getStatus() == Pt::STATUS_ACTIVE){
                        $takeNo = $this->entityManager->getRepository(PtGood::class)
                                ->count(['pt' => $pt->getId(), 'take' => PtGood::TAKE_NO]);
                        $flag = $takeNo == 0;
                        if (!$flag && $pt->getDocDate() <= $this->meDate){
                            $ptGoods = $this->entityManager->getRepository(PtGood::class)
                                    ->findBy(['pt' => $pt->getId(), 'take' => PtGood::TAKE_NO]);
                            foreach ($ptGoods as $ptGood){
                                if ($this->findNearPtu($ptGood->getGood(), $pt->getDocDate())){
                                    return $this->docActualize($register);
                                } 
                                if ($this->correctCodePtu($ptGood->getGood(), $pt->getDocDate())){
                                    return $this->docActualize($register);                                    
                                } 
                            }
                            $flag = $this->docActualize($register);
                        }
                    }    
                }
                break;
            case Movement::DOC_PTU:
                $ptu = $this->entityManager->getRepository(Ptu::class)
                    ->find($register->getDocId());
                if ($ptu){
                    $this->ptuManager->repostPtu($ptu);
                    $flag = true;
                }
                break;
            case Movement::DOC_ST:
                $st = $this->entityManager->getRepository(St::class)
                    ->find($register->getDocId());
                if ($st){
                    $this->stManager->repostSt($st);
                    if ($st->getStatus() == St::STATUS_ACTIVE){
                        $takeNo = $this->entityManager->getRepository(StGood::class)
                                ->count(['st' => $st->getId(), 'take' => StGood::TAKE_NO]);
                        $flag = $takeNo == 0;
                        if (!$flag && $st->getDocDate() <= $this->meDate){
                            $stGoods = $this->entityManager->getRepository(StGood::class)
                                    ->findBy(['st' => $st->getId(), 'take' => StGood::TAKE_NO]);
                            foreach ($stGoods as $stGood){
                                if ($this->findNearPtu($stGood->getGood(), $st->getDocDate())){
                                    return $this->docActualize($register);
                                } 
                                if ($this->correctCodePtu($stGood->getGood(), $st->getDocDate())){
                                    return $this->docActualize($register);                                    
                                } 
                            }
                            $flag = $this->docActualize($register);
                        }
                    }    
                }
                break;
            case Movement::DOC_VT:
                $vt = $this->entityManager->getRepository(Vt::class)
                    ->find($register->getDocId());
                if ($vt){
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
                    $flag = $this->vtpManager->repostVtp($vtp);
                    if ($vtp->getStatus() == Vtp::STATUS_ACTIVE){
                        $takeNo = $this->entityManager->getRepository(VtpGood::class)
                                ->count(['vtp' => $vtp->getId(), 'take' => VtpGood::TAKE_NO]);
                        $flag = $takeNo == 0;
                    }   
                }
                break;
            default: $flag = false;    
        }
        
        $this->updateVar($register);
        
        return $flag;
    }
    
    /**
     *  Восстановление последовательности
     */
    public function actualize()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(900);
        $startTime = time();
        
        $registers = $this->entityManager->getRepository(Register::class)
                ->findForActualize();

        if ($registers){
            foreach ($registers as $register){
                if ($this->docActualize($register)){
                    usleep(100);                    
                } else {
                    throw new \Exception('Документ не проведен!');
                }
                if (time() > $startTime + 840){
                    break;
                }
            }
        }

        return;                
    }
}

