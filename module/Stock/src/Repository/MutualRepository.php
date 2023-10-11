<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Repository;

use Doctrine\ORM\EntityRepository;
use Stock\Entity\Mutual;
use Stock\Entity\Retail;
use Application\Entity\Supplier;
use Company\Entity\Legal;
use Stock\Entity\Ptu;
use Stock\Entity\Vtp;
use Stock\Entity\Register;
use Stock\Entity\Movement;
use Application\Entity\Contact;
use Company\Entity\Contract;

/**
 * Description of MutualRepository
 *
 * @author Daddy
 */
class MutualRepository extends EntityRepository{
    
    /**
     * Удаление записей взаиморасчетов
     * 
     * @param string $docKey
     */
    public function removeDocMutuals($docKey)
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $qb = $entityManager->createQueryBuilder();
        $qb->select('m')
                ->from(Mutual::class, 'm')
                ->where('m.docKey = ?1')
                ->setParameter('1', $docKey)
                ;
        $mutuals = $qb->getQuery()->getResult();
        
        foreach ($mutuals as $mutual){
            $connection->delete('mutual', ['id' => $mutual->getId()]);
        }
        
        return;
    }

    /**
     * Удаление записей взаиморасчетов розницы
     * 
     * @param string $docKey
     */
    public function removeOrderRetails($docKey)
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $qb = $entityManager->createQueryBuilder();
        $qb->select('r')
                ->from(Retail::class, 'r')
                ->where('r.docKey = ?1')
                ->setParameter('1', $docKey)
                ;
        $retails = $qb->getQuery()->getResult();
        
        foreach ($retails as $retail){
            $connection->delete('retail', ['id' => $retail->getId()]);
        }
        
        return;
    }

    /**
     * Обновить баланс договора
     * @param array $data
     */
    public function  updateContractBalance($data)
    {
        if (!empty($data['contract_id'])){
            if (is_numeric($data['contract_id'])){
                $entityManager = $this->getEntityManager();
                $queryBuilder = $entityManager->createQueryBuilder();

                $queryBuilder->select("sum(m.amount) as total")
                        ->from(Mutual::class, 'm')
                        ->where('m.contract = :contract')
                        ->setParameter('contract', $data['contract_id'])
                        ;

                $result = $queryBuilder->getQuery()->getOneOrNullResult();
                
                $entityManager->getConnection()
                        ->update('contract', ['balance' => (float) $result['total']], ['id' => $data['contract_id']]);
            }    
        }    
        
        return;
    }
    
    /**
     * Добавление записей взаиморасчетов
     * 
     * @param array $data
     */
    public function insertMutual($data)
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $connection->insert('mutual', $data);
        
        $this->updateContractBalance($data);
        
        return;
    }
    
    /**
     * Добавление записей взаиморасчетов
     * 
     * @param array $data
     */
    public function insertRetail($data)
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $connection->insert('retail', $data);
        return;
    }
    
    /**
     * Сумма поставок юрлица
     * 
     * @param Legal $legal
     */
    public function legalAmount($legal)
    {
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();
        $qb->select('sum(m.amount) as amountSum')
                ->from(Mutual::class, 'm')
                ->where('m.legal = ?1')
                ->setParameter('1', $legal->getId())
                ;
        $data = $qb->getQuery()->getResult();
        foreach ($data as $row){
            return $row['amountSum'];
        }

        return 0;
    }
    
    /**
     * Сумма поставок юрлица
     * 
     * @param Legal $legal
     */
    public function ptuAmount($legal)
    {
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();
        $qb->select('sum(p.amount) as amountSum')
                ->from(Ptu::class, 'p')
                ->where('p.legal = ?1')
                ->setParameter('1', $legal->getId())
                ->andWhere('p.status = ?2')
                ->setParameter('2', Ptu::STATUS_ACTIVE)
                ;
        $data = $qb->getQuery()->getResult();
        foreach ($data as $row){
            return $row['amountSum'];
        }

        return 0;
    }
    
    /**
     * Сумма возвратов юрлица
     * 
     * @param Legal $legal
     */
    public function vtpAmount($legal)
    {
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();
        $qb->select('sum(v.amount) as amountSum')
                ->from(Vtp::class, 'v')
                ->join('v.ptu', 'p')
                ->where('p.legal = ?1')
                ->setParameter('1', $legal->getId())
                ->andWhere('v.status = ?2')
                ->setParameter('2', Vtp::STATUS_ACTIVE)
                ;
        $data = $qb->getQuery()->getResult();
        foreach ($data as $row){
            return $row['amountSum'];
        }

        return 0;
    }
    
    /**
     * Сумма поставок поставщика
     * 
     * @param Supplier $supplier
     */
    public function supplierAmount($supplier)
    {
        $result = 0;
        $legalContact = $supplier->getLegalContact();        
        foreach($legalContact->getLegals() as $legal){
            $result += $this->ptuAmount($legal);
            $result -= $this->vtpAmount($legal);
        }
        return $result;
    }
 
    /**
    * Остаток на момент времени
    * @param integer $clientId
     *@param integer $docType 
     *@param integer $docId 
     * @param integer $companyId
    * @return integer
    */
    public function clientStampRest($clientId, $docType, $docId, $companyId = null)
    {
        $entityManager = $this->getEntityManager();
        
        $register = $entityManager->getRepository(Register::class)
                ->findOneBy(['docType' => $docType, 'docId' => $docId]);
                
        if ($register){
            $qb = $entityManager->createQueryBuilder();
            $qb->select('sum(r.amount) as rSum')
                    ->from(Retail::class, 'r')
                    ->join('r.contact', 'c')
                    ->where('c.client = ?1')
                    ->andWhere('r.docStamp <= ?2') 
                    ->andWhere('r.docStamp > 0')
                    ->andWhere('r.status = :status')
                    ->setParameter('1', $clientId)
                    ->setParameter('2', $register->getDocStamp())
                    ->setParameter('status', Retail::STATUS_ACTIVE)
                    ;

            if (!empty($companyId)){
                if (is_numeric($companyId)){
                    $qb->andWhere('r.company = ?4');
                    $qb->setParameter('4', $companyId);
                }    
            }

            $result = $qb->getQuery()->getOneOrNullResult();

            return $result['rSum'];
        }
        return;
    }     
    
    /**
     * Текущий баланс контрагентов
     * @param array $params
     * @param string $alias
     */
    public function mutualBalanceQb($params = null, $alias = 'mm') 
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select("sum($alias.amount) as total")
                ->from(Mutual::class, $alias)
                ->join("$alias.company", $alias.'mc')
                ->join("$alias.legal", $alias.'ml')
                ->join("$alias.contract", $alias.'mct')
                ->join($alias.'ml.contacts', $alias.'mcn')
                ->andWhere("$alias.status = ".Mutual::STATUS_ACTIVE)
                ->andWhere($alias."mcn.status = ".Contact::STATUS_LEGAL)
                //->setParameter('status', Mutual::STATUS_ACTIVE)
                
                ;
        
        if (is_array($params)){            
            if (!empty($params['supplierBalance'])){
                $queryBuilder->addSelect($alias.'ms')
                        ->join($alias.'mcn.supplier', $alias.'ms')
                        ->addGroupBy($alias.'mcn.supplier')
                        ;
            }
            if (!empty($params['supplierId'])){
                $queryBuilder->andWhere($alias.'mcn.supplier = :supplier')
                        ->setParameter('supplier', $params['supplierId'])
                        ;
            }
            if (!empty($params['companyId'])){
                $queryBuilder->andWhere("$alias.company = :company")
                        ->setParameter('company', $params['companyId'])
                        ;
            }
            if (!empty($params['legalId'])){
                $queryBuilder->andWhere("$alias.legal = :legal")
                        ->setParameter('legal', $params['legalId'])
                        ;
            }
            if (!empty($params['contractId'])){
                $queryBuilder->andWhere("$alias.contract = :contract")
                        ->setParameter('contract', $params['contractId'])
                        ;
            }
            if (!empty($params['docStamp'])){
                $queryBuilder->andWhere("$alias.docStamp <= :docStamp")
                        ->setParameter('docStamp', $params['docStamp'])
                        ;
            }
            if (!empty($params['startDate'])){
                $queryBuilder->andWhere("$alias.dateOper >= :startDate")
                        ->setParameter('startDate', $params['startDate'])
                        ;
            }
            if (!empty($params['endDate'])){
                $queryBuilder->andWhere("$alias.dateOper <= :endDate")
                        ->setParameter('endDate', $params['endDate'])
                        ;
            }
            if (!empty($params['turnover'])){
                $queryBuilder
                    ->addSelect("sum(case "
                            . "when $alias.docType=".Movement::DOC_PTU." then $alias.amount "
                            . "when $alias.docType=".Movement::DOC_VTP." then $alias.amount "
                            . "else 0 end) as outTotal")
                    ->addSelect("sum(case "
                            . "when $alias.docType=".Movement::DOC_PTU." then 0 "
                            . "when $alias.docType=".Movement::DOC_VTP." then 0 "
                            . "else $alias.amount end) as inTotal")
                        ;                
            }
            if (!empty($params['endBalance'])){
                
                $qbParams = $params;
                unset($qbParams['endBalance']);
                unset($qbParams['startDate']);
                $qbb = $this->mutualBalanceQb($qbParams, 'mmm');
                $qbb->resetDQLPart('select');
                $qbb->addSelect('sum(mmm.amount) as sumTotal');
                
                $queryBuilder->addSelect('('. $qbb->getQuery()->getDQL().') as endTotal');
            }
            if (!empty($params['groupContract'])){
                $queryBuilder->addSelect($alias.'mct')
                        ->addGroupBy("$alias.contract")
                        ;
            }
            if (!empty($params['groupLegal'])){
                $queryBuilder->addSelect($alias.'ml')
                        ->addGroupBy("$alias.legal")
                        ;
            }
            if (!empty($params['groupCompany'])){
                $queryBuilder->addSelect($alias.'mc')
                        ->addGroupBy("$alias.company")
                        ;
            }
        }
        
        return $queryBuilder;                        
    }    
    
    
    /**
     * Текущий баланс контрагентов
     * @param array $params
     */
    public function mutualBalance($params = null)
    {
        $qb = $this->mutualBalanceQb($params);        
        
//        var_dump($qb->getQuery()->getSQL());
        return $qb->getQuery();
    }

    /**
     * Движение контрагентов
     * @param array $params
     */
    public function mutuals($params = null) 
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('m, l, c, ct, cd, rv, cash, user')
            ->from(Mutual::class, 'm')
            ->join('m.company', 'c')
            ->join('m.legal', 'l')
            ->join('m.contract', 'ct')
            ->join('l.contacts', 'cn')
            ->leftJoin('m.cashDoc', 'cd')
            ->leftJoin('cd.cash', 'cash')
            ->leftJoin('cd.user', 'user')
            ->leftJoin('m.reviseDoc', 'rv')
            ->orderBy('m.docStamp', 'DESC')
            ->andWhere('cn.status = :contactStatus')
            ->setParameter(':contactStatus', Contact::STATUS_LEGAL)    
                ;
        
        if (is_array($params)){
            if (!empty($params['startDate'])){
                $queryBuilder->andWhere('m.dateOper >= :startDate')
                        ->setParameter('startDate', $params['startDate']);
            }
            if (!empty($params['endDate'])){
                $queryBuilder->andWhere('m.dateOper <= :endDate')
                        ->setParameter('endDate', $params['endDate']);
            }
            if (!empty($params['supplierId'])){
                if (is_numeric($params['supplierId'])){
                    $queryBuilder
                        ->andWhere('cn.supplier = :supplier')
                        ->setParameter('supplier', $params['supplierId'])
                            ;
                }    
            }            
            if (!empty($params['companyId'])){
                if (is_numeric($params['companyId'])){
                    $queryBuilder
                        ->andWhere('m.company = :company')
                        ->setParameter('company', $params['companyId'])
                            ;
                }    
            }            
            if (!empty($params['legalId'])){
                if (is_numeric($params['legalId'])){
                    $queryBuilder
                        ->andWhere('m.legal = :legal')
                        ->setParameter('legal', $params['legalId'])
                            ;
                }    
            }            
            if (!empty($params['contractId'])){
                if (is_numeric($params['contractId'])){
                    $queryBuilder
                        ->andWhere('m.contract = :contract')
                        ->setParameter('contract', $params['contractId'])
                            ;
                }    
            }            
            if (!empty($params['order'])){
                $queryBuilder->orderBy('m.docStamp', $params['order']);
            }
        }

