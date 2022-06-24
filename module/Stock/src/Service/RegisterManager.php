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
        $start = 0;
        
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

