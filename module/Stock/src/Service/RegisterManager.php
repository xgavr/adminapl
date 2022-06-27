<?php
namespace Stock\Service;

use Admin\Entity\Log;
use Stock\Entity\Mutual;
use Stock\Entity\Retail;
use Stock\Entity\Revise;
use Company\Entity\Legal;
use Application\Entity\Phone;
use Application\Entity\Contact;
use Company\Entity\Contract;
use Application\Entity\Supplier;
use Company\Entity\Office;
use User\Filter\PhoneFilter;
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
     * Добавить оприходование раньше 2013-12-15
     * @param Order $order
     * @return Ot
     */
    private function oldOt($order)
    {
        if ($order->getDateOper() <= $this->meDate){
            $bids = $this->entityManager->getRepository(Bid::class)
                    ->findBy(['order' => $order->getId(), 'take' => Bid::TAKE_NO]);
            if (count($bids)){
                $otData = [
                    'apl_id' => 0,
                    'doc_date' => date('Y-m-d', strtotime($order->getDateOper(), '-1 days')),
                    'comment' => "Дооприходование для заказа {$order->getId()} раньше {$this->meDate}",
                    'status_ex' => Ot::STATUS_EX_APL, 
                    'status' => Ot::STATUS_INVENTORY,
                    'office' => $order->getOffice(),
                    'company' => $order->getCompany(),
                ];

                $ot = $this->otManager->addOt($otData);
                
                $i = 1;
                foreach ($bids as $bid){
                    $otgGoodData = [
                        'quantity' => $bid->getNum(),
                        'amount' => $bid->getNum()*$bid->getPrice(),
                        'good_id' => $bid->getGood()->getId(),
                    ];

                    $this->otManager->addOtGood($ot->getId(), $otgGoodData, $i);
                    $i++;
                }
                
                $this->otManager->updateOtAmount($ot);
                
                return $ot;
            }                
        }
        
        return;
    }
    
    /**
     * Добавить оприходование раньше 2013-12-15
     * @param Pt $pt
     * @return Ot
     */
    private function oldOtPt($pt)
    {
        if ($pt->getDocDate() <= $this->meDate){
            $ptGoods = $this->entityManager->getRepository(PtGood::class)
                    ->findBy(['pt' => $pt->getId(), 'take' => PtGood::TAKE_NO]);
            if (count($ptGoods)){
                $otData = [
                    'apl_id' => 0,
                    'doc_date' => date('Y-m-d', strtotime($pt->getDocDate(), '-1 days')),
                    'comment' => "Дооприходование для перемещения {$pt->getId()} раньше {$this->meDate}",
                    'status_ex' => Ot::STATUS_EX_APL, 
                    'status' => Ot::STATUS_INVENTORY,
                    'office' => $pt->getOffice(),
                    'company' => $pt->getCompany(),
                ];

                $ot = $this->otManager->addOt($otData);
                
                $i = 1;
                foreach ($ptGoods as $ptGood){
                    $otgGoodData = [
                        'quantity' => $ptGood->getQuantity(),
                        'amount' => $ptGood->getAmount(),
                        'good_id' => $ptGood->getGood()->getId(),
                    ];

                    $this->otManager->addOtGood($ot->getId(), $otgGoodData, $i);
                    $i++;
                }
                
                $this->otManager->updateOtAmount($ot);
                
                return $ot;
            }                
        }
        
        return;
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
                        if (!$flag){
                            $ot = $this->oldOt($order);
                            if ($ot){
                                $otRegister = $this->entityManager->getRepository(Register::class)
                                        ->findOneBy(['docType' => Movement::DOC_OT, 'docId' => $ot->getId()]);
                                $this->docActualize($otRegister);
                                $flag = $this->docActualize($register);
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
                    $this->ptManager->repostPt($pt);
                    if ($pt->getStatus() == Pt::STATUS_ACTIVE){
                        $takeNo = $this->entityManager->getRepository(PtGood::class)
                                ->count(['pt' => $pt->getId(), 'take' => PtGood::TAKE_NO]);
                        $flag = $takeNo == 0;
                        if (!$flag){
                            $ot = $this->oldOtPt($pt);
                            if ($ot){
                                $otRegister = $this->entityManager->getRepository(Register::class)
                                        ->findOneBy(['docType' => Movement::DOC_OT, 'docId' => $ot->getId()]);
                                $this->docActualize($otRegister);
                                $flag = $this->docActualize($register);
                            }
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
                    $this->vtpManager->repostVtp($vtp);
                    $flag = true;
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

