<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace GoodMap\Service;

use GoodMap\Entity\Rack;
use GoodMap\Entity\Shelf;
use GoodMap\Entity\Cell;
use Company\Entity\Office;
use GoodMap\Entity\Fold;
use GoodMap\Entity\FoldBalance;
use GoodMap\Entity\FoldDoc;
use Stock\Entity\Ptu;
use Stock\Entity\Movement;
use Stock\Entity\Ot;
use Stock\Entity\Vt;
use Stock\Entity\St;
use Stock\Entity\Pt;
use Stock\Entity\Vtp;
use Application\Entity\Order;
use Stock\Entity\Register;

/**
 * Description of FoldManager
 * 
 * @author Daddy
 */
class FoldManager {
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Admin manager.
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;
        
    /**
     * Log manager.
     * @var \Admin\Service\LogManager
     */
    private $logManager;
        
    public function __construct($entityManager, $adminManager, $logManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
        $this->logManager = $logManager;
    }
    
    public function currentUser()
    {
        return $this->logManager->currentUser();
    }
        
    /**
     * 
     * @param array $data
     * @return Fold
     */
    public function addFold($data)
    {
        $fold = new Fold();
        $fold->setCell(empty($data['cell']) ?  null:$data['cell']);
        $fold->setDateOper($data['dateOper']);
        $fold->setDocId(empty($data['docId']) ?  null:$data['docId']);
        $fold->setDocKey(empty($data['docKey']) ?  null:$data['docKey']);
        $fold->setDocStamp(empty($data['docStamp']) ?  null:$data['docStamp']);
        $fold->setDocType(empty($data['docType']) ?  null:$data['docType']);
        $fold->setGood($data['good']);
        $fold->setOffice($data['office']);
        $fold->setQuantity($data['quantity']);
        $fold->setRack($data['rack']);
        $fold->setShelf(empty($data['shelf']) ?  null:$data['shelf']);
        $fold->setStatus($data['status']);
        
        $this->entityManager->persist($fold);
        $this->entityManager->flush();
        
        $this->updateFoldBalance($fold);
        
        return $fold;
    }
    
    /**
     * @param Fola $fold
     * @param array $data
     * @return Fold
     */
    public function updateFold($fold, $data)
    {
        $fold->setCell(empty($data['cell']) ?  null:$data['cell']);
        $fold->setDateOper($data['dateOper']);
        $fold->setDocId(empty($data['docId']) ?  null:$data['docId']);
        $fold->setDocKey(empty($data['docKey']) ?  null:$data['docKey']);
        $fold->setDocStamp(empty($data['docStamp']) ?  null:$data['docStamp']);
        $fold->setDocType(empty($data['docType']) ?  null:$data['docType']);
        $fold->setGood($data['good']);
        $fold->setOffice($data['office']);
        $fold->setQuantity($data['quantity']);
        $fold->setRack($data['rack']);
        $fold->setShelf(empty($data['shelf']) ?  null:$data['shelf']);
        $fold->setStatus($data['status']);
        
        $this->entityManager->persist($fold);
        $this->entityManager->flush();
        
        $this->updateFoldBalance($fold);
        
        return $fold;
    }
    
    /**
     * 
     * @param Fold $fold
     * @return bool
     */
    public function removeFold($fold)
    {
        
        $fold->setStatus(Fold::STATUS_RETIRED);
        $this->entityManager->persist($fold);
        $this->entityManager->flush();

        $params = array_filter([
            'good' => $fold->getGood()->getId(),
            'office' => $fold->getOffice()->getId(),
            'rack' => $fold->getRackId(),
            'shelf' => $fold->getShelfId(),
            'cell' => $fold->getCellId(),
        ]);

        $foldBalance = $this->entityManager->getRepository(FoldBalance::class)
                ->findOneBy($params);
        
        if ($foldBalance){
            $foldBalance->setRest(max(0, $this->entityManager->getRepository(Fold::class)->goodFoldRest($fold)));
        }    

        $this->entityManager->remove($fold);
        $this->entityManager->flush();        
        
        return true;
    }    
    
