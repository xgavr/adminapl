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

/**
 * Description of GoodMapManager
 * 
 * @author Daddy
 */
class GoodMapManager {
    
    /**
     * Adapter
     */
    const HTTPS_ADAPTER = 'Laminas\Http\Client\Adapter\Curl';  
    
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
     * @param Shelf $shelf
     * @param array $data
     * @return Cell
     */
    public function addCell($shelf, $data)
    {
        $cell = new Cell();
        $cell->setComment(empty($data['comment']) ?  null:$data['comment']);
        $cell->setName(empty($data['name']) ?  null:$data['name']);
        $cell->setCode(empty($data['code']) ?  null:$data['code']);
        $cell->setFoldCount(0);
        $cell->setStatus($data['status']);
        $cell->setShelf($shelf);
        
        $this->entityManager->persist($cell);
        $this->entityManager->flush();
                
        if (!$cell->getCode()){
            $codeCount = $this->entityManager->getRepository(Cell::class)
                    ->count(['shelf' => $shelf->getId()]);
            
            $cell->setCode($shelf->getCode().'-'.$codeCount);                
            
            $this->entityManager->persist($cell);
            $this->entityManager->flush();
        }
        if (!$cell->getName()){
            $cell->setName('Ячейка '.$cell->getCode());
            $this->entityManager->persist($cell);
            $this->entityManager->flush();        
        }
        
        return $cell;
    }
    
    /**
     * 
     * @param Cell $cell
     * @param array $data
     * @return Cell
     */
    public function updateCell($cell, $data)
    {
        $cell->setComment(empty($data['comment']) ?  null:$data['comment']);
        $cell->setName(empty($data['name']) ?  null:$data['name']);
        $cell->setCode(empty($data['code']) ?  null:$data['code']);
        $cell->setStatus($data['status']);
        
        $this->entityManager->persist($cell);
        $this->entityManager->flush();
        
        return $cell;
    }
    
    /**
     * 
     * @param Cell $cell
     * @return bool
     */
    public function removeCell($cell)
    {
        $foldCount = $this->entityManager->getRepository(Fold::class)
                ->count(['cell' => $cell->getId()]);
        if ($foldCount){
            return false;
        }
        
        $this->entityManager->remove($cell);
        $this->entityManager->flush();
        
        return true;
    }

    /**
     * 
     * @param Rack $rack
     * @param array $data
     * @return Shelf
     */
    public function addShelf($rack, $data)
    {
        $shelf = new Shelf();
        $shelf->setComment(empty($data['comment']) ?  null:$data['comment']);
        $shelf->setName(empty($data['name']) ?  null:$data['name']);
        $shelf->setCode(empty($data['code']) ?  null:$data['code']);
        $shelf->setFoldCount(0);
        $shelf->setStatus($data['status']);
        $shelf->setRack($rack);
        
        $this->entityManager->persist($shelf);
        $this->entityManager->flush();
                
        if (!$shelf->getCode()){
            $codeCount = $this->entityManager->getRepository(Shelf::class)
                    ->count(['rack' => $rack->getId()]);
            
            $shelf->setCode($rack->getCode().'-'.$codeCount);                
            
            $this->entityManager->persist($shelf);
            $this->entityManager->flush();
        }
        if (!$shelf->getName()){
            $shelf->setName('Полка '.$shelf->getCode());
            $this->entityManager->persist($shelf);
            $this->entityManager->flush();
        }
        
        return $shelf;
    }
    
    /**
     * 
     * @param Shelf $shelf
     * @param array $data
     * @return Shelf
     */
    public function updateShelf($shelf, $data)
    {
        $shelf->setComment(empty($data['comment']) ?  null:$data['comment']);
        $shelf->setName(empty($data['name']) ?  null:$data['name']);
        $shelf->setCode(empty($data['code']) ?  null:$data['code']);
        $shelf->setStatus($data['status']);
        
        $this->entityManager->persist($shelf);
        $this->entityManager->flush();
        
        return $shelf;
    }
    
    /**
     * 
     * @param Shelf $shelf
     * @return bool
     */
    public function removeShelf($shelf)
    {
        $foldCount = $this->entityManager->getRepository(Fold::class)
                ->count(['shelf' => $shelf->getId()]);
        if ($foldCount){
            return false;
        }
        
        $cells = $this->entityManager->getRepository(Cell::class)
                ->findBy(['shelf' => $shelf->getId()]);
        
        foreach ($cells as $cell){
            if (!$this->removeCell($cell)){
                return false;
            }
        }
        
        $this->entityManager->remove($shelf);
        $this->entityManager->flush();
        
        return true;
    }

