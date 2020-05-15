<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\AutoDbResponse;
/**
 * Description of ExternalRepository
 *
 * @author Daddy
 */
class ExternalRepository extends EntityRepository
{

    /**
     * Добавить новую запись в auto_db_response
     * @param string $uri
     * @param string $response
     */
    public function insertAutoDbResponse($uri, $response)
    {
        $url = mb_strtoupper(trim($uri), 'UTF-8');
        $resp = $response;
        $respMd5 = mb_strtoupper(trim($response), 'UTF-8');
        $data = [
            'uri' => $url,
            'uri_md5' => md5($url),
            'response' => $resp,
            'response_md5' => md5($respMd5),
            'date_created' => date('Y-m-d'),
        ];
                
        $this->getEntityManager()->getConnection()->insert('auto_db_response', $data);        
        
        return;
    }        

    /**
     * Обновить запись в auto_db_response
     * @param string $uri
     * @param string $response
     */
    public function updateAutoDbResponse($uri, $response)
    {
        $url = mb_strtoupper(trim($uri), 'UTF-8');
        $resp = $response;
        $respMd5 = mb_strtoupper(trim($response), 'UTF-8');
        $data = [
            'response' => $resp,
            'response_md5' => md5($respMd5),
            'date_created' => date('Y-m-d'),
        ];
                
        $this->getEntityManager()->getConnection()->update('auto_db_response', $data, ['uri_md5' => md5($url)]); 
        
        return;
    }     
    
    /**
     * Колиество запросов за сегодня
     * @return integer
     */
    public function getAutoDbResponseTodayCount()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('adr.id')
            ->from(AutoDbResponse::class, 'adr')
            ->where('adr.dateCreated >= ?1')
            ->setParameter('1', date('Y-m-d'))
                ;
        
        return count($queryBuilder->getQuery()->getResult());                    
    }
    
    /**
     * Удаление старых записей
     * @param string $uriMd5 Description
     */
    public function deleteOld($uriMd5 = null)
    {
        $dateAgo = new \DateTime('3 month ago');
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->delete(AutoDbResponse::class, 'adr')
            ->where('adr.dateCreated < ?1')
            ->setParameter('1', date("Y-m-d", $dateAgo->getTimestamp()))
                ;
        if ($uriMd5){
            $queryBuilder->andWhere('adr.uriMd5 = ?2')
                    ->setParameter('2', $uriMd5)
                    ;
        }
        
        return $queryBuilder->getQuery()->getResult();                    
    }
}
