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
     * Добавить атрибут
     * 
     * @param array $attr
     * @return \Application\Entity\Attribute;
     */
    public function addAtribute($attr)
    {
        $attribute = $this->getEntityManager()->getRepository(Attribute::class)
                ->findOneByTdId($attr['attrId']);

        if ($attribute == null){
            $data = [
                'td_id' => $attr['attrId'],
                'block_no' => $attr['attrBlockNo'],
                'is_conditional' => (int) boolval($attr['attrIsConditional']),
                'is_interval' => (int) boolval($attr['attrIsInterval']),
                'is_linked' => (int) boolval($attr['attrIsLinked']),
                'value_type' => $attr['attrType'],
                'value_unit' => (isset($attr['attrUnit'])) ? $attr['attrUnit']:'',
                'name' => $attr['attrName'],
                'short_name' => (isset($attr['attrShortName'])) ? $attr['attrShortName']:$attr['attrName'],
                'status' => Attribute::STATUS_ACTIVE,
                'status_ex' => Attribute::EX_TO_TRANSFER,
            ];
        
            $this->getEntityManager()->getConnection()->insert('attribute', $data);           

            $attribute = $this->getEntityManager()->getRepository(Attribute::class)
                 ->findOneByTdId($attr['attrId']);
        }
        
        return $attribute;
    }
    
    /**
     * Обновить атрибут
     * 
     * @param \Application\Entity\Attribute $attribute
     * @param array $data
     */
    public function updateAttribute($attribute, $data)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->update(Attribute::class, 'a')
                ->where('a.id = ?1')
                ->setParameter('1', $attribute->getId())
                ;
        
        foreach ($data as $key => $value){
            $queryBuilder->set('a'.$key, $value);
        }
        
        return $queryBuilder->getQuery()->getResult();        
        
    }
    
    /**
     * Добавить значение атрибута
     * 
     * @param array $attr
     * @return \Application\Entity\AttributeValue;
     */
    public function addAtributeValue($attr)
    {
        $value = isset($attr['attrValue']) ? $attr['attrValue']:'';
        $attributeValue = $this->getEntityManager()->getRepository(AttributeValue::class)
                ->findOneBy(['tdId' => $attr['attrValueId'], 'value' => $value]);

        if ($attributeValue == null){
            $data = [
                'td_id' => $attr['attrValueId'],
                'value' => $value,
                'status_ex' => AttributeValue::EX_TO_TRANSFER,
            ];

            $this->getEntityManager()->getConnection()->insert('attribute_value', $data);           

            $attributeValue = $this->getEntityManager()->getRepository(AttributeValue::class)
                    ->findOneBy(['tdId' => $attr['attrValueId'], 'value' => $value]);
        }
        
        return $attributeValue;
    }
    
    /**
     * Запрос значений атрибутов для экспорта
     * 
     * @return query;
     */
    public function queryAtributeValueEx()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('av')
                ->from(AttributeValue::class, 'av')
                ->where('av.statusEx = ?1')
                ->setParameter('1', AttributeValue::EX_TO_TRANSFER)
                ;
        
        return $queryBuilder->getQuery();//->iterate();        
    }
    
    /**
     * Добавление значения атрибута к товару
     * 
     * @param \Application\Entity\Goods $good
     * @param array $attr
     */
    public function addGoodAttributeValue($good, $attr)
    {

        $attribute = $this->addAtribute($attr);
        
        if ($attribute){            
            
            $attributeValue = $this->addAtributeValue($attr);
            
            if ($attributeValue){
                $this->getEntityManager()->getRepository(Goods::class)
                            ->addGoodAttributeValue($good, $attribute, $attributeValue);
            }                
        }
        
        return;

    }
        
    
}
