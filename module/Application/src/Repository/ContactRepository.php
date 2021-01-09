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
            return $mail->getTypeAsString();
        }
        
        return 'Неизвестно';
    }

}