    /**
     * 
     * @param int $docId
     * @param int $docType
     */
    private function removeFoldsByDoc($docId, $docType)
    {
        $folds = $this->entityManager->getRepository(Fold::class)
                ->findBy(['docId' => $docId, 'docType' => $docType]);
        
        foreach ($folds as $fold){
            $this->removeFold($fold);
        }
        
        return;
    }
    
    /**
     * Обновить остаток в месте хранения
     * @param Fold $fold
     */
    public function updateFoldBalance($fold)
    {
        $params = [
            'good' => $fold->getGood()->getId(),
            'office' => $fold->getOffice()->getId(),
            'rack' => $fold->getRackId(),
            'shelf' => $fold->getShelfId(),
            'cell' => $fold->getCellId(),
        ];
        
        $rest = $this->entityManager->getRepository(Fold::class)->goodFoldRest($fold);
//        var_dump($rest);
        $foldBalance = $this->entityManager->getRepository(FoldBalance::class)
                ->findOneBy(array_filter($params));
        
        if ($foldBalance){
            $foldBalance->setRest(max(0, $rest));

            $this->entityManager->persist($foldBalance);
            $this->entityManager->flush();

            return;
        }
                
        if (!empty($rest)){
            $foldBalance = new FoldBalance();
            $foldBalance->setCell($fold->getCell());
            $foldBalance->setGood($fold->getGood());
            $foldBalance->setOffice($fold->getOffice());
            $foldBalance->setRack($fold->getRack());
            $foldBalance->setShelf($fold->getShelf());
            $foldBalance->setStatus(FoldBalance::STATUS_ACTIVE);
            
            $foldBalance->setFoldCode($fold->getRack()->getCode());
            $foldBalance->setFoldName($fold->getRack()->getName());
            if ($fold->getShelf()){
                $foldBalance->setFoldCode($fold->getShelf()->getCode());
                $foldBalance->setFoldName($fold->getShelf()->getName());
            }
            if ($fold->getCell()){
                $foldBalance->setFoldCode($fold->getCell()->getCode());
                $foldBalance->setFoldName($fold->getCell()->getName());
            }

            $foldBalance->setRest(max(0, $rest));

            $this->entityManager->persist($foldBalance);
            $this->entityManager->flush();            
        }
                
        return;
    }
    
    /**
     * 
     * @param FoldDoc $foldDoc
     */
    public function repostFoldDoc($foldDoc)
    {
        $this->removeFoldsByDoc($foldDoc->getId(), Movement::DOC_FT);
        
        $docStamp = $this->entityManager->getRepository(Register::class)
                ->foldDocRegister($foldDoc);
        
        if ($foldDoc->getKind() === FoldDoc::KIND_SET){
            $goodFolds = $this->entityManager->getRepository(FoldBalance::class)
                    ->findBy(['good' => $foldDoc->getGood()->getId(), 
                        'office' => $foldDoc->getOffice()->getId(),
                        'status' => FoldBalance::STATUS_ACTIVE]);
            
            foreach ($goodFolds as $goodFold){
                if ($goodFold->getRest() > 0){
                    $this->addFold([
                        'dateOper' => $foldDoc->getDocDate(),
                        'docId' => $foldDoc->getId(),
                        'docKey' => $foldDoc->getLogKey(),
                        'docStamp' => $docStamp,
                        'docType' => Movement::DOC_FT,
                        'good' => $foldDoc->getGood(),
                        'office' => $foldDoc->getOffice(),
                        'quantity' => -$goodFold->getRest(),
                        'rack' => $goodFold->getRack(),
                        'shelf' => $goodFold->getShelf(),
                        'cell' => $goodFold->getCell(),
                        'status' => Fold::STATUS_ACTIVE,
                    ]);
                }    
            }    
        }  
        
        $this->addFold([
            'dateOper' => $foldDoc->getDocDate(),
            'docId' => $foldDoc->getId(),
            'docKey' => $foldDoc->getLogKey(),
            'docStamp' => $docStamp,
            'docType' => Movement::DOC_FT,
            'good' => $foldDoc->getGood(),
            'office' => $foldDoc->getOffice(),
            'quantity' => $foldDoc->getQuantity(),
            'rack' => $foldDoc->getRack(),
            'shelf' => $foldDoc->getShelf(),
            'cell' => $foldDoc->getCell(),
            'status' => Fold::STATUS_ACTIVE,
        ]);

        return;
    }
    
