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
     * Добавление атрибута к товару
     * 
     * @param \Application\Entity\Goods $good
     * @param array $attribute
     */
    public function addAttributeToGood($good, $attribute)
    {
        $attribute = $this->getEntityManager()->getRepository(Attribute::class)
                ->findOneByTdId($attribute['attrId']);
        
        if ($attribute == null){
            
            $attributeValue = $this->getEntityManager()->getRepository(AttributeValue::class)
                    ->findOneByTdId($attribute['attrValueId']);
            
            if ($attributeValue == null){
                $value = [
                    'td_id' => $attribute['attrValueId'],
                    'value' => $attribute['attrValue'],
                ];
                
               $this->getEntityManager()->getConnection()->insert('attribute_value', $value);

               $attributeValue = $this->getEntityManager()->getRepository(AttributeValue::class)
                    ->findOneByTdId($attribute['attrValueId']);
            }
            
            if ($attributeValue){
                $data = [
                    'td_id' => $attribute['attrId'],
                    'block_no' => $attribute['attrBlockNo'],
                    'is_conditional' => $attribute['attrIsConditional'],
                    'is_interval' => $attribute['attrIsInterval'],
                    'is_linked' => $attribute['attrIsLinked'],
                    'value_type' => $attribute['attrType'],
                    'name' => $attribute['attrName'],
                    'short_name' => $attribute['attrShortName'],
                    'status' => Attribute::STATUS_ACTIVE,
                    'value_id' => $attributeValue->getId(),
                ];

                $this->getEntityManager()->getConnection()->insert('attribute', $data);

                $attribute = $this->getEntityManager()->getRepository(Attribute::class)
                    ->findOneByTdId(['tdId' => $attribute['attrId']]);
            }                
        }

        if ($attribute){
            $this->getEntityManager()->getRepository(Goods::class)
                        ->addGoodAttribute($good, $attribute);
        }    
        
        return $attribute;
    }
        
    
    /**
     * Запрос по номерам по разным параметрам
     * 
     * @param array $params
     * @return object
     */
    public function findAllOem($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('o, g')
            ->from(Oem::class, 'o')
            ->join('o.good', 'g')    
            ->orderBy('o.id', 'DESC')
            ->setMaxResults(100)                
                ;   
        
        if (is_array($params)){
            if ($params['q']){
                $filter = new \Application\Filter\ArticleCode();
                $queryBuilder->where('o.oe like :search')
                    ->setParameter('search', '%' . $filter->filter($params['q']) . '%')
                        ;
            }
            if (isset($params['next1'])){
                $queryBuilder->where('o.oe > ?1')
                    ->setParameter('1', $params['next1'])
                    ->orderBy('o.oe')
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->where('o.oe < ?2')
                    ->setParameter('2', $params['prev1'])
                    ->orderBy('o.oe', 'DESC')
                    ->setMaxResults(1)    
                 ;
            }
        }
//var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }            

}