//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery();
    }

    
    /**
     * Движение контрагентов
     * @param array $params
     */
    public function mutualsCount($params = null) 
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('count(m.id) as mutualCount')
            ->from(Mutual::class, 'm')
            ->join('m.company', 'c')
            ->join('m.legal', 'l')
            ->join('m.contract', 'ct')
            ->join('l.contacts', 'cn')
            ->andWhere('cn.status = :contactStatus')
            ->setParameter(':contactStatus', Contact::STATUS_LEGAL)    
                ;
        
        if (is_array($params)){
            if (!empty($params['startDate'])){
                $queryBuilder->andWhere('m.dateOper >= :startDate')
                        ->setParameter('startDate', $params['startDate']);
            }
            if (!empty($params['endDate'])){
                $queryBuilder->andWhere('m.dateOper <= :endDate')
                        ->setParameter('endDate', $params['endDate']);
            }
            if (!empty($params['supplierId'])){
                if (is_numeric($params['supplierId'])){
                    $queryBuilder
                        ->andWhere('cn.supplier = :supplier')
                        ->setParameter('supplier', $params['supplierId'])
                            ;
                }    
            }            
            if (!empty($params['companyId'])){
                if (is_numeric($params['companyId'])){
                    $queryBuilder
                        ->andWhere('m.company = :company')
                        ->setParameter('company', $params['companyId'])
                            ;
                }    
            }            
            if (!empty($params['legalId'])){
                if (is_numeric($params['legalId'])){
                    $queryBuilder
                        ->andWhere('m.legal = :legal')
                        ->setParameter('legal', $params['legalId'])
                            ;
                }    
            }            
            if (!empty($params['contractId'])){
                if (is_numeric($params['contractId'])){
                    $queryBuilder
                        ->andWhere('m.contract = :contract')
                        ->setParameter('contract', $params['contractId'])
                            ;
                }    
            }            
        }

