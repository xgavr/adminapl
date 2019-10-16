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
        $resp = mb_strtoupper(trim($response), 'UTF-8');
        $data = [
            'uri' => $url,
            'uri_md5' => md5($url),
            'response' => $resp,
            'response_md5' => md5($resp),
        ];
                
        $this->getEntityManager()->getConnection()->insert('auto_db_responce', $data);        
        
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
        $resp = mb_strtoupper(trim($response), 'UTF-8');
        $data = [
            'response' => $resp,
            'response_md5' => md5($resp),
        ];
                
        $this->getEntityManager()->getConnection()->update('auto_db_responce', $data, ['uri_md5' => md5($url)]); 
        
        return;
    }        
}
