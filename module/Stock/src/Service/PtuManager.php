<?php
namespace Stock\Service;

use Stock\Entity\Ptu;
use Stock\Entity\Ntd;
use Stock\Entity\Unit;
use Company\Entity\Country;
use Stock\Entity\PtuGood;
use Admin\Entity\Log;
use Stock\Entity\Movement;
use Stock\Entity\Mutual;

/**
 * This service is responsible for adding/editing ptu.
 */
class PtuManager
{
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;  
        
    /**
     * Constructs the service.
     */
    public function __construct($entityManager) 
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Лог пту
     * 
     * @param Ptu $ptu
     */
    public function addLog($ptu)
    {
        $this->entityManager->getRepository(Log::class)
                ->infoPtu($ptu, Log::STATUS_NEW, $this->currentUser()->getId());
        return;
    }
    
    /**
     * Обновить взаиморасчеты документа
     * 
     * @param Ptu $ptu
     */
    public function updatePtuMutuals($ptu)
    {
        
        $this->entityManager->getRepository(Mutual::class)
                ->removeDocMutuals($ptu->getLogKey());
        
        if ($ptu->getStatus() === Ptu::STATUS_ACTIVE){
            $data = [
                'doc_key' => $ptu->getLogKey(),
                'date_oper' => $ptu->getDateDoc(),
                'status' => Mutual::STATUS_ACTIVE,
                'revise' => Mutual::REVISE_NOT,
                'amount' => $ptuGood->getAmount(),
                'legal_id' => $ptuGood->getLegal()->getId(),
                'contract_id' => $ptuGood->getContract()->getId(),
                'office_id' => $ptu->getOffice()->getId(),
                'company_id' => $ptu->getContract()->getLegal()->getId(),
            ];

            $this->entityManager->getRepository(Mutual::class)
                    ->insertMutual($data);
        }   
        
        return;
    }    
    
    /**
     * Обновить движения документа
     * 
     * @param Ptu $ptu
     */
    public function updatePtuMovement($ptu)
    {
        
        $this->entityManager->getRepository(Movement::class)
                ->removeDocMovements($ptu->getLogKey());
        
        if ($ptu->getStatus() === Ptu::STATUS_ACTIVE){
            $ptuGoods = $this->entityManager->getRepository(PtuGood::class)
                    ->findByPtu($ptu->getId());
            foreach ($ptuGoods as $ptuGood){
                $data = [
                    'doc_key' => $ptu->getLogKey(),
                    'doc_row_key' => $ptuGood->getDocRowKey(),
                    'date_oper' => $ptu->getDateDoc(),
                    'status' => Movement::STATUS_ACTIVE,
                    'quantity' => $ptuGood->getQuantity(),
                    'amount' => $ptuGood->getAmount(),
                    'good_id' => $ptuGood->getGood()->getId(),
                    'office_id' => $ptu->getOffice()->getId(),
                ];

                $this->entityManager->getRepository(Movement::class)
                        ->insertMovement($data);
            }
        }   
        
        return;
    }    
    
    /**
     * Adds a new ptu.
     * @param array $data
     * @param integer $userId
     * @return integer
     */
    public function addPtu($data, $userId = 0)
    {
        $rec = [
            'date_created' => date('Y-m-d H:i:s'),
            'status' => Ptu::STATUS_ACTIVE,
            'status_doc' => Ptu::STATUS_DOC_NOT_RECD,
            'status_ex' => Ptu::STATUS_EX_NEW,
            'amount' => 0,
//            'doc_no' => $data['doc_no'],
//            'doc_date' => $data['doc_date'],
//            'legal_id' => $data['legal_id'],
//            'contract_id' => $data['contract_id'],
//            'office_id' => $data['office_id'],
//            'comment' => $data['comment'],
//            'info' => $data['info'],
        ];
        foreach ($data as $key => $value){
            $rec[$key] = $value;
        }
        
        $connection = $this->entityManager->getConnection(); 
        $ptuId = $connection->insert('ptu', $rec);
        if ($ptuId){
            $ptu = $this->entityManager->getRepository(Ptu::class)
                    ->findOneById($ptuId);
        }
        
        return $ptu;
    }
    
    /**
     * Update ptu.
     * @param Ptu $ptu
     * @param array $data
     * @param integer $userId
     * @return integer
     */
    public function updatePtu($ptu, $data, $userId = 0)            
    {
        
        $connection = $this->entityManager->getConnection(); 
        $connection->update('ptu', $data, ['id' => $ptu->getId()]);
        
        $this->entityManager->getRepository(Log::class)
                ->infoPtu($ptu, Log::STATUS_UPDATE, $userId);
        return;
    }
    
    /**
     * Получить НТД
     * @param string $strNtd
     * @return integer 
     */
    public function findNtd($strNtd)
    {
        $ntd = $this->entityManager->getRepository(Ntd::class)
                ->findOneByNtd(trim($strNtd));
        if ($ntd === NULL){
            if (empty(trim($strNtd))){
                $strNtd = '-';
            }
            $connection = $this->entityManager->getConnection();
            $connection->insert('ntd', ['ntd' => trim($strNtd)]);
            return $connection->lastInsertId();
        }
        
        return $ntd->getId();
    }
    