//        var_dump($queryBuilder->getQuery()->getSQL());
        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result['mutualCount'];        
    }    
    
    /**
     * Сменить флаг сверки
     * @param Mutual $mutual
     * @param int $check
     */
    public function changeRevise($mutual, $check)
    {
        $mutual->setRevise($check);        
        $entityManager = $this->getEntityManager();
        $entityManager->persist($mutual);
        $entityManager->flush();
        
        return;
    }
    
    /**
     * Текущие остатки по договрам
     * @param array $params
     */
    public function contractBalances($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c, l, cm, cn, s')
            ->from(Contract::class, 'c')
            ->join('c.company', 'cm')
            ->join('c.legal', 'l')
            ->join('l.contacts', 'cn')
            ->join('cn.supplier', 's')    
            ->andWhere('cn.status = :contactStatus')
            ->setParameter(':contactStatus', Contact::STATUS_LEGAL)    
                ;
        
        if (is_array($params)){
            if (!empty($params['supplierId'])){
                if (is_numeric($params['supplierId'])){
                    $queryBuilder
                        ->andWhere('cn.supplier = :supplier')
                        ->setParameter('supplier', $params['supplierId'])
                            ;
                }    
            }            
            if (!empty($params['companyId'])){
                if (is_numeric($params['companyId'])){
                    $queryBuilder
                        ->andWhere('c.company = :company')
                        ->setParameter('company', $params['companyId'])
                            ;
                }    
            }            
            if (!empty($params['legalId'])){
                if (is_numeric($params['legalId'])){
                    $queryBuilder
                        ->andWhere('c.legal = :legal')
                        ->setParameter('legal', $params['legalId'])
                            ;
                }    
            }            
            if (!empty($params['contractId'])){
                if (is_numeric($params['contractId'])){
                    $queryBuilder
                        ->andWhere('c.id = :contract')
                        ->setParameter('contract', $params['contractId'])
                            ;
                }    
            }            
            if (!empty($params['pay'])){
                if (is_numeric($params['pay'])){
                    $queryBuilder
                        ->andWhere('c.pay = :pay')
                        ->setParameter('pay', $params['pay'])
                            ;
                }    
            }            
            if (!empty($params['sort'])){
                $queryBuilder
                    ->orderBy('c.'.$params['sort'], $params['order'])
                        ;
            }            
        }
        
        return $queryBuilder->getQuery();
    }
}