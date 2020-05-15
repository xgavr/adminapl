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
use Application\Entity\GoodAttributeValue;


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
     * @return Attribute;
     */
    public function addAtribute($attr)
    {
        $attribute = $this->getEntityManager()->getRepository(Attribute::class)
                ->findOneByTdId($attr['id']);

        if ($attribute == null){
            $data = [
                'td_id' => $attr['id'],
                'value_id' => 0,
                'block_no' => $attr['attrBlockNo'],
                'is_conditional' => (int) boolval($attr['conditional']),
                'is_interval' => (int) boolval($attr['interval']),
                'is_linked' => (int) boolval($attr['applicable']),
                'value_type' => substr($attr['type'], 0, 3),
                'value_unit' => (isset($attr['unitName'])) ? $attr['unitName']:'',
                'name' => $attr['name'],
                'short_name' => (isset($attr['nameAbbreviation'])) ? $attr['nameAbbreviation']:$attr['name'],
                'status' => Attribute::STATUS_ACTIVE,
                'status_ex' => Attribute::EX_TO_TRANSFER,
            ];
        
            $this->getEntityManager()->getConnection()->insert('attribute', $data);           

            $attribute = $this->getEntityManager()->getRepository(Attribute::class)
                 ->findOneByTdId($attr['id']);
        }
        
        return $attribute;
    }
    
    /**
     * Обновить атрибут
     * 
     * @param Attribute $attribute
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
     * @return AttributeValue;
     */
    public function addAtributeValue($attr)
    {
        $value = isset($attr['value']) ? $attr['value']:'';
        $attributeValue = $this->getEntityManager()->getRepository(AttributeValue::class)
                ->findOneBy(['tdId' => $attr['id'], 'value' => $value]);

        if ($attributeValue == null){
            $data = [
                'td_id' => $attr['id'],
                'value' => $value,
                'status_ex' => AttributeValue::EX_TO_TRANSFER,
            ];

            $this->getEntityManager()->getConnection()->insert('attribute_value', $data);           

            $attributeValue = $this->getEntityManager()->getRepository(AttributeValue::class)
                    ->findOneBy(['tdId' => $attr['id'], 'value' => $value]);
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
     * @param Goods $good
     * @param array $attr
     * @param bool $similarGood
     */
    public function addGoodAttributeValue($good, $attr, $similarGood = false)
    {

        $attribute = $this->addAtribute($attr['property']);
        
        if ($attribute){            
            
            if ($similarGood){
                if ($attribute->getSimilarGood() == Attribute::FOR_SIMILAR_NO_GOOD){
                    return;
                }
            }
            
            $attributeValue = $this->addAtributeValue($attr['value']);
            
            if ($attributeValue){
                $this->getEntityManager()->getRepository(Goods::class)
                            ->addGoodAttributeValue($good, $attribute, $attributeValue);
            }                
        }
        
        return;

    }
        
    /**
     * Атрибуты для наименования товара
     * 
     * @param Goods $good
     */
    public function nameAttribute($good)
    {
        $result = [];
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('av.value')
                ->distinct()
                ->from(GoodAttributeValue::class, 'gav')
                ->join('gav.attribute', 'a')
                ->join('gav.attributeValue', 'av')
                ->where('gav.good = ?1')
                ->andWhere('a.toBestName = ?2')
                ->andWhere('a.status = ?3')
                ->setParameter('1', $good->getId())
                ->setParameter('2', Attribute::TO_BEST_NAME)
                ->setParameter('3', Attribute::STATUS_ACTIVE)                
                ;
        
        $data = $queryBuilder->getQuery()->getResult();        
        
        foreach ($data as $row){
            $result[] = $row['value'];
        }
        
        return implode(' ', $result);
    }
}
