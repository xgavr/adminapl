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
use GoodMap\Filter\DecodeFoldCode;

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
            $rack->setCode($office->getId().'-'.$codeCount);                
            
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
     * @param Rack $rack
     * @param int $status
     */
    public function updateRackStatus($rack, $status)
    {
        $rack->setStatus($status);
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
     * @param Shelf $shelf
     * @param int $status
     */
    public function updateShelfStatus($shelf, $status)
    {
        $shelf->setStatus($status);
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
     * @param Cell $cell
     * @param int $status
     */
    public function updateCellStatus($cell, $status)
    {
        $cell->setStatus($status);
        $this->entityManager->persist($cell);
        $this->entityManager->flush();
        
        return;
    }    

    /**
     * Разложить код на сущности
     * @param string $code
     */
    public function decodeCode($code) 
    {
        $decodeFilter = new DecodeFoldCode();
        return $decodeFilter->filter($code);
    }           
}