    /**
     * 
     * @param array $data
     * @return FoldDoc
     */
    public function addFoldDoc($data)
    {        
        $foldDoc = new FoldDoc();
        $foldDoc->setCell(empty($data['cell']) ?  null:$data['cell']);
        $foldDoc->setDateCreated(date('Y-m-d H:i:s'));
        $foldDoc->setDocDate($data['docDate']);
        $foldDoc->setGood($data['good']);
        $foldDoc->setKind($data['kind']);
        $foldDoc->setOffice($data['office']);
        $foldDoc->setQuantity($data['quantity']);
        $foldDoc->setRack($data['rack']);
        $foldDoc->setShelf(empty($data['shelf']) ?  null:$data['shelf']);
        $foldDoc->setStatus($data['status']);
        
        $this->entityManager->persist($foldDoc);
        $this->entityManager->flush();
                
        $this->repostFoldDoc($foldDoc);
                
        return $foldDoc;
    }
        
    /**
     * 
     * @param FoldDoc $foldDoc
     * @param array $data
     * @return Fold
     */
    public function updateFoldDoc($foldDoc, $data)
    {
        $foldDoc->setCell(empty($data['cell']) ?  null:$data['cell']);
        $foldDoc->setDocDate($data['docDate']);
        $foldDoc->setGood($data['good']);
        $foldDoc->setKind($data['kind']);
        $foldDoc->setOffice($data['office']);
        $foldDoc->setQuantity($data['quantity']);
        $foldDoc->setRack($data['rack']);
        $foldDoc->setShelf(empty($data['shelf']) ?  null:$data['shelf']);
        $foldDoc->setStatus($data['status']);
        
        $this->entityManager->persist($foldDoc);
        $this->entityManager->flush();
                
        $this->repostFoldDoc($foldDoc);
                        
        return $foldDoc;
    }
        
    /**
     * 
     * @param FoldDoc $foldDoc
     * @return bool
     */
    public function removeFoldDoc($foldDoc)
    {
        $this->removeFoldsByDoc($foldDoc->getId(), Movement::DOC_FT);
        
        $this->entityManager->remove($foldDoc);
        $this->entityManager->flush();
        
        return true;
    }       
    
    /**
     * Выдача офиса
     * @param Office $office
     * @return Rack
     */
    private function findOfficeRack($office)
    {
        $rack = $this->entityManager->getRepository(Rack::class)
                ->findOneBy(['status' => Rack::STATUS_EXTRADITION, 'office' => $office->getId()]);
        
        if ($rack){
            return $rack;
        }
        
        $rack = $this->entityManager->getRepository(Rack::class)
                ->findOneBy(['code' => 1, 'office' => $office->getId()]);
        
        if (!$rack){
            return $rack;
        }
        
        return;
    }
    
