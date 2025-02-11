<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Application\Entity\GroupSite;
use Application\Entity\TokenGroup;


/**
 * Description of GroupSiteManager
 *
 * @author Daddy
 */
class GroupSiteManager
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
     * Добавить группу для сайта
     * 
     *
     * @param array $data
     * @return Comment
     */
    public function addGroupSite($data)
    {        
        $groupSite = new GroupSite();
        $groupSite->setCode($data['code'] ?? 0);
        $groupSite->setDescription($data['description'] ?? null);
        $groupSite->setGoodCount($data['goodCount'] ?? 0);
        $groupSite->setImage($data['image'] ?? null);
        $groupSite->setLevel($data['level'] ?? 0);
        $groupSite->setName($data['name']);
        $groupSite->setAplId($data['aplId'] ?? 0);
        $groupSite->setSort($data['sort'] ?? 0);
        $groupSite->setSlug($data['slug'] ?? null);
        $groupSite->setStatus($data['status'] ?? GroupSite::STATUS_ACTIVE);
        
        if (!empty($data['groupSite'])){
            if (is_numeric($data['groupSite'])){
                $data['groupSite'] = $this->entityManager->getRepository(GroupSite::class)
                        ->find($data['groupSite']);
                $groupSite->setSiteGroup($data['groupSite'] ?? null);
            }
            $groupSite->setLevel($data['groupSite']->getLevel() + 1);
        }
        
        $this->entityManager->persist($groupSite);
        $this->entityManager->flush($groupSite);
        
        $code = sprintf("%02d", $groupSite->getId());
        if (!empty($data['groupSite'])){
            $code = $data['groupSite']->getCode().'-'.$code;
        }
        
        $groupSite->setCode($code);
        
        $this->entityManager->persist($groupSite);
        $this->entityManager->flush($groupSite);
        
        return $groupSite;
    }
    
    /**
     * Обновить группу для сайта
     * 
     * @param GroupSite $groupSite
     * @param array $data
     * @return GroupSite
     */
    public function updateGroupSite($groupSite, $data)
    {
        
        $groupSite->setDescription($data['description'] ?? null);
        $groupSite->setGoodCount($data['goodCount'] ?? 0);
        $groupSite->setImage($data['image'] ?? null);
        $groupSite->setLevel($data['level'] ?? 0);
        $groupSite->setName($data['name']);
        $groupSite->setAplId($data['aplId'] ?? 0);
        $groupSite->setSort($data['sort'] ?? 0);
        $groupSite->setSlug($data['slug'] ?? null);
        $groupSite->setStatus($data['status'] ?? GroupSite::STATUS_ACTIVE);
        
        $code = sprintf("%02d", $groupSite->getId());
        
        if (!empty($data['groupSite'])){
            if (is_numeric($data['groupSite'])){
                $data['groupSite'] = $this->entityManager->getRepository(GroupSite::class)
                        ->find($data['groupSite']);
                $groupSite->setSiteGroup($data['groupSite'] ?? null);
            }
            $groupSite->setLevel($data['groupSite']->getLevel() + 1);
            $code = $data['groupSite']->getCode().'-'.$code;
        }
        
        $groupSite->setCode($code);
        
        $this->entityManager->persist($groupSite);
        $this->entityManager->flush($groupSite);
        
        return $groupSite;
    }
    
    /**
     * Удалить группу для сайта
     * 
     * @param GroupSite $groupSite
     */
    public function removeGroupSite($groupSite)
    {
        $childsCount = $this->entityManager->getRepository(GroupSite::class)
                ->count(['siteGroup' => $groupSite->getId()]);
        if ($childsCount){
            return false;
        }
        
        $tokenGroups = $this->entityManager->getRepository(TokenGroup::class)
                ->findBy(['groupSite' => $groupSite->getId()]);
        foreach ($tokenGroups as $tokenGroup){
            $tokenGroup->setGroupSite(null);
            $this->entityManager->persist($tokenGroup);
        }
        
        $this->entityManager->remove($groupSite);
        $this->entityManager->flush();
        
        return;
    }
    
}