    /**
     * Выдать ЕИ по умолчанию
     * @return integer
     */
    public function findDefaultUnit()
    {
        $defaultCode = '796';
        $defaultName = 'шт';
        $unit = $this->entityManager->getRepository(Unit::class)
                ->findOneBy(['code' => $defaultCode, 'name' => $defaultName]);            
        if ($unit === NULL){            
            $connection = $this->entityManager->getConnection();
            $connection->insert('unit', ['code' => $defaultCode, 'name' => $defaultName]);
            return $connection->lastInsertId();
        }          
        return $unit->getId();
    }
    
    /**
     * Получить ЕИ
     * @param string $unitName
     * @param string $unitCode
     * @return integer 
     */
    public function findUnit($unitName, $unitCode = null)
    {
        if (empty($unitCode)){
            $unit = $this->entityManager->getRepository(Unit::class)
                    ->findOneByName(trim($unitName));
            if ($unit == NULL){
                return $this->findDefaultUnit();
            }
        } else {
            $unit = $this->entityManager->getRepository(Unit::class)
                    ->findOneByCode(trim($unitCode));            
            if ($unit == NULL){
                return $this->findDefaultUnit();
            }
        }    
    }
    
    
    public function findDefaultCountry()
    {
        $defaultCode = '-';
        $defaultName = '-';
        $country = $this->entityManager->getRepository(Country::class)
                ->findOneBy(['code' => $defaultCode, 'name' => $defaultName]);            
        if ($country === NULL){            
            $connection = $this->entityManager->getConnection();
            $connection->insert('country', ['code' => $defaultCode, 'name' => $defaultName]);
            return $connection->lastInsertId();
        }          
        return $country->getId();        
    }
    
    /**
     * Получить Страну
     * @param string $countryName
     * @param string $countryCode
     * @return integer 
     */
    public function findCountry($countryName, $countryCode = null)
    {
        if (empty($countryCode)){
            $country = $this->entityManager->getRepository(Country::class)
                    ->findOneByName(trim($countryName));
            if ($country == NULL){
                return $this->findDefaultCountry();
            }
        } else {
            $country = $this->entityManager->getRepository(Country::class)
                    ->findOneByCode(trim($countryCode));            
            if ($country == NULL){
                return $this->findDefaultCountry();
            }
        }    
    }
    

    /**
     * Adds a new ptu-good.
     * @param integer $ptuId
     * @param array $data
     * @return integer
     */
    public function addPtuGood($ptuId, $data)
    {
        $ptu = [
            'ptu_id' => $ptuId,
            'status' => Ptu::STATUS_ACTIVE,
            'status_doc' => Ptu::STATUS_DOC_NOT_RECD,
            'quantity' => $data['quantity'],
            'amount' => $data['amount'],
            'good_id' => $data['good_id'],
            'comment' => $data['comment'],
            'info' => $data['info'],
            'country_id' => $this->findCountry($data['countryName'], $data['countryCode']),
            'unit_id' => $this->findUnit($data['unitName'], $data['unitCode']),
            'ntd_id' => $this->findNtd($data['ntd']),
        ];
        
        $connection = $this->entityManager->getConnection(); 
        $connection->insert('ptu', $ptu);
        return;
    }
    
    /**
     * Update ptu_good.
     * @param PtuGood $ptuGood
     * @param array $data
     * @return integer
     */
    public function updatePtuGood($ptuGood, $data)            
    {
        
        $connection = $this->entityManager->getConnection(); 
        $connection->update('ptu_good', $data, ['id' => $ptuGood->getId()]);
        return;
    }
    
    /**
     * Обновить сумму ПТУ
     * @param Ptu $ptu
     */
    public function updatePtuAmount($ptu)
    {
        $ptuAmountTotal = $this->entityManager->getRepository(Ptu::class)
                ->ptuAmountTotal($ptu);
        $this->entityManager->getConnection()->update('ptu', ['amount' => $ptuAmountTotal]);
        return;
    }
    
    /**
     * Удаление строк ПТУ
     * @param Ptu $ptu
     */
    public function removePtuGood($ptu)
    {
        $ptuGoods = $this->entityManager->getRepository(PtuGood::class)
                ->findByPtu($ptu->getId());
        foreach ($ptuGoods as $ptuGood){
            $this->entityManager->getConnection()
                    ->delete('ptu_good', ['ptu_id' => $ptu->getId()]);
        }
        
        return;
    }
    
    /**
     * Обновление строк ПТУ
     * 
     * @param Ptu $ptu
     * @param array $data
     */
    public function updatePtuGoods($ptu, $data)
    {
        $this->removePtuGood($ptu);
        
        foreach ($data as $row){
            $this->addPtuGood($ptu->getId(), $row);
        }
        
        $this->updatePtuAmount($ptu);
        $this->updatePtuMovement($ptu);
        $this->updatePtuMutuals($ptu);
        return;
    }    
}