    /**
     * 
     * @param Ptu $ptu
     * @param float $docStamp
     */
    public function ptuFold($ptu, $docStamp)
    {
        $this->removeFoldsByDoc($ptu->getId(), Movement::DOC_PTU);

        if ($ptu->getStatus() === Ptu::STATUS_ACTIVE){
            
            $defaultRack = $this->findOfficeRack($ptu->getOffice());
            
            if (!$defaultRack){
                return;
            }
                        
            foreach ($ptu->getPtuGoods() as $ptuGood){
                $lastFoldDoc =  $this->entityManager->getRepository(FoldDoc::class)
                        ->findLastFoldDoc($ptu->getOffice(), $ptuGood->getGood(), $ptu->getDocDate());
                
                if (empty($lastFoldDoc)){        
                    $this->addFold([
                        'dateOper' => $ptu->getDocDate(),
                        'docId' => $ptu->getId(),
                        'docKey' => $ptu->getLogKey(),
                        'docStamp' => $docStamp,
                        'docType' => Movement::DOC_PTU,
                        'good' => $ptuGood->getGood(),
                        'office' => $ptu->getOffice(),
                        'quantity' => $ptuGood->getQuantity(),
                        'rack' => $defaultRack,
                        'status' => Fold::STATUS_ACTIVE,
                    ]);
                }    
            }
        }    
    }    
    
    /**
     * 
     * @param Ot $ot
     * @param float $docStamp
     */
    public function otFold($ot, $docStamp)
    {
        $this->removeFoldsByDoc($ot->getId(), Movement::DOC_OT);
        
        if ($ot->getStatus() === Ot::STATUS_ACTIVE){
            
            $defaultRack = $this->findOfficeRack($ot->getOffice());

            if (!$defaultRack){
                return;
            }
            
            foreach ($ot->getOtGoods() as $otGood){
                $lastFoldDoc =  $this->entityManager->getRepository(FoldDoc::class)
                        ->findLastFoldDoc($ot->getOffice(), $otGood->getGood(), $ot->getDocDate());
                
                if (empty($lastFoldDoc)){        
                    $this->addFold([
                        'dateOper' => $ot->getDocDate(),
                        'docId' => $ot->getId(),
                        'docKey' => $ot->getLogKey(),
                        'docStamp' => $docStamp,
                        'docType' => Movement::DOC_OT,
                        'good' => $otGood->getGood(),
                        'office' => $ot->getOffice(),
                        'quantity' => $otGood->getQuantity(),
                        'rack' => $defaultRack,
                        'status' => Fold::STATUS_ACTIVE,
                    ]);
                }    
            }
        }    
    } 
    
    /**
     * 
     * @param Vt $vt
     * @param float $docStamp
     */
    public function vtFold($vt, $docStamp)
    {
        $this->removeFoldsByDoc($vt->getId(), Movement::DOC_VT);
        
        if ($vt->getStatus() === Vt::STATUS_ACTIVE){
            
            $defaultRack = $this->findOfficeRack($vt->getOffice());

            if (!$defaultRack){
                return;
            }

            foreach ($vt->getVtGoods() as $vtGood){
                $lastFoldDoc =  $this->entityManager->getRepository(FoldDoc::class)
                        ->findLastFoldDoc($vt->getOffice(), $vtGood->getGood(), $vt->getDocDate());
                
                if (empty($lastFoldDoc)){        
                    $this->addFold([
                        'dateOper' => $vt->getDocDate(),
                        'docId' => $vt->getId(),
                        'docKey' => $vt->getLogKey(),
                        'docStamp' => $docStamp,
                        'docType' => Movement::DOC_VT,
                        'good' => $vtGood->getGood(),
                        'office' => $vt->getOffice(),
                        'quantity' => $vtGood->getQuantity(),
                        'rack' => $defaultRack,
                        'status' => Fold::STATUS_ACTIVE,
                    ]);
                }    
            }
        }    
    }  
    
