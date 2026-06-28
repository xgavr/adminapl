<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Laminas\Filter\AbstractFilter;
use Application\Entity\Goods;
use Application\Entity\Car;

/**
 * Наличие товара
 *
 * @author Daddy
 */
class GoodPrompt extends AbstractFilter
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
    public function __construct($entityManager, $options = null) 
    {     
        $this->entityManager = $entityManager;
        
        // Задает опции фильтра (если они предоставлены).
        if(is_array($options)) {
            $this->options = $options;
        }    
    }
    
    /**
     * 
     * @param Goods $good
     * @param string $type
     * @return string
     */
    public function filter($good, $type = null)
    {
        $norms = null;
        
        if ($type == 'attr' || empty($type)){
            
            if ($good->inAntifreezCategory()){
                $norms = $this->entityManager->getRepository(Car::class)
                        ->normsList([8]);
            }
        
            if ($good->inMotorOilCategory()){
                $norms = $this->entityManager->getRepository(Car::class)
                        ->normsList([2]);
            }

            if ($good->inTransOilCategory()){
                $norms = $this->entityManager->getRepository(Car::class)
                        ->normsList([4, 6, 11, 13, 15, 17, 18, 21, 23, 28, 30, 32, 34, 36, 40, 43, 46, 49, 61, 74, 76, 78]);
            }

            if ($good->inBrakeOilCategory()){
                $norms = $this->entityManager->getRepository(Car::class)
                        ->normsList([10, 26]);
            }
        }    

        $resultOem = "Сделай строку в формате JSON с оригинальными номерами для {$good->getCode()} {$good->getProducer()->getName()} "
                . "без аналогов в виде {oems: [{oem: xxxx, 'brand': 'xxxx'}]}, где oem - оригинальный номер, brand - производитель. "
                . "Если номеров нет, то выводи пустой массив. Ничего не выдумывай!";
        
        $resultAttr = "Сделай строку в формате JSON с основными характеристиками (узлы, линейка/модель, состав, свойства, цвет, вес, температура, спецификации и т.п.) "
                . "для {$good->getCode()} {$good->getProducer()->getName()} в виде {attr: [{type:xxx, name: xxxx, 'value': 'xxxx', 'unit': 'xxxx'}]}, "
                . "где name - название характеристики (цвет, вес, спецификация и т.п.), value - значение характеристики, unit - размерность (кг, л. и т.п.), "
                . "type - тип характеристики. Для спецификаций антифриза, масла, жидкостей type = A, для остальных случаев type = G.";
        
        if(!empty($norms)){
            $norms = implode(', ', $norms);
            $resultAttr .= " Используй нормализованные значения спецификаций - $norms. ";
        } 
        
        $resultAttr .= " Если имеется несколько характеристик с одинаковым наименованием, но разными значениями, то для каждого значения делается новая строка."
                . " Если характеристик нет, то выводи пустой массив. Не добавляй в характеристики бренд и артикул. Ничего не выдумывай!";
        
        if ($type == 'attr'){
            return $resultAttr;
        }
        
        if ($type == 'oem'){
            return $resultOem;
        } 
        
        $result = $resultAttr . ' и ' . $resultOem . ' Собери все в один объект JSON.';
        
        return $result;
    }
    
}
