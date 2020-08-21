<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Repository;

use Doctrine\ORM\EntityRepository;
use Admin\Entity\Log;
use Application\Entity\Rate;
use Application\Entity\Producer;
use Application\Entity\GenericGroup;
use Application\Entity\TokenGroup;
use Company\Entity\Office;
//use User\Entity\User;


/**
 * Description of LogRepository
 *
 * @author Daddy
 */
class LogRepository extends EntityRepository{
    
    
    /**
     * Текст лога
     * 
     * @param Log $log
     * 
     * @return string
     */
    private function messageText($log)
    {
        $entityManager = $this->getEntityManager();
        $ident = $log->getIdentFromLogKey();
        $message = $log->getMessageAsArray();
        switch ($ident){
            case 'rate':
                $name = "<a href='/rate/view/{$log->getIdFromLogKey()}'>{$message['name']}</a>";
                if ($log->getStatus() == Log::STATUS_DELETE){
                    $name = $message['name'];
                }    
                $statusName = Rate::getStatusName($message['status']);
                $modeName = Rate::getModeName($message['mode']);
                $change = '';
                if (isset($message['change'])){
                    $change = "{$message['change']}";                    
                    if ($change > 0){
                        $change = "+{$message['change']}";                    
                    }    
                }
                $param = 'Для всех';
                if (!empty($message['producer'])){
                    $producer = $entityManager->getRepository(Producer::class)
                            ->findOneById($message['producer']);
                    $param = "ограничение <a href='/producer/view/{$message['producer']}'>{$producer->getName()}</a>";
                }
                if (!empty($message['genericGroup'])){
                    $genericGroup = $entityManager->getRepository(GenericGroup::class)
                            ->findOneById($message['genericGroup']);
                    $param = "ограничение <a href='/group/view/{$message['genericGroup']}'>{$genericGroup->getName()}</a>";
                }
                if (!empty($message['tokenGroup'])){
                    $tokenGroup = $entityManager->getRepository(TokenGroup::class)
                            ->findOneById($message['tokenGroup']);
                    $param = "ограничение <a href='/name/view-token-group/{$message['tokenGroup']}'>{$tokenGroup->getName()}</a>";
                }
                if (!empty($message['office'])){
                    $office = $entityManager->getRepository(Office::class)
                            ->findOneById($message['office']);
                    $officeName = $office->getName();
                }
                $result = trim("$name $change");
                return $result;
                break;
            default: break;
        }
        
        return;
    }
    
    /**
     * Выбрать по типу документа
     * 
     * @param string $docType
     * @param array $options
     */
    public function findByDocType($docType, $options = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('l')
                ->from(Log::class, 'l')
                ->where("l.logKey like '$docType:%'")
                ->orderBy('l.id', 'DESC')
                ;
        if (is_array($options)){
            if (isset($options['id'])){
                $queryBuilder->andWhere('l.logKey = ?1');
                $queryBuilder->setParameter('1', "$docType:{$options['id']}");
            }
            if (isset($options['limit'])){
                $queryBuilder->setMaxResults($options['limit']);
            }
        }
        
        $data = $queryBuilder->getQuery()->getResult();
        $result = [];
        foreach ($data as $row){
            $result[$row->getId()] = [
                'id' => $row->getIdFromLogKey(),                
                'priority' => $row->getPriorityAsString(),                
                'status' => $row->getStatusAsString(),                
                'dateCreated' => date('Y-m-d H:i:s', strtotime($row->getDateCreated())),                
                'user' => $row->getUser()->getFullName(),
                'message' => $this->messageText($row),
            ];
        }
                
        return $result;
        
    }
    
}