    /**
     * 
     * @param St $st
     * @param float $docStamp
     */
    public function stFold($st, $docStamp)
    {
        $this->removeFoldsByDoc($st->getId(), Movement::DOC_ST);
        
        if ($st->getStatus() === St::STATUS_ACTIVE){
            
            foreach ($st->getStGoods() as $stGood){
                $lastFoldDoc =  $this->entityManager->getRepository(FoldDoc::class)
                        ->findLastFoldDoc($st->getOffice(), $stGood->getGood(), $st->getDocDate());
                
                if (!empty($lastFoldDoc)){
                    continue;
                }        
                
                $stGoodQuantity = $stGood->getQuantity();
                
                $goodFolds = $this->entityManager->getRepository(FoldBalance::class)
                        ->findBy(['good' => $stGood->getGood()->getId(), 
                            'office' => $st->getOffice()->getId(),
                            'status' => FoldBalance::STATUS_ACTIVE]);
                
                foreach ($goodFolds as $goodFold){
                    
                    $writeOf = min($goodFold->getRest(), $stGoodQuantity);
                    
                    $this->addFold([
                        'dateOper' => $st->getDocDate(),
                        'docId' => $st->getId(),
                        'docKey' => $st->getLogKey(),
                        'docStamp' => $docStamp,
                        'docType' => Movement::DOC_ST,
                        'good' => $stGood->getGood(),
                        'office' => $st->getOffice(),
                        'quantity' => -$writeOf,
                        'rack' => $goodFold->getRack(),
                        'shelf' => $goodFold->getShelf(),
                        'cell' => $goodFold->getCell(),
                        'status' => Fold::STATUS_ACTIVE,
                    ]);
                    
                    $stGoodQuantity -= $writeOf;
                    
                    if ($stGoodQuantity <= 0) {
                        break;
                    }
                }    
            }
        }    
    } 
    
    /**
     * 
     * @param Order $order
     * @param float $docStamp
     */
    public function orderFold($order, $docStamp)
    {
        $this->removeFoldsByDoc($order->getId(), Movement::DOC_ORDER);
        
        if ($order->getStatus() === Order::STATUS_SHIPPED){
            
            foreach ($order->getBids() as $bid){
                
                $lastFoldDoc =  $this->entityManager->getRepository(FoldDoc::class)
                        ->findLastFoldDoc($order->getOffice(), $bid->getGood(), $order->getDocDate());
                
                if (!empty($lastFoldDoc)){
                    continue;
                }        

                $bidQuantity = $bid->getNum();
                
                $goodFolds = $this->entityManager->getRepository(FoldBalance::class)
                        ->findBy(['good' => $bid->getGood()->getId(), 
                            'office' => $order->getOffice()->getId(),
                            'status' => FoldBalance::STATUS_ACTIVE]);
                
                foreach ($goodFolds as $goodFold){
                    
                    $writeOf = min($goodFold->getRest(), $bidQuantity);
                    
                    $this->addFold([
                        'dateOper' => $order->getDocDate(),
                        'docId' => $order->getId(),
                        'docKey' => $order->getLogKey(),
                        'docStamp' => $docStamp,
                        'docType' => Movement::DOC_ORDER,
                        'good' => $bid->getGood(),
                        'office' => $order->getOffice(),
                        'quantity' => -$writeOf,
                        'rack' => $goodFold->getRack(),
                        'shelf' => $goodFold->getShelf(),
                        'cell' => $goodFold->getCell(),
                        'status' => Fold::STATUS_ACTIVE,
                    ]);
                    
                    $bidQuantity -= $writeOf;
                    
                    if ($bidQuantity <= 0) {
                        break;
                    }
                }    
            }
        }    
    }   
    
