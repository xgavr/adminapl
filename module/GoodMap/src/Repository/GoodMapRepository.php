<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace GoodMap\Repository;

use Doctrine\ORM\EntityRepository;
use GoodMap\Entity\Rack;
use Company\Entity\Office;
use GoodMap\Entity\Shelf;
use GoodMap\Entity\Cell;
use GoodMap\Entity\Fold;


/**
 * Description of GoodMapRepository
 *
 * @author Daddy
 */
class GoodMapRepository extends EntityRepository
{
    /**
     * Запрос по Rack
     * 
     * @param array $params
     * @return query
     */
    public function queryAllRack($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select( 
            'CASE WHEN c.id > 0 and s.id > 0 THEN CONCAT_WS(\'-\', r.id, s.id, c.id) '
            . 'WHEN s.id > 0 THEN CONCAT_WS(\'-\', r.id, s.id) '
            . 'ELSE r.id END as id, ' 
            . 'CASE WHEN c.id > 0 and s.id > 0 THEN CONCAT_WS(\'-\', r.id, s.id) '
            . 'WHEN s.id > 0 THEN r.id '
            . 'ELSE 0 END as pid, ' 
            . 'CASE WHEN c.id > 0 and s.id > 0 THEN c.name '
            . 'WHEN s.id > 0 THEN s.name ELSE r.name END as name, ' 
            . 'CASE WHEN c.id > 0 and s.id > 0 THEN c.comment '
            . 'WHEN s.id > 0 THEN s.comment ELSE r.comment END as comment, ' 
            . 'CASE WHEN c.id > 0 and s.id > 0 THEN \'cell\' '
            . 'WHEN s.id > 0 THEN \'shelf\' ELSE \'rack\' END as tbl, ' 
            . 'CASE WHEN c.id > 0 and s.id > 0 THEN c.id '
            . 'WHEN s.id > 0 THEN s.id ELSE r.id END as rid ' 
            )
            ->from(Rack::class, 'r')
            ->join('r.shelfs', 's', 'WITH')    
            ->leftJoin('s.cells', 'c')    
                ;
        
        if (is_array($params)){
            if (isset($params['sort'])){
                $queryBuilder->orderBy($params['sort'], $params['order']);
            }            
            if (!empty($params['officeId'])){
                $office = $entityManager->getRepository(Office::class)
                        ->findOneById($params['officeId']);
                if ($office){
                    $queryBuilder->andWhere('r.office = ?2')
                            ->setParameter('2', $office->getId());
                }
            }
        }
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }    
    
    public function findAllRack($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $result = [];
        
        $queryBuilder->select('r, s, c')
            ->from(Rack::class, 'r')
            ->leftJoin('r.shelfs', 's')    
            ->leftJoin('s.cells', 'c')    
                ;
        
        if (is_array($params)){
            if (isset($params['sort'])){
                $queryBuilder->orderBy('r.'.$params['sort'], $params['order'])
                    ->addOrderBy('s.'.$params['sort'], $params['order'])
                    ->addOrderBy('c.'.$params['sort'], $params['order'])
                        ;
            }            
            if (!empty($params['officeId'])){
                $office = $entityManager->getRepository(Office::class)
                        ->findOneById($params['officeId']);
                if ($office){
                    $queryBuilder->andWhere('r.office = ?2')
                            ->setParameter('2', $office->getId());
                }
            }
        }
        
        $data = $queryBuilder->getQuery()->getResult(2);
        
//        var_dump($data); exit;
        
        foreach ($data as $row){
            $rackId = $row['id'];
            $rack = [
                'id' => $rackId,
                'code' => $row['code'],
                'pid' => 0,
                'name' => $row['name'],
                'comment' => $row['comment'],
                'tbl' => 'rack',
                'rid' => $row['id'],
                'foldCount' => $row['foldCount'],
            ];
            
            $result[] = $rack;
            
            if (!empty($row['shelfs'])){
                foreach ($row['shelfs'] as $rowShelf){
                    $shelfId = $rackId.'-'.$rowShelf['id'];
                    $shelf = [
                        'id' => $shelfId,
                        'code' => $rowShelf['code'],
                        'pid' => $rackId,
                        'name' => $rowShelf['name'],
                        'comment' => $rowShelf['comment'],
                        'tbl' => 'shelf',
                        'rid' => $rowShelf['id'],
                        'foldCount' => $rowShelf['foldCount'],
                    ];                
                    
                    $result[] = $shelf;
                    
                    if (!empty($rowShelf['cells'])){
                        foreach ($rowShelf['cells'] as $rowCell){
                            $cellId = $shelfId.'-'.$rowCell['id'];
                            $cell = [
                                'id' => $cellId,
                                'code' => $rowCell['code'],
                                'pid' => $shelfId,
                                'name' => $rowCell['name'],
                                'comment' => $rowCell['comment'],
                                'tbl' => 'cell',
                                'rid' => $rowCell['id'],
                                'foldCount' => $rowCell['foldCount'],
                            ];                

                            $result[] = $cell;
                        }    
                    }
                }    
            }
        }

        return $result;
    }    
    
    /**
     * остаток в месте
     * @param Fold $fold
     */
    public function goodFoldRest($fold)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('sum(f.rest) as rest')
            ->from(Fold::class, 'f')
            ->setMaxResults(1)
            ->andWhere('f.office = :office')
            ->setParameter('office', $fold->getOffice()->getId())    
            ->andWhere('f.rack = :rack')
            ->setParameter('rack', $fold->getRack()->getId())
            ->andWhere('f.status = :status')
            ->setParameter('status', Fold::STATUS_ACTIVE)                
                ;
        
        if ($fold->getShelf()){
            $queryBuilder->andWhere('f.shelf = :shelf')
                ->setParameter('shelf', $fold->getShelf()->getId());
        }        
        if ($fold->getCell()){
            $queryBuilder->andWhere('f.cell = :cell')
                ->setParameter('cell', $fold->getCell()->getId());
        }        
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        
        if ($result){
            return $result['rest'];
        }    
        
        return 0;
    }
}
