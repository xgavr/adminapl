<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\GenericGroup;
use Application\Entity\Goods;
use Application\Entity\TokenGroup;
use Application\Entity\Token;
use Phpml\Math\Statistic\Mean;
use Phpml\Math\Statistic\StandardDeviation;
use Application\Validator\Sigma3;


/**
 * Description of GenericGroupRepository
 *
 * @author Daddy
 */
class GenericGroupRepository extends EntityRepository{

    public function findAllGenericGroup()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('g')
            ->from(GenericGroup::class, 'g')
            ->orderBy('g.id')
                ;

        return $queryBuilder->getQuery();
    }
    
    /**
     * Запрос по группам по разным параметрам
     * 
     * @param array $params
     * @return object
     */
    public function findAllGroup($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('g')
            ->from(GenericGroup::class, 'g')
                ;
        
        if (is_array($params)){
            if (isset($params['q'])){
                $queryBuilder->where('g.name like :search')
                    ->setParameter('search', '%' . $params['q'] . '%')
                        ;
            }
            if (isset($params['next1'])){
                $queryBuilder->where('g.name > ?1')
                    ->setParameter('1', $params['next1'])
                    ->addOrderBy('g.name')
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->where('g.name < ?2')
                    ->setParameter('2', $params['prev1'])
                    ->addOrderBy('g.name', 'DESC')
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['status'])){
                $queryBuilder->andWhere('g.status = ?3')
                    ->setParameter('3', $params['status'])
                        ;
            }            
            if (isset($params['sort'])){
                $queryBuilder->addOrderBy('g.'.$params['sort'], $params['order']);                
            }            
        }

        return $queryBuilder->getQuery();
    }   
    

    public function updateZeroGroup()
    {
        $zeroGroup = $this->getEntityManager()->getRepository(GenericGroup::class)
                ->findOneByTdId(0);
        
        $this->getEntityManager()->getConnection()->update('goods', ['generic_group_id' => $zeroGroup->getId()], ['generic_group_id' => 0]);
        
        return;
    }
    
    /**
     * Установить пустую группу в товаре
     * @param Goods $good
     * @return type
     */
    public function updateZeroGroupInGood($good)
    {
        $zeroGroup = $this->getEntityManager()->getRepository(GenericGroup::class)
                ->findOneByTdId(0);
        
        $this->getEntityManager()->getConnection()->update('goods', ['generic_group_id' => $zeroGroup->getId()], ['id' => $good->getId()]);
        
        return;
    }
    
    /**
     * Обновление количества товаров в группах
     */
    public function updateGoodCount()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('gg.id, count(g.id) as gCount')
            ->from(GenericGroup::class, 'gg')
            ->leftJoin('gg.goods', 'g')    
            ->groupBy('gg.id')                
                ;
        
        $data = $queryBuilder->getQuery()->getResult();

        foreach ($data as $row){
            if ($row['gCount']){
                $status = GenericGroup::STATUS_ACTIVE;
            } else {
                $status = GenericGroup::STATUS_RETIRED;
            }
            $this->getEntityManager()->getConnection()->update('generic_group', ['good_count' => $row['gCount'], 'status' => $status], ['id' => $row['id']]);            
        }        
    }

    /**
     * Добавить группу товаров
     * 
     * @param array $data
     */
    public function addGenericGroup($data)
    {
       $genericGroup = $this->getEntityManager()->getRepository(GenericGroup::class)
               ->findOneByTdId($data['td_id']);
       
       if ($genericGroup == null){
           $this->getEntityManager()->getConnection()->insert('generic_group', $data);
       }
       
       return;
    }
    
    
    /**
     * Поиск группы по группе наименований
     * 
     * @param TokenGroup $tokenGroup
     * @param Goods $good
     */
    public function genericTokenGroup($tokenGroup, $good = null)
    {
        if ($tokenGroup){
            $entityManager = $this->getEntityManager();

            $queryBuilder = $entityManager->createQueryBuilder();

            $queryBuilder->select('gg, count(g.id) as goodCount')
                    ->from(GenericGroup::class, 'gg')
                    ->join('gg.goods', 'g')
                    ->where('g.tokenGroup = ?1')
                    ->andWhere('g.tdDirect = ?3')
                    ->andWhere('gg.tdId != 0')
                    ->groupBy('gg.id')
                    ->orderBy('goodCount', 'DESC')
                    ->setParameter('1', $tokenGroup->getId())
                    ->setParameter('3', Goods::TD_DIRECT)
                    ;
            if (isset($good)){
                $queryBuilder->andWhere('gg.id != ?2')
                        ->andHaving('goodCount > ?4')
                        ->setParameter('2', $good->getId())
                        ->setParameter('4', GenericGroup::MIN_GOOD_COUNT)
                        ;
            }

            return $queryBuilder->getQuery()->getResult();
        }    
        return;        
    }

    /**
     * Поиск лучшей группы
     * 
     * @param array $groups
     * @return GenericGroup 
     */
    protected function findBestGenericGroup($groups)
    {
        $counts = [];
        foreach ($groups as $group){
            $counts[] = $group['goodCount'];
        }
        if (count($counts)){
            $mean = Mean::arithmetic($counts);
            $dispersion = StandardDeviation::population($counts, count($counts)>1);
            $validator = new Sigma3();
            if (!$validator->isValid($groups[0]['goodCount'], $mean, $dispersion)){
                return $groups[0][0];
            }
        }
        
        return;
    }
    
    /**
     * Выбор группы по группе наименований
     * 
     * @param TokenGroup $tokenGroup
     * @param Goods $good
     * 
     * @return GenericGroup
     */
    public function findGenericTokenGroup($tokenGroup, $good = null)
    {
        $data = $this->genericTokenGroup($tokenGroup, $good);

        if ($data){
            if (count($data) == 1){
                foreach ($data as $row){
                    return $row[0];
                }
            } else {
                return $this->findBestGenericGroup($data);
            }
        }
        
        return;
    }
    
    /**
     * Поиск групп наименований по группе 
     * 
     * @param \Application\Entity\GenericGroup $genericGroup
     * @param array $params
     */
    public function tokenGenericGroup($genericGroup, $params = null)
    {
        if ($genericGroup){
            $entityManager = $this->getEntityManager();

            $queryBuilder = $entityManager->createQueryBuilder();

            $queryBuilder->select('tg')
                    ->distinct()
                    ->from(TokenGroup::class, 'tg')
                    ->join('tg.goods', 'g')
                    ->where('g.genericGroup = ?1')
                    ->setParameter('1', $genericGroup->getId())
                    ;
            
            if (is_array($params)){
                if (isset($params['sort'])){
                    $queryBuilder->orderBy('tg.'.$params['sort'], $params['order'])
                            ;
                }
            }    

            return $queryBuilder->getQuery();
        }    
        return;        
    }
    
    /**
     * Получить токены группы
     * 
     * @param GenericGroup $genericGroup
     * @params array $params
     * @params array
     */
    public function getTokens($genericGroup, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('t')
                ->from(Token::class, 't')
                ->join('t.genericGroups', 'gg')
                ->where('gg.id = ?1')
                ->setParameter('1', $genericGroup->getId())
                ;

        if (is_array($params)){
            if (isset($params['sort'])){
                $queryBuilder->orderBy('t.'.$params['sort'], $params['order'])
                        ;
            }
        }    
        
        return $queryBuilder->getQuery();       
    }

    /**
     * Заплнить токены группы
     * 
     * @param GenericGroup $genericGroup
     * @return void
     */
    public function updateGenericGroupToken($genericGroup)
    {
        $existingTokens = $this->getTokens($genericGroup)->getResult();
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('t')
                ->distinct()
                ->from(Token::class, 't')
                ->join('t.tokenGroups', 'tg')
                ->join('tg.goods', 'g')
                ->where('g.genericGroup = ?1')
                ->setParameter('1', $genericGroup->getId())
                ;
                if (count($existingTokens)){
                    foreach ($existingTokens as $existingToken) {
                        $queryBuilder->andWhere($queryBuilder->expr()->neq('t.id', $existingToken->getId()));
                    }
                }    
        
        $tokens = $queryBuilder->getQuery()->getResult();
        
        if (count($tokens)){
            foreach ($tokens as $token){
                $this->getEntityManager()->getConnection()->insert('generic_group_token', 
                        ['generic_group_id' => $genericGroup->getId(), 'token_id' => $token->getId()]);
            }
        }    
        return;
    }
    
    /**
     * Получить группы апл соответствующую общей групе
     * 
     * @param GenericGroup $genericGroup
     */
    public function getGroupApl($genericGroup)
    {
        if (!$genericGroup->getTdId()){
            return [];
        }
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('g.groupApl, count(g.id) as goodCount')
                ->from(Goods::class, 'g')
                ->where('g.genericGroup = ?1')
                ->andWhere('g.groupApl != ?2')
                ->andWhere('g.groupApl != 0')
                ->setParameter('1', $genericGroup->getId())
                ->setParameter('2', Goods::DEFAULT_GROUP_APL_ID)
                ->groupBy('g.groupApl')
                ->orderBy('goodCount', 'DESC')
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }
    
    /**
     * НЕИСПОЛЬЗУЕТСЯ
     * Быстрое обновление группы апл в товарах общей группы
     * 
     * @param GenericGroup $genericGroup
     * @return integer
     */
    public function updateGoodsGroupApl($genericGroup)
    {
        if ($genericGroup->getAplId()){
            $entityManager = $this->getEntityManager();

            $queryBuilder = $entityManager->createQueryBuilder();
            $queryBuilder->update(Goods::class, 'g')
                    ->where('g.genericGroup = ?1')
                    ->andWhere($queryBuilder->expr()->orX(
                            $queryBuilder->expr()->eq('g.groupApl', 0),
                            $queryBuilder->expr()->eq('g.groupApl', Goods::DEFAULT_GROUP_APL_ID)
                        )
                    )
                    ->andWhere('g.groupApl != ?2')
                    ->set('g.groupApl', $genericGroup->getAplId())
                    ->setParameter('1', $genericGroup->getId())
                    ->setParameter('2', $genericGroup->getAplId())
                    ;

            return $queryBuilder->getQuery()->getResult();        
        }
        
        return;
    }
    
    
    /**
     * Обновить группу апл
     * 
     * @param GenericGroup $genericGroup
     * @return type
     */
    public function updateGroupApl($genericGroup)
    {
        $aplGroups = $this->getGroupApl($genericGroup);
        if (count($aplGroups)){
            foreach($aplGroups as $row){
                if ($genericGroup->getTdId() > 0){
                    $this->getEntityManager()->getConnection()->update('generic_group', ['apl_id' => $row['groupApl']], ['id' => $genericGroup->getId()]);
                } else {
                    $this->getEntityManager()->getConnection()->update('generic_group', ['apl_id' => 0], ['id' => $genericGroup->getId()]);                    
                }    
                return;
            }    
        }
        return;
    }
}
