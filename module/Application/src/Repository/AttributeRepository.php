<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;
use Doctrine\ORM\EntityRepository;
use Application\Entity\Goods;
use Application\Entity\Attribute;
use Application\Entity\AttributeValue;


/**
 * Description of AttributeRepository
 *
 * @author Daddy
 */
class AttributeRepository  extends EntityRepository{

    /**
     * Добавить значение атрибута
     * 
     * @param array $attr
     * @return \Application\Entity\AttributeValue;
     */
    public function addAtributeValue($attr)
    {
        $attributeValue = $this->getEntityManager()->getRepository(AttributeValue::class)
                ->findOneByTdId($attr['attrValueId']);

        if ($attributeValue == null){
            $data = [
                'td_id' => $attr['attrValueId'],
                'value' => $attr['attrValue'],
            ];
        
            $this->getEntityManager()->getConnection()->insert('attribute_value', $data);           

            $attributeValue = $this->getEntityManager()->getRepository(AttributeValue::class)
                 ->findOneByTdId($attr['attrValueId']);
        }
        
        return $attributeValue;
    }
    
    /**
     * Добавление атрибута к товару
     * 
     * @param \Application\Entity\Goods $good
     * @param array $attr
     */
    public function addAttributeToGood($good, $attr)
    {
//        var_dump($attr); exit;
        $attribute = $this->getEntityManager()->getRepository(Attribute::class)
                ->findOneByTdId($attr['attrId']);
        
        if ($attribute == null){            
            
            $attributeValue = $this->addAtributeValue($attr);
            
            if ($attributeValue){
                $data = [
                    'td_id' => $attr['attrId'],
                    'block_no' => $attr['attrBlockNo'],
                    'is_conditional' => (int) boolval($attr['attrIsConditional']),
                    'is_interval' => (int) boolval($attr['attrIsInterval']),
                    'is_linked' => (int) boolval($attr['attrIsLinked']),
                    'value_type' => $attr['attrType'],
                    'name' => $attr['attrName'],
                    'short_name' => (isset($attr['attrShortName'])) ? $attr['attrShortName']:$attr['attrName'],
                    'status' => Attribute::STATUS_ACTIVE,
                    'value_id' => $attributeValue->getId(),
                ];

                $this->getEntityManager()->getConnection()->insert('attribute', $data);

                $attribute = $this->getEntityManager()->getRepository(Attribute::class)
                    ->findOneByTdId(['tdId' => $attr['attrId']]);
                
                //$attributeValue->addAttribute($attribute);
            }                
        }

        if ($attribute){
            $this->getEntityManager()->getRepository(Goods::class)
                        ->addGoodAttributeValue($good, $attribute);
            
            //$good->addAttribut($attribut);
        }    
        
        return $attribute;
    }
        
    
}
