<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;
use Doctrine\ORM\EntityRepository;
use Application\Entity\Token;
use Application\Entity\FpTree;

/**
 * Description of FpTreeRepository
 *
 * @author Daddy
 */
class FpTreeRepository  extends EntityRepository{

    /**
     * Добавить ветвь
     * 
     * @param Token $token
     * @param Token|integer $rootToken
     * @param FpTree|integer $rootTree
     * 
     * @return FpTree|null;
     */
    public function findBanch($token, $rootToken = 0, $rootTree = 0)
    {
        if (is_numeric($rootToken)){
            $rootTokenId = $rootToken;            
        } else {
            $rootTokenId = $rootToken->getId();            
        }
        
        if (is_numeric($rootTree)){
            $rootTreeId = $rootTree;            
        } else {
            $rootTreeId = $rootTree->getId();            
        }

        return $this->getEntityManager()->getRepository(FpTree::class)
                ->findOneBy(['rootTree' => $rootTreeId, 'rootToken' => $rootToken, 'token' => $token->getId()]);
    }    

    /**
     * Добавить ветвь
     * 
     * @param Token $token
     * @param Token|integer $rootToken
     * @param FpTree|integer $rootTree
     * 
     * @return null;
     */
    public function addBanch($token, $rootToken = 0, $rootTree = 0)
    {
        if (is_numeric($rootToken)){
            $rootTokenId = $rootToken;            
        } else {
            $rootTokenId = $rootToken->getId();            
        }
        
        if (is_numeric($rootTree)){
            $rootTreeId = $rootTree;            
        } else {
            $rootTreeId = $rootTree->getId();            
        }
        
        $fpTree = $this->findBanch($token, $rootTokenId, $rootTreeId);
        
        if (!$fpTree){
            $this->getEntityManager()->getConnection()->insert('fp_tree', [
                'root_tree_id' => $rootTreeId,
                'root_token_id' => $rootTokenId,
                'token_id' => $token->getId(),
                'frequency' => 1,
            ]);           
            $fpTree = $this->findBanch($token, $rootTokenId, $rootTreeId);
        } else {
            $this->getEntityManager()->getConnection()->update('fp_tree', [
                'frequency' => $fpTree->getFrequency() + 1,
            ], ['id' => $fpTree->getId()]);                       
        }
                
        return $fpTree;
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
     * @param Goods $good
     * @param array $attr
     * @param bool $similarGood
     */
    public function addGoodAttributeValue($good, $attr, $similarGood = false)
    {

        $attribute = $this->addAtribute($attr);
        
        if ($attribute){            
            
            if ($similarGood){
                if ($attribute->getSimilarGood() == Attribute::FOR_SIMILAR_NO_GOOD){
                    return;
                }
            }
            
            $attributeValue = $this->addAtributeValue($attr);
            
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
