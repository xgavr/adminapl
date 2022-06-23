<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Repository;

use Doctrine\ORM\EntityRepository;
use Stock\Entity\Register;
use Stock\Entity\RegisterVariable;
use Stock\Entity\Movement;
use Stock\Entity\Pt;
use Stock\Entity\Ptu;
use Stock\Entity\Vtp;
use Stock\Entity\Ot;
use Stock\Entity\St;
use Stock\Entity\Vt;
use Application\Entity\Order;


/**
 * Description of RegisterRepository
 *
 * @author Daddy
 */
class RegisterRepository extends EntityRepository
{
    
    /**
     * Обновить дату последовательности
     * 
     * @param date $dateVar
     * @param integer $varType
     * @param integer $varId
     * @return null
     */
    private function updateVariable($dateVar, $varType, $varId)
    {
        $entityManager = $this->getEntityManager();
        $var = $entityManager->getRepository(RegisterVariable::class)
                ->findOneBy([]);
        if (!$var){
            $var = new RegisterVariable();
            $var->setDateVar($dateVar);
            $var->setVarId($varId);
            $var->setVarType($varType);
            $entityManager->persist($var);
            $entityManager->flush($var);
        }
        
        if ($var->getDateVar() > $dateVar){
            $var->setDateVar($dateVar);
            $var->setVarId($varId);
            $var->setVarType($varType);
            $entityManager->persist($var);
            $entityManager->flush($var);            
        }        
        
        return;
    }
    
    /**
     * Регистрация документа
     * 
     * @param date $dateOper
     * @param integer $docType
     * @param integer $docId
     */
    private function register($dateOper, $docType, $docId)
    {
        $entityManager = $this->getEntityManager();
        $reg = $entityManager->getRepository(Register::class)
                ->findOneBy(['docId' => $docId, 'docType' => $docType]);

        if (!$reg){
            $reg = new Register();
            $reg->setDocId($docId);
            $reg->setDocType($docType);            
            $reg->setDateOper($dateOper);
            $entityManager->persist($reg);
            $entityManager->flush($reg);
        }
        
        if ($reg->getDateOper() != $dateOper){
            $reg->setDateOper($dateOper);
            $entityManager->persist($reg);
            $entityManager->flush($reg);            
        }
        
        $this->updateVariable($dateOper, $docType, $docId);
        
        return;        
    }
    
    
    /**
     * Регистриция Pt
     * 
     * @param Pt $pt
     */
    public function ptRegister($pt)
    {
        $dateOper = date('Y-m-d 12:00:00', strtotime($pt->getDocDate()));
        $this->register($dateOper, Movement::DOC_PT, $pt->getId());
        return;
    }
    
    /**
     * Регистриция Ptu
     * 
     * @param Ptu $ptu
     */
    public function ptuRegister($ptu)
    {
        $dateOper = date('Y-m-d 00:01:00', strtotime($ptu->getDocDate()));
        $this->register($dateOper, Movement::DOC_PTU, $ptu->getId());
        return;
    }    
    
    /**
     * Регистриция Vtp
     * 
     * @param Vtp $vtp
     */
    public function vtpRegister($vtp)
    {
        $dateOper = date('Y-m-d 23:01:00', strtotime($vtp->getDocDate()));
        $this->register($dateOper, Movement::DOC_VTP, $vtp->getId());
        return;
    }    

    /**
     * Регистриция Ot
     * 
     * @param Ot $ot
     */
    public function otRegister($ot)
    {
        $dateOper = date('Y-m-d 00:01:00', strtotime($ot->getDocDate()));
        $this->register($dateOper, Movement::DOC_OT, $ot->getId());
        return;
    }    

    /**
     * Регистриция St
     * 
     * @param St $st
     */
    public function stRegister($st)
    {
        $dateOper = date('Y-m-d 00:01:00', strtotime($st->getDocDate()));
        $this->register($dateOper, Movement::DOC_ST, $st->getId());
        return;
    }    

    /**
     * Регистриция Vt
     * 
     * @param Vt $vt
     */
    public function vtRegister($vt)
    {
        $dateOper = date('Y-m-d 22:01:00', strtotime($vt->getDocDate()));
        $this->register($dateOper, Movement::DOC_VT, $vt->getId());
        return;
    }    

    /**
     * Регистриция Order
     * 
     * @param Order $order
     */
    public function orderRegister($order)
    {
        $dateOper = date('Y-m-d 21:01:00', strtotime($order->getDocDate()));
        $this->register($dateOper, Movement::DOC_ORDER, $order->getId());
        return;
    } 
    
    public function allRegister()
    {
        ini_set('memory_limit', '8192M');
        set_time_limit(0);
        
        $ptus = $this->getEntityManager()->getRepository(Ptu::class)
                ->findBy([]);
        foreach ($ptus as $ptu){
            $this->ptuRegister($ptu);
        }

        $ots = $this->getEntityManager()->getRepository(Ot::class)
                ->findBy([]);
        foreach ($ots as $ot){
            $this->otRegister($ot);
        }

        $pts = $this->getEntityManager()->getRepository(Pt::class)
                ->findBy([]);
        foreach ($pts as $pt){
            $this->ptRegister($pt);
        }

        $orders = $this->getEntityManager()->getRepository(Order::class)
                ->findBy([]);
        foreach ($orders as $order){
            $this->orderRegister($order);
        }

        $vts = $this->getEntityManager()->getRepository(Vt::class)
                ->findBy([]);
        foreach ($vts as $vt){
            $this->vtRegister($vt);
        }

        $vtps = $this->getEntityManager()->getRepository(Vtp::class)
                ->findBy([]);
        foreach ($vtps as $vtp){
            $this->vtpRegister($vtp);
        }

        $sts = $this->getEntityManager()->getRepository(St::class)
                ->findBy([]);
        foreach ($sts as $st){
            $this->stRegister($st);
        }
    }
}