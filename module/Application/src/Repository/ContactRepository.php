<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\Conatact;
use Application\Entity\Email;
use Admin\Filter\EmailFromStr;

/**
 * Description of SupplierRepository
 *
 * @author Daddy
 */
class ContactRepository extends EntityRepository
{
    /**
     * Получить тип адреса
     * @param string $mail
     * @return int
     */
    public function emailType($email)
    {
        $emailFilter = new EmailFromStr();
        
        $mail = $this->getEntityManager()->getRepository(Email::class)
                ->findOneByName($emailFilter->filter($email));
        
        if ($mail){
            return $mail->getType();
        }
        
        $parts = explode("@",$mail); 
        $domain = $parts[1];
        
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('e')
                ->from(Email::class, 'e')
                ->where('e.name like ?1')
                ->setParameter('1', "*@$domain")
                ;
        
        $data = $queryBuilder->getQuery()->getResult();
        if (count($data)){
            $types = [];
            foreach ($data as $mail){
                $types[$mail->getType()] = $mail->getType();
            }
            
            if (count($types) == 1){
                return $types[0];
            }
        }   
        
        
        return Email::EMAIL_UNKNOWN;
    }

}