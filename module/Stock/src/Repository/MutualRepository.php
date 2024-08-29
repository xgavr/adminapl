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
use Stock\Entity\Revision;
use User\Entity\User;
use Application\Entity\Client;
use Application\Entity\Order;
use Stock\Entity\Vt;

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
                        ->andWhere('m.status = :status')
                        ->setParameter('status', Mutual::STATUS_ACTIVE)
                        ;

                $result = $queryBuilder->getQuery()->getOneOrNullResult();
                
                $entityManager->getConnection()
                        ->update('contract', ['balance' => round($result['total'], 2)], ['id' => $data['contract_id']]);
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
        
        $revision = $entityManager->getRepository(Revision::class)
                ->findOneBy(['docKey' => $data['doc_key']]);
        if ($revision){
            if ($data['amount'] == $revision->getAmount()){
                $data['revise'] = Mutual::REVISE_OK;
                $data['revision_id'] = $revision->getId();
            }            
        }
        
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

        $contact = $entityManager->getRepository(Contact::class)
                ->find($data['contact_id']);
        if ($contact){
            if ($contact->getClient()){
                $entityManager->getRepository(Client::class)
                        ->updateBalance($contact->getClient());
            }    
        }    

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
     *@param string $docKey 
     * @param integer $companyId
     * @param integer $legalId
     * @param integer $contractId
    * @return integer
    */
    public function clientStampRest($clientId, $docKey, $companyId = null, 
            $legalId = null, $contractId = null)
    {
        $entityManager = $this->getEntityManager();
        
        $register = $entityManager->getRepository(Register::class)
                ->findOneBy(['docKey' => $docKey]);
                
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
            if (!empty($legalId)){
                if (is_numeric($legalId)){
                    $qb->andWhere('r.legal = :legal');
                    $qb->setParameter('legal', $legalId);
                }    
                if ($legalId == Client::RETAIL_ID){
                    $qb->andWhere('r.legal is null');
                }    
            }
            if (!empty($contractId)){
                if (is_numeric($contractId)){
                    $qb->andWhere('r.contract = :contract');
                    $qb->setParameter('contract', $contractId);
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
            if (!empty($params['status'])){
                if (is_numeric($params['status'])){
                    $queryBuilder
                        ->andWhere('m.status = :status')
                        ->setParameter('status', $params['status'])
                            ;
                }    
            }            
            if (!empty($params['docType'])){
                if (is_numeric($params['docType'])){
                    $queryBuilder
                        ->andWhere('m.docType = :docType')
                        ->setParameter('docType', $params['docType'])
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
            if (!empty($params['status'])){
                if (is_numeric($params['status'])){
                    $queryBuilder
                        ->andWhere('m.status = :status')
                        ->setParameter('status', $params['status'])
                            ;
                }    
            }            
            if (!empty($params['docType'])){
                if (is_numeric($params['docType'])){
                    $queryBuilder
                        ->andWhere('m.docType = :docType')
                        ->setParameter('docType', $params['docType'])
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
     * @param User $currentUser
     */
    public function changeRevise($mutual, $check, $currentUser)
    {
        $entityManager = $this->getEntityManager();
        
        $mutual->setRevision(null);        
        $mutual->setRevise($check);        
        $entityManager->persist($mutual);
        $entityManager->flush();
        
        $entityManager->getConnection()->delete('revision', ['doc_key' => $mutual->getDocKey()]);
        
        if ($check == Mutual::REVISE_OK){
            $revision = new Revision();
            $revision->setDateCreated(date('Y-m-d H:i:s'));
            $revision->setDocKey($mutual->getDocKey());
            $revision->setUser($currentUser);
            $revision->setAmount($mutual->getAmount());
            $revision->setDocId($mutual->getDocId());
            $revision->setDocStamp($mutual->getDocStamp());
            $revision->setDocType($mutual->getDocType());
            
            $entityManager->persist($revision);
//            $entityManager->flush();

            $mutual->setRevision($revision);
            $entityManager->persist($mutual);
            $entityManager->flush();
        }
        
        return;
    }
    
    /**
     * Заполнить ревизии
     */
    public function fillRevision($currentUser)
    {
        $entityManager = $this->getEntityManager();
        
        $mutuals = $entityManager->getRepository(Mutual::class)
                ->findBy(['revise' => Mutual::REVISE_OK]);
        
        
        foreach ($mutuals as $mutual){
            $this->changeRevise($mutual, $mutual->getRevise(), $currentUser);
        }
        
        return;
    }
    
    /**
     * Текущие остатки по договорам
     * @param array $params
     */
    public function contractBalances($params = null)
    {
        $entityManager = $this->getEntityManager();
        
        $subQb = $entityManager->createQueryBuilder();
        $subQb->select('min(contacts.id) as contactId')
                ->from(Legal::class, 'legal')
                ->join('legal.contacts', 'contacts')
                ->andWhere('contacts.status = :contactStatus')
                ->andWhere('legal.id = l.id')
                ->setMaxResults(1)
                ;
        

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c, l, cm, cn, s')
            ->distinct()    
            ->from(Contract::class, 'c')
            ->join('c.company', 'cm')
            ->join('c.legal', 'l')
            ->join('l.contacts', 'cn', 'WITH', 'cn.id = ('.$subQb->getQuery()->getDQL().')')
            ->join('cn.supplier', 's')    
            ->andWhere('cn.status = :contactStatus')
            ->setParameter(':contactStatus', Contact::STATUS_LEGAL)
                ;
        
        $notnull = true;
        if (is_array($params)){
            if (!empty($params['supplierId'])){
                if (is_numeric($params['supplierId'])){
                    $notnull = false;
                    $queryBuilder
                        ->andWhere('cn.supplier = :supplier')
                        ->setParameter('supplier', $params['supplierId'])
                            ;
                }    
            }            
            if (!empty($params['companyId'])){
                if (is_numeric($params['companyId'])){
                    $notnull = false;
                    $queryBuilder
                        ->andWhere('c.company = :company')
                        ->setParameter('company', $params['companyId'])
                            ;
                }    
            }            
            if (!empty($params['legalId'])){
                if (is_numeric($params['legalId'])){
                    $notnull = false;
                    $queryBuilder
                        ->andWhere('c.legal = :legal')
                        ->setParameter('legal', $params['legalId'])
                            ;
                }    
            }            
            if (!empty($params['contractId'])){
                if (is_numeric($params['contractId'])){
                    $notnull = false;
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
            if (!empty($params['kind'])){
                if (is_numeric($params['kind'])){
                    $queryBuilder
                        ->andWhere('c.kind = :kind')
                        ->setParameter('kind', $params['kind'])
                            ;
                }    
            }            
            if (!empty($params['priceListStatus'])){
                if (is_numeric($params['priceListStatus'])){
                    $queryBuilder
                        ->andWhere('s.priceListStatus = :priceListStatus')
                        ->setParameter('priceListStatus', $params['priceListStatus'])
                            ;
                }    
            }            
            if (!empty($params['sort'])){
                $queryBuilder
                    ->orderBy('c.'.$params['sort'], $params['order'])
                        ;
            }            
        }
        if ($notnull){
            $queryBuilder->andWhere('round(c.balance) != 0')
//                    ->andWhere('s.priceListStatus = :priceListStatus')
//                    ->setParameter('priceListStatus', Supplier::PRICE_LIST_ON)
                    ;
        }
        
//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery();
    }
    
    /**
     * Текущие итоги по договорам
     * @param array $params
     */
    public function contractBalancesTotal($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('count(c.id) as countC, '
                . 'sum(CASE WHEN c.balance >= 0 THEN c.balance ELSE 0 END) as balanceIn, '
                . 'sum(CASE WHEN c.balance < 0 THEN c.balance ELSE 0 END) as balanceOut')
            ->from(Contract::class, 'c')
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
            if (!empty($params['priceListStatus'])){
                if (is_numeric($params['priceListStatus'])){
                    $queryBuilder
                        ->andWhere('s.priceListStatus = :priceListStatus')
                        ->setParameter('priceListStatus', $params['priceListStatus'])
                            ;
                }    
            }            
            if (!empty($params['kind'])){
                if (is_numeric($params['kind'])){
                    $queryBuilder
                        ->andWhere('c.kind = :kind')
                        ->setParameter('kind', $params['kind'])
                            ;
                }    
            }            
        }
//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery();
    }
    
    /**
     * Найти заказы у котрых нет записей в retail
     */
    public function findOrdersToFixRetail()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('o.id as orderId, r.docStamp as docStamp')
                ->from(Register::class, 'r')
                ->join('r.order', 'o', 'WITH', 'r.docType = :docType')
                ->leftJoin(Retail::class, 'rt', 'WITH', 'r.docKey = rt.docKey')
                ->setParameter('docType', Movement::DOC_ORDER)
                ->andWhere('o.status = :status')
                ->setParameter('status', Order::STATUS_SHIPPED)
                ->andWhere('rt.docKey is null')
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }
    
    /**
     * Найти возвраты у котрых нет записей в retail
     */
    public function findVtToFixRetail()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('vt.id as vtId, r.docStamp as docStamp')
                ->from(Register::class, 'r')
                ->join('r.vt', 'vt', 'WITH', 'r.docType = :docType')
                ->leftJoin(Retail::class, 'rt', 'WITH', 'r.docKey = rt.docKey')
                ->setParameter('docType', Movement::DOC_VT)
                ->andWhere('vt.status = :status')
                ->setParameter('status', Vt::STATUS_ACTIVE)
                ->andWhere('rt.docKey is null')
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }
}