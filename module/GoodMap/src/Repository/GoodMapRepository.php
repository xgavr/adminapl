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
use GoodMap\Entity\FoldDoc;
use Application\Entity\Goods;


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
            if (!empty($params['rack'])){
                $queryBuilder->andWhere('r.id = :rack')
                        ->setParameter('rack', $params['rack']->getId());
            }
            if (!empty($params['shelf'])){
                $queryBuilder->andWhere('s.id = :shelf')
                        ->setParameter('shelf', $params['shelf']->getId());
            }
            if (!empty($params['cell'])){
                $queryBuilder->andWhere('c.id = :cell')
                        ->setParameter('cell', $params['cell']->getId());
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
                'status' => $row['status'],
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
                        'status' => $row['status'],
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
                                'status' => $row['status'],
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
     * @param array $params
     */
    public function goodMapRest($params)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('sum(f.quantity) as rest')
            ->from(Fold::class, 'f')
            ->setMaxResults(1)
            ->andWhere('f.good = :good')
            ->setParameter('good', $params['goodId'])    
            ->andWhere('f.office = :office')
            ->setParameter('office', $params['officeId'])    
            ->andWhere('f.rack = :rack')
            ->setParameter('rack', $params['rackId'])
            ->andWhere('f.status = :status')
            ->setParameter('status', Fold::STATUS_ACTIVE)                
                ;
        
        if (!empty($params['shelfId'])){
            if (is_numeric($params['shelfId'])){
                $queryBuilder->andWhere('f.shelf = :shelf')
                    ->setParameter('shelf', $params['shelfId']);
            }    
        }        
        if (!empty($params['cellId'])){
            if (is_numeric($params['cellId'])){
                $queryBuilder->andWhere('f.cell = :cell')
                    ->setParameter('cell', $params['cellId']);
            }    
        }        
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();
//        var_dump($params, $result);
        if (!empty($result['rest'])){
            return $result['rest'];
        }    
        
        return 0;
    }
    
    /**
     * остаток в месте
     * @param Fold $fold
     */
    public function goodFoldRest($fold)
    {
        return $this->goodMapRest([
            'goodId' => $fold->getGood()->getId(),
            'officeId' => $fold->getOffice()->getId(),
            'rackId' => $fold->getRackId(),
            'shelfId' => $fold->getShelfId(),
            'cellId' => $fold->getCellId(),
        ]);
    }
    
    /**
     * @param Office $office
     * @param Goods $good
     * @param date $docDate
     * 
     * @return FoldDoc
     */
    public function findLastFoldDoc($office, $good, $docDate)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('fd')
            ->from(FoldDoc::class, 'fd')
            ->andWhere('fd.office = :office')
            ->setParameter('office', $office->getId())
            ->andWhere('fd.good = :good')
            ->setParameter('good', $good->getId())
            ->andWhere('fd.docDate >= :docDate')
            ->setParameter('docDate', $docDate)
            ->setMaxResults(1)    
                ;
        
        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
