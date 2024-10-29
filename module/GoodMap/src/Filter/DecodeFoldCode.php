<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace GoodMap\Filter;

use Laminas\Filter\AbstractFilter;
use GoodMap\Entity\Rack;
use GoodMap\Entity\Shelf;
use GoodMap\Entity\Cell;

/**
 * Расшифровать код хранения
 *
 * @author Daddy
 */
class DecodeFoldCode extends AbstractFilter
{

    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    // Доступные опции фильтра.
    protected $options = [
    ];    
    
    // Конструктор.
    public function __construct($options = null) 
    {     
        // Задает опции фильтра (если они предоставлены).
        if(is_array($options)) {
            $this->entityManager = $options['entityManager'];
        }    
    }
    
    /**
     * 
     * @param string $value
     * @return array
     */
    public function filter($value)
    {
        $rack = $this->entityManager->getRepository(Rack::class)
                ->findOneBy(['code' => $value]);
        
        $result = [
            'rack' => $rack,
            'shelf' => null,
            'cell' => null,
        ];    
        
        $shelf = $this->entityManager->getRepository(Shelf::class)
                ->findOneBy(['code' => $value]);
        if ($shelf){
            $result = [
                'rack' => $shelf->getRack(),
                'shelf' => $shelf,
                'cell' => null,
            ];    
        }
        
        $cell = $this->entityManager->getRepository(Cell::class)
                ->findOneBy(['code' => $value]);
        if ($cell){
            $result = [
                'rack' => $cell->getShelf()->getRack(),
                'shelf' => $cell->getShelf(),
                'cell' => $cell,
            ];    
        }
        
        return $result;
    }
    
}