    /**
     * 
     * @param Vtp $vtp
     * @param float $docStamp
     */
    public function vtpFold($vtp, $docStamp)
    {
        $this->removeFoldsByDoc($vtp->getId(), Movement::DOC_VTP);
        
        if ($vtp->getStatus() === Vtp::STATUS_ACTIVE && $vtp->getStatusDoc() === Vtp::STATUS_DOC_NOT_RECD){
            
            foreach ($vtp->getVtpGoods() as $vtpGood){
                
                $lastFoldDoc =  $this->entityManager->getRepository(FoldDoc::class)
                        ->findLastFoldDoc($vtp->getPtu()->getOffice(), $vtpGood->getGood(), $vtp->getDocDate());
                
                if (!empty($lastFoldDoc)){
                    continue;
                }        

                $vtpGoodQuantity = $vtpGood->getQuantity();
                
                $vtpGoodFolds = $this->entityManager->getRepository(FoldBalance::class)
                        ->findBy(['good' => $vtpGood->getGood()->getId(), 
                            'office' => $vtp->getPtu()->getOffice()->getId(),
                            'status' => FoldBalance::STATUS_ACTIVE]);
                
                foreach ($vtpGoodFolds as $vtpGoodFold){
                    
                    $writeOf = min($vtpGoodFold->getRest(), $vtpGoodQuantity);
                    
                    $this->addFold([
                        'dateOper' => $vtp->getDocDate(),
                        'docId' => $vtp->getId(),
                        'docKey' => $vtp->getLogKey(),
                        'docStamp' => $docStamp,
                        'docType' => Movement::DOC_VTP,
                        'good' => $vtpGood->getGood(),
                        'office' => $vtp->getPtu()->getOffice(),
                        'quantity' => -$writeOf,
                        'rack' => $vtpGoodFold->getRack(),
                        'shelf' => $vtpGoodFold->getShelf(),
                        'cell' => $vtpGoodFold->getCell(),
                        'status' => Fold::STATUS_ACTIVE,
                    ]);
                    
                    $vtpGoodQuantity -= $writeOf;
                    
                    if ($vtpGoodQuantity <= 0) {
                        break;
                    }
                }    
            }
        }    
    }  
    
    /**
     * 
     * @param Pp $pt
     * @param float $docStamp
     */
    public function ptFold($pt, $docStamp)
    {
        $this->removeFoldsByDoc($pt->getId(), Movement::DOC_PT);
        
        if ($pt->getStatus() === Pt::STATUS_ACTIVE){
            
            $defaultRack = $this->findOfficeRack($pt->getOffice2());
            
            foreach ($pt->getPtGoods() as $ptGood){
                
                $lastFoldDoc =  $this->entityManager->getRepository(FoldDoc::class)
                        ->findLastFoldDoc($pt->getOffice(), $ptGood->getGood(), $pt->getDocDate());
                
                if (!empty($lastFoldDoc)){
                    continue;
                }        

                $ptGoodQuantity = $ptGood->getQuantity();
                
                $ptGoodFolds = $this->entityManager->getRepository(FoldBalance::class)
                        ->findBy(['good' => $ptGood->getGood()->getId(), 
                            'office' => $pt->getOffice()->getId(),
                            'status' => FoldBalance::STATUS_ACTIVE]);
                
                foreach ($ptGoodFolds as $ptGoodFold){
                    
                    $writeOf = min($ptGoodFold->getRest(), $ptGoodQuantity);
                    
                    $this->addFold([
                        'dateOper' => $pt->getDocDate(),
                        'docId' => $pt->getId(),
                        'docKey' => $pt->getLogKey(),
                        'docStamp' => $docStamp,
                        'docType' => Movement::DOC_PT,
                        'good' => $ptGood->getGood(),
                        'office' => $pt->getOffice(),
                        'quantity' => -$writeOf,
                        'rack' => $ptGoodFold->getRack(),
                        'shelf' => $ptGoodFold->getShelf(),
                        'cell' => $ptGoodFold->getCell(),
                        'status' => Fold::STATUS_ACTIVE,
                    ]);
                    
                    $ptGoodQuantity -= $writeOf;
                    
                    if ($ptGoodQuantity <= 0) {
                        break;
                    }
                }    

                if (empty($defaultRack)){
                    continue;
                }
            
                $this->addFold([
                    'dateOper' => $pt->getDocDate(),
                    'docId' => $pt->getId(),
                    'docKey' => $pt->getLogKey(),
                    'docStamp' => $docStamp,
                    'docType' => Movement::DOC_OT,
                    'good' => $ptGood->getGood(),
                    'office' => $pt->getOffice2(),
                    'quantity' => $ptGood->getQuantity(),
                    'rack' => $defaultRack,
                    'status' => Fold::STATUS_ACTIVE,
                ]);
            }
        }    
    }    
}
