<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Application\Entity\Comment;
use Application\Entity\Order;
use Application\Entity\Client;
use User\Entity\User;

/**
 * Description of CommentManager
 *
 * @author Daddy
 */
class CommentManager
{
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Log manager.
     * @var \Admin\Service\LogManager
     */
    private $logManager;
    
    
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $logManager)
    {
        $this->entityManager = $entityManager;
        $this->logManager = $logManager;
    }
    
    public function currentUser()
    {
        return $this->logManager->currentUser();
    }
    
    /**
     * Добавить comment в заказ
     * 
     * @param Order $order
     * @param array $data
     * @return Comment
     */
    public function addOrderComment($order, $data)
    {        
        $comment = new Comment();
        $comment->setAplId((empty($data['aplId'])) ? null:$data['aplId']);
        $comment->setClient($order->getContact()->getClient());
        $comment->setComment(empty($data['comment']) ? null:$data['comment']);
        $comment->setDateCreated(empty($data['dateCreated']) ? date('Y-m-d H:i:s'):$data['dateCreated']);
        $comment->setOrder($order);
        $comment->setUser(empty($data['user']) ? $this->currentUser():$data['user']);
        
        $this->entityManager->persist($comment);
        $this->entityManager->flush($comment);
        
        return $comment;
    }
    
    /**
     * Добавить comment в client
     * 
     * @param Client $client
     * @param array $data
     * @return Comment
     */
    public function addClientComment($client, $data)
    {        
        $comment = new Comment();
        $comment->setAplId((empty($data['aplId'])) ? null:$data['aplId']);
        $comment->setClient($client);
        $comment->setComment(empty($data['comment']) ? null:$data['comment']);
        $comment->setDateCreated(empty($data['dateCreated']) ? date('Y-m-d H:i:s'):$data['dateCreated']);
        $comment->setOrder(null);
        $comment->setUser(empty($data['user']) ? $this->currentUser():$data['user']);
        
        $this->entityManager->persist($comment);
        $this->entityManager->flush($comment);
        
        return $comment;
    }
    
    /**
     * Обновить comment
     * 
     * @param Comment $comment
     * @param array $data
     * @return comment
     */
    public function updateComment($comment, $data)
    {
        $comment->setComment(empty($data['comment']) ? null:$data['comment']);
        
        $this->entityManager->persist($comment);
        $this->entityManager->flush($comment);
        
        return $comment;
    }
    
    /**
     * Удалить comment
     * 
     * @param Comment $comment
     */
    public function removeComment($comment)
    {
        
        $this->entityManager->remove($comment);
        $this->entityManager->flush($comment);
        
        return;
    }
    
}
