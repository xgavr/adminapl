<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\Contact;
use Application\Entity\Email;
use Admin\Filter\EmailFromStr;
use Laminas\Validator\EmailAddress;

/**
 * Description of ContactRepository
 *
 * @author Daddy
 */
class ContactRepository extends EntityRepository
{
    /**
     * Получить тип адреса
     * @param string $emailStr
     * @return int
     */
    public function emailType($emailStr)
    {
        $emailFilter = new EmailFromStr();
        $email = $emailFilter->filter($emailStr);

        $emailValidator = new EmailAddress();
        if ($emailValidator->isValid($email)){
            
            $mail = $this->getEntityManager()->getRepository(Email::class)
                    ->findOneByName($email);

            if ($mail){
                return $mail->getType();
            }
        
            $parts = explode("@", $email); 
            $domain = $parts[1];

            $entityManager = $this->getEntityManager();
            $queryBuilder = $entityManager->createQueryBuilder();
            $queryBuilder->select('e')
                    ->from(Email::class, 'e')
                    ->where('e.name like ?1')
                    ->setParameter('1', "%@$domain")
                    ;

            $data = $queryBuilder->getQuery()->getResult();
            if (count($data)){
                $types = [];
                foreach ($data as $mail){
                    $types[$mail->getType()] = $mail->getType();
                }
    //            var_dump(count($types));

                if (count($types) == 1){
                    return $mail->getType();
                }
            }   
        }    
        
        return Email::EMAIL_UNKNOWN;
    }

    /**
     * Выборка для формы
     * 
     * @param array params
     */
    public function formFind($params)
    {
        $contact = null;
        if (!empty($params['contact'])){
            $contact = $params['contact'];
        }

        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('u')
            ->from(Contact::class, 'u')
            ->where('u.id = ?1')    
            ->setParameter('1', -1)    
                ;
        if ($contact){
            $queryBuilder->setParameter(1, $contact->getId());
        }

        return $queryBuilder->getQuery()->getResult();       
    }
    
    /**
     * Запрос по поиска
     * 
     * @param array $params
     * @return object
     */
    public function liveSearch($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c.id, c.name, p.name as phone')
            ->from(Contact::class, 'c')
            ->join('c.phones', 'p')
            ->where('u.id = 0')    
                ;
//        var_dump($params); exit;
        if (is_array($params)){
            if (isset($params['search'])){
                $q = preg_replace('#[^0-9]#', '', $params['search']);
                if ($q){
                    $queryBuilder
                        ->where('p.name like :code')                           
                        ->setParameter('code', '%'.$q.'%')    
                            ;
                }    
            }
            if (isset($params['limit'])){
                $queryBuilder->setMaxResults($params['limit']);
            }
            if (isset($params['sort'])){
                $queryBuilder->orderBy('c.'.$params['sort'], $params['order']);                
            }            
        }
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }    
 
    /**
     * Выборка контактов 
     * @return type
     */
    public function findAllContact()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select("c")
            ->from(Contact::class, 'c')
                ;
                
//var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }        
    
}