    /**
     * 
     * @param Office $office
     * @param array $data
     * @return Rack
     */
    public function addRack($office, $data)
    {
        $rack = new Rack();
        $rack->setComment(empty($data['comment']) ?  null:$data['comment']);
        $rack->setName(empty($data['name']) ?  null:$data['name']);
        $rack->setCode(empty($data['code']) ?  null:$data['code']);
        $rack->setFoldCount(0);
        $rack->setStatus($data['status']);
        $rack->setOffice($office);
        
        $this->entityManager->persist($rack);
        $this->entityManager->flush();

        if (!$rack->getCode()){
            $codeCount = $this->entityManager->getRepository(Rack::class)
                    ->count(['office' => $office->getId()]);
            $rack->setCode($codeCount);                
            
            $this->entityManager->persist($rack);
            $this->entityManager->flush();
        }
        
        if (!$rack->getName()){
            $rack->setName('Стеллаж '.$rack->getCode());
            $this->entityManager->persist($rack);
            $this->entityManager->flush();
        }
                
        return $rack;
    }
    
    /**
     * 
     * @param Rack $rack
     * @param array $data
     * @return Rack
     */
    public function updateRack($rack, $data)
    {
        $rack->setComment(empty($data['comment']) ?  null:$data['comment']);
        $rack->setName(empty($data['name']) ?  null:$data['name']);
        $rack->setCode(empty($data['code']) ?  null:$data['code']);
        $rack->setStatus($data['status']);
        
        $this->entityManager->persist($rack);
        $this->entityManager->flush();
        
        return $rack;
    }
    
    /**
     * 
     * @param Rack $rack
     * @return bool
     */
    public function removeRack($rack)
    {
        $foldCount = $this->entityManager->getRepository(Fold::class)
                ->count(['rack' => $rack->getId()]);
        if ($foldCount){
            return false;
        }
        
        $shelfs = $this->entityManager->getRepository(Shelf::class)
                ->findBy(['rack' => $rack->getId()]);
        
        foreach ($shelfs as $shelf){
            if (!$this->removeShelf($shelf)){
                return false;
            }
        }
        
        $this->entityManager->remove($rack);
        $this->entityManager->flush();
        
        return true;
    }    
    
    /**
     * 
     * @param Rack $rack
     * @param str $name
     */
    public function updateRackName($rack, $name)
    {
        $rack->setName($name);
        $this->entityManager->persist($rack);
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * 
     * @param Rack $rack
     * @param str $comment
     */
    public function updateRackComment($rack, $comment)
    {
        $rack->setComment($comment);
        $this->entityManager->persist($rack);
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * 
     * @param Rack $rack
     * @param str $code
     */
    public function updateRackCode($rack, $code)
    {
        $rack->setCode($code);
        $this->entityManager->persist($rack);
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * 
     * @param Shelf $shelf
     * @param str $name
     */
    public function updateShelfName($shelf, $name)
    {
        $shelf->setName($name);
        $this->entityManager->persist($shelf);
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * 
     * @param Shelf $shelf
     * @param str $comment
     */
    public function updateShelfComment($shelf, $comment)
    {
        $shelf->setComment($comment);
        $this->entityManager->persist($shelf);
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * 
     * @param Shelf $shelf
     * @param str $code
     */
    public function updateShelfCode($shelf, $code)
    {
        $shelf->setCode($code);
        $this->entityManager->persist($shelf);
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * 
     * @param Cell $cell
     * @param str $name
     */
    public function updateCellName($cell, $name)
    {
        $cell->setName($name);
        $this->entityManager->persist($cell);
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * 
     * @param Cell $cell
     * @param str $comment
     */
    public function updateCellComment($cell, $comment)
    {
        $cell->setComment($comment);
        $this->entityManager->persist($cell);
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * 
     * @param Cell $cell
     * @param str $code
     */
    public function updateCellCode($cell, $code)
    {
        $cell->setCode($code);
        $this->entityManager->persist($cell);
        $this->entityManager->flush();
        
        return;
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
        $this->entityManager->remove($fold);
        $this->entityManager->flush();
        
        return true;
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
        
        $foldBalance = $this->entityManager->getRepository(FoldBalance::class)
                ->findOneBy(array_filter($params));
                
        if (!$foldBalance){
            $foldBalance = new FoldBalance();
            $foldBalance->setCell($fold->getCell());
            $foldBalance->setGood($fold->getGood());
            $foldBalance->setOffice($fold->getOffice());
            $foldBalance->setRack($fold->getRack());
            $foldBalance->setShelf($fold->getShelf());
            $foldBalance->setStatus(FoldBalance::STATUS_ACTIVE);
        }
        
        $foldBalance->setRest($this->entityManager->getRepository(Fold::class)->goodFoldRest($fold));
        
        $this->entityManager->persist($foldBalance);
        $this->entityManager->flush();
        
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
        $foldDoc->setDateCreated(date('Y--m-d H:i:s'));
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
                
        return $foldDoc;
    }
        
    /**
     * 
     * @param FoldDoc $foldDoc
     * @return bool
     */
    public function removeFoldDoc($foldDoc)
    {
        $this->entityManager->remove($foldDoc);
        $this->entityManager->flush();
        
        return true;
    }        
}
