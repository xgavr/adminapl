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
use Stock\Entity\Vtp;
use Application\Entity\Order;
use Stock\Entity\Pt;
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
     * @param bool $short //сокращенный вывод
     * 
     * @return string
     */
    private function messageText($log, $short = true)
    {
        $entityManager = $this->getEntityManager();
        $ident = $log->getIdentFromLogKey();
        $message = $log->getMessageAsArray();
        $messages = [];
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
                    if ($tokenGroup){
                        $param = "ограничение <a href='/name/view-token-group/{$message['tokenGroup']}'>{$tokenGroup->getName()}</a>";
                    } else {
                        $param = "ограничение удаленная группа {$message['tokenGroup']}";                        
                    }   
                }
                if (!empty($message['office'])){
                    $office = $entityManager->getRepository(Office::class)
                            ->findOneById($message['office']);
                    $officeName = $office->getName();
                }
                $result = trim("$name $change");
                return $result;
                
            case 'vtp':
//                var_dump(Vtp::getStatusList()[$message['statusDoc']]);
                if ($message['status'] == Vtp::STATUS_RETIRED){
                    $messages[] = 'Удален';
                } else {
                    $messages[] = Vtp::getStatusDocList()[$message['statusDoc']];
                }    
                $messages[] = $message['comment'];
                return implode('; ', $messages);
                
            case 'ord':
                if (!empty($message['comment'])){
                    $messages[] = 'Комментарий: '.$message['comment']['comment'];
                } else {
//                    var_dump($message['aplId']);
                    $header = [];
                    $header[] = 'Номер АПЛ: '.(empty($message['aplId']) ? 'нет':$message['aplId']);
                    $header[] = 'Статус: '.(empty($message['status']) ? 'нет':Order::getStatusList()[$message['status']]);
                    $header[] = 'Сумма: '.(empty($message['amount']) ? 'нет':round($message['amount'], 2));
                    $header[] = 'Отгрузка: '.(empty($message['shipmentDate']) ? 'нет':$message['shipmentDate']);
                    $header[] = 'Контакт: '.(empty($message['contact']) ? 'нет':$message['contact']);
                    if (!empty($message['goods'])){
                        $goods = [];
                        if ($short){
                            $header[] = 'Товаров: '.count($message['goods']);                            
                        } else {
                            foreach ($message['goods'] as $good){
                                $goods[] = $good['rowNo'];
                                $goods[] = $good['good'];
                                $goods[] = 'Количество: '.$good['num'];
                                $goods[] = 'Цена: '.$good['price'];
                            }            
                            $messages[] = implode('<br/> ', $goods);
                        }    
                    }                    
                    $messages[] = implode('; ', $header);
//                    $header[] = '<br/>';
                }
//                $messages[] = $message['comment'];
                return implode('; ', $messages);
                
            case 'pt':
                $header = [];
                $header[] = 'Номер АПЛ: '.(empty($message['aplId']) ? 'нет':$message['aplId']);
                $header[] = 'Статус: '.(empty($message['status']) ? 'нет':Pt::getStatusList()[$message['status']]);
                $header[] = 'Сумма: '.(empty($message['amount']) ? 'нет':round($message['amount'], 2));
                $header[] = 'Товаров: '.count($message['goods']);                            

                if (!$short){
                    $goods = [];
                    foreach ($message['goods'] as $row){
                        $goods[] = implode(';', ['<b>'.$row['rowNo'].'</b>', $row['good'], $row['quantity']]);
                    }   
                    $header[] = '<br/><small class="retired-muted">'.implode('; ', $goods).'</small>';
                }    
                $messages[] = implode('; ', $header);

                return implode('; ', $messages);

            default: break;
        }
        
        return;
    }
    
    /**
     * Запрос по типу документа
     * 
     * @param string $docType
     * @param array $options
     */
    public function queryByDocType($docType, $options = null)
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
        
        return $queryBuilder->getQuery();
    }    
        
    
    /**
     * Подготовить описание логов
     * 
     * @param array $data
     * @param bool $short //сокращенный вывод
     * 
     * @return array
     */
    public function logDescription($data, $short = true)
    {        
        $result = [];
        foreach ($data as $row){
            $result[$row->getId()] = [
                'id' => $row->getIdFromLogKey(),                
                'priority' => $row->getPriorityAsString(),                
                'status' => $row->getStatusAsString(),                
                'dateCreated' => date('Y-m-d H:i:s', strtotime($row->getDateCreated())),                
                'user' => ($row->getUser()) ?  $row->getUser()->getFullName():'Генератор',
                'message' => $this->messageText($row, $short),
            ];
        }
                
        return $result;
        
    }

    /**
     * Выбрать по типу документа
     * 
     * @param string $docType
     * @param array $options
     */
    public function findByDocType($docType, $options = null)
    {
        $query = $this->queryByDocType($docType, $options);
        $data = $query->getResult();        
        
        return $this->logDescription($data);        
    }
    
}