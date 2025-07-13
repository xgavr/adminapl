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
use Stock\Entity\Register;
use Laminas\Json\Encoder;
use Stock\Entity\PtuCost;
use Company\Entity\Cost;
use Stock\Entity\Retail;
use Company\Entity\Contract;
use Application\Entity\Goods;

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
     * Log manager
     * @var \Admin\Service\LogManager
     */
    private $logManager;
        
    /**
     * Admin manager
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;
        
    /**
     * Cost manager
     * @var \Company\Service\CostManager
     */
    private $costManager;
        
    /**
     * Fold manager
     * @var \GoodMap\Service\FoldManager
     */
    private $foldManager;
        
    /**
     * Дата запрета
     * @var string
     */
    private $allowDate;

    /**
     * Constructs the service.
     */
    public function __construct($entityManager, $logManager, $adminManager, 
            $costManager, $foldManager) 
    {
        $this->entityManager = $entityManager;
        $this->logManager = $logManager;
        $this->adminManager = $adminManager;
        $this->costManager = $costManager;
        $this->foldManager = $foldManager;

        $setting = $this->adminManager->getSettings();
        $this->allowDate = $setting['allow_date'];
                
    }
    
    /**
     * Получить дату запрета
     * @return date
     */
    public function getAllowDate()
    {
        return $this->allowDate; 
    }
        
    /**
     * Текущий пользователь
     * @return User
     */
    public function currentUser()
    {
        return $this->logManager->currentUser();
    }
    
    /**
     * Обновить взаиморасчеты документа
     * 
     * @param Ptu $ptu
     * @param float $docStamp
     */
    public function updatePtuRetail($ptu, $docStamp)
    {
        
        $this->entityManager->getRepository(Retail::class)
                ->removeOrderRetails($ptu->getLogKey());
        
        if ($ptu->getStatus() == Ptu::STATUS_ACTIVE && $ptu->getContract()->getKind() == Contract::KIND_COMITENT){        
            $data = [
                'doc_key' => $ptu->getLogKey(),
                'doc_type' => Movement::DOC_PTU,
                'doc_id' => $ptu->getId(),
                'date_oper' => $ptu->getDocDate(),
                'status' => Retail::getStatusFromPtu($ptu),
                'revise' => Retail::REVISE_NOT,
                'amount' => -$ptu->getAmount(),
                'contact_id' => $ptu->getLegal()->getClientContact()->getId(),
                'legal_id' => $ptu->getLegal()->getId(),
                'contract_id' => $ptu->getContract()->getId(),
                'office_id' => $ptu->getOffice()->getId(),
                'company_id' => $ptu->getContract()->getCompany()->getId(),
                'doc_stamp' => $docStamp,                
            ];

            $this->entityManager->getRepository(Retail::class)
                    ->insertRetail($data);
        }    
        
        return;
    }    
    
    /**
     * Обновить взаиморасчеты документа
     * 
     * @param Ptu $ptu
     * @param float $docStamp 
     */
    public function updatePtuMutuals($ptu, $docStamp)
    {
        
        $this->entityManager->getRepository(Mutual::class)
                ->removeDocMutuals($ptu->getLogKey());
        
        if ($ptu->getStatus() == Ptu::STATUS_ACTIVE){        
            $data = [
                'doc_key' => $ptu->getLogKey(),
                'doc_type' => Movement::DOC_PTU,
                'doc_id' => $ptu->getId(),
                'date_oper' => $ptu->getDocDate(),
                'status' => Mutual::getStatusFromPtu($ptu),
                'revise' => Mutual::REVISE_NOT,
                'amount' => -$ptu->getAmount(),
                'legal_id' => $ptu->getLegal()->getId(),
                'contract_id' => $ptu->getContract()->getId(),
                'office_id' => $ptu->getOffice()->getId(),
                'company_id' => $ptu->getContract()->getCompany()->getId(),
                'doc_stamp' => $docStamp,
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
     * @param float $docStamp
     */
    public function updatePtuMovement($ptu, $docStamp)
    {
        $this->entityManager->getRepository(Movement::class)
                ->removeDocMovements($ptu->getLogKey());
        
        $ptuGoods = $this->entityManager->getRepository(PtuGood::class)
                ->findByPtu($ptu->getId());
        foreach ($ptuGoods as $ptuGood){
            if ($ptu->getStatus() == Ptu::STATUS_ACTIVE){        
                $data = [
                    'doc_key' => $ptu->getLogKey(),
                    'doc_type' => Movement::DOC_PTU,
                    'doc_id' => $ptu->getId(),
                    'base_key' => $ptu->getLogKey(),
                    'base_type' => Movement::DOC_PTU,
                    'base_id' => $ptu->getId(),
                    'doc_row_key' => $ptuGood->getDocRowKey(),
                    'doc_row_no' => $ptuGood->getRowNo(),
                    'date_oper' => date('Y-m-d 00:00:00', strtotime($ptu->getDocDate())),
                    'status' => $ptu->getStatus(),
                    'quantity' => $ptuGood->getQuantity(),
                    'amount' => $ptuGood->getAmount(),
                    'base_amount' => $ptuGood->getAmount(),
                    'good_id' => $ptuGood->getGood()->getId(),
                    'office_id' => $ptu->getOffice()->getId(),
                    'company_id' => $ptu->getContract()->getCompany()->getId(),
                    'doc_stamp' => $docStamp,
                ];

                $this->entityManager->getRepository(Movement::class)
                        ->insertMovement($data);
            }  
            
            $this->entityManager->getRepository(Movement::class)
                    ->updateGoodBalance($ptuGood->getGood()->getId());
        }    
        
        return;
    }    
    
    
    /**
     * Обновить зависимые записи
     * @param Ptu $ptu
     * @param bool $flush
     */
    public function updateInfo($ptu, $flush = false)
    {

        $info = $ptu->dependInfo();
//        var_dump($info);
        $ptu->setInfo($info);
        
        if ($flush){
            $this->entityManager->persist($ptu);
            $this->entityManager->flush($ptu);
        }
        
        return Encoder::encode($info);
    }    
    
    /**
     * Update ptu status.
     * @param Ptu $ptu
     * @param integer $status
     * @return integer
     */
    public function updatePtuStatus($ptu, $status)            
    {

        if ($ptu->getDocDate() > $this->allowDate || $ptu->getStatus() != Ptu::STATUS_ACTIVE){
            $ptu->setStatus($status);
            $ptu->setStatusEx(Ptu::STATUS_EX_NEW);
            $ptu->setStatusAccount(Ptu::STATUS_ACCOUNT_NO);
            
            if ($ptu->getDocDate() < $this->getAllowDate() && $status == Ptu::STATUS_RETIRED){
                $ptu->setDocDate(date('Y-m-d', strtotime($this->getAllowDate().' + 1 day')));
            }

            $this->entityManager->persist($ptu);
            $this->entityManager->flush($ptu);

            $this->repostPtu($ptu);
            $this->logManager->infoPtu($ptu, Log::STATUS_UPDATE);
        }    
        
        return;
    }
    
    /**
     * Перепроведение ПТУ
     * @param Ptu $ptu
     */
    public function repostPtu($ptu)
    {
        $this->updateInfo($ptu, true);
        if ($ptu->getDocDate() > $this->getAllowDate()){
            
            $docStamp = $this->entityManager->getRepository(Register::class)
                    ->ptuRegister($ptu);
            
            $this->updatePtuMovement($ptu, $docStamp);
            $this->updatePtuMutuals($ptu, $docStamp);
            $this->updatePtuRetail($ptu, $docStamp);
            $this->costManager->repostPtu($ptu, $docStamp);
            $this->foldManager->ptuFold($ptu, $docStamp);
        }    
        return;
    }

    /**
     * Перепровести и обновить
     * @param Ptu $ptu
     */
    public function repostEx($ptu)
    {
        $this->repostPtu($ptu);
        
        $ptu->setStatusEx(Ptu::STATUS_EX_NEW);
        $this->entityManager->persist($ptu);
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * Перепроведение всех ПТУ
     * @param Ptu $ptu
     */
    public function repostAllPtu()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        
        $ptuQuery = $this->entityManager->getRepository(Ptu::class)
                ->queryAllPtu();
        $iterable = $ptuQuery->iterate();
        
        foreach ($iterable as $row){
            foreach($row as $ptu){ 
                $this->repostPtu($ptu);
                $this->entityManager->detach($ptu);
                unset($ptu);
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
        if ($data['doc_date'] > $this->allowDate){
            $ptu = new Ptu();        
            $ptu->setAplId($data['apl_id']);
            $ptu->setDocNo($data['doc_no']);
            $ptu->setDocDate($data['doc_date']);
            $ptu->setComment(empty($data['comment']) ? null:$data['comment']);
//            $ptu->setStatusEx($data['status_ex']);
            $ptu->setStatusEx(empty($data['status_ex']) ? Ptu::STATUS_EX_NEW:$data['status_ex']);
            $ptu->setStatusAccount(Ptu::STATUS_ACCOUNT_NO);
            $ptu->setStatus($data['status']);
            $ptu->setOffice($data['office']);
            $ptu->setSupplier($data['supplier']);
            $ptu->setLegal($data['legal']);
            $ptu->setContract($data['contract']); 
            $ptu->setStatusDoc(Ptu::STATUS_DOC_NOT_RECD);
            $ptu->setAmount(0);
            $ptu->setDateCreated(date('Y-m-d H:i:s'));

            $this->entityManager->persist($ptu);
            $this->entityManager->flush($ptu);        
            return $ptu;        
        }    
        
        return;
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
        if ($data['doc_date'] > $this->allowDate){
            $ptu->setAplId($data['apl_id']);
            $ptu->setDocNo($data['doc_no']);
            $ptu->setDocDate($data['doc_date']);
            $ptu->setComment(empty($data['comment']) ? null:$data['comment']);
//            $ptu->setStatusEx($data['status_ex']);
            $ptu->setStatusEx(empty($data['status_ex']) ? Ptu::STATUS_EX_NEW:$data['status_ex']);
            $ptu->setStatusAccount(Ptu::STATUS_ACCOUNT_NO);
            $ptu->setStatus($data['status']);
            $ptu->setOffice($data['office']);
            $ptu->setSupplier($data['supplier']);
            $ptu->setLegal($data['legal']);
            $ptu->setContract($data['contract']);

            $this->entityManager->persist($ptu);
            $this->entityManager->flush($ptu);
        }    
        
        return;
    }
    
    /**
     * Получить НТД
     * @param string $strNtd
     * @return integer 
     */
    public function findNtd($strNtd)
    {
        if (empty(trim($strNtd))){
            $strNtd = '-';
        }
        $ntd = $this->entityManager->getRepository(Ntd::class)
                ->findOneByNtd(trim($strNtd));
        if ($ntd === NULL){
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
            } else {
                return $unit->getId();
            }
        }    
        $unit = $this->entityManager->getRepository(Unit::class)
                ->findOneByCode(trim($unitCode));            
        if ($unit == NULL){
            if (empty($unitName)){
                return $this->findDefaultUnit();
            } else {
                $connection = $this->entityManager->getConnection();
                $connection->insert('unit', ['code' => $unitCode, 'name' => $unitName]);
                return $connection->lastInsertId();                
            }    
        } else {
            return $unit->getId();            
        }   
        
        return;
    }
    
    
    public function findDefaultCountry()
    {
        $defaultCode = '-';
        $defaultName = '-';
        $country = $this->entityManager->getRepository(Country::class)
                ->findOneBy(['code' => $defaultCode, 'name' => $defaultName]);            
        if ($country === NULL){            
            $connection = $this->entityManager->getConnection();
            $connection->insert('country', [
                'code' => $defaultCode, 
                'name' => $defaultName, 
                'fullname' => $defaultName,
                'alpha2' => '--',
                'alpha3' => '---',
                ]);
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
            } else {
                return $country->getId();
            }
        }
        
        $country = $this->entityManager->getRepository(Country::class)
                ->findOneByCode(trim($countryCode));            
        
        if ($country == NULL){
            if (empty($countryName)){
                return $this->findDefaultCountry();
            } else {
                $connection = $this->entityManager->getConnection();
                $connection->insert('country', [
                    'code' => $countryCode, 
                    'name' => $countryName, 
                    'fullname' => $countryName,
                    'alpha2' => '--',
                    'alpha3' => '---',
                    ]);
                return $connection->lastInsertId();
            }    
        } else {
            return $country->getId();
        }
        
        return;
    }
    

    /**
     * Adds a new ptu-good.
     * @param integer $ptuId
     * @param array $data
     * @param integer $rowNo
     * 
     * @return integer
     */
    public function addPtuGood($ptuId, $data, $rowNo)
    {
//        var_dump($data); exit;
        $ptuGood = [
            'ptu_id' => $ptuId,
            'status' => (isset($data['status'])) ? $data['status']:PtuGood::STATUS_ACTIVE,
            'status_doc' => (isset($data['statusDoc'])) ? $data['statusDoc']:PtuGood::STATUS_DOC_RECD,
            'quantity' => $data['quantity'],
            'amount' => $data['amount'],
            'good_id' => $data['good_id'],
            'comment' => (isset($data['comment'])) ? $data['comment']:'',
//            'info' => $data['info'],
            'country_id' => $this->findCountry($data['countryName'], (isset($data['countryCode'])) ? $data['countryCode']:null),
            'unit_id' => $this->findUnit($data['unitName'], $data['unitCode']),
            'ntd_id' => $this->findNtd($data['ntd']),
            'row_no' => $rowNo,
        ];
        
        $connection = $this->entityManager->getConnection(); 
        $connection->insert('ptu_good', $ptuGood);
        $connection->update('goods', ['fasade_ex' => Goods::FASADE_EX_NEW], ['id' => $ptuGood['good_id']]);
        
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
        $connection->update('goods', ['fasade_ex' => Goods::FASADE_EX_NEW], ['id' => $ptuGood->getGood()->getId()]);
        
        return;
    }
    
    /**
     * Удаление строк ПТУ
     * @param Ptu $ptu
     */
    public function removePtuGood($ptu)
    {
        $this->entityManager->getConnection()
                ->delete('ptu_good', ['ptu_id' => $ptu->getId()]);
        return;
    }
        
    /**
     * Обновление строк ПТУ
     * 
     * @param Ptu $ptu
     * @param array $data
     * @param array $dataCost
     */
    public function updatePtuGoods($ptu, $data, $dataCost = null)
    {
        $this->removePtuGood($ptu);
        $this->removePtuCost($ptu);
        
        $rowNo = 1;
        foreach ($data as $row){
            $this->addPtuGood($ptu->getId(), $row, $rowNo);
            $rowNo++;
        }

        if (is_array($dataCost)){
            $rowNo = 1;
            foreach ($dataCost as $row){
                $this->addPtuCost($ptu->getId(), $row, $rowNo);
                $rowNo++;
            }
        }    
        
        $this->updatePtuAmount($ptu);
        return;
    }
    
    /**
     * Adds a new ptu-cost.
     * @param integer $ptuId
     * @param array $data
     * @param integer $rowNo
     * 
     * @return integer
     */
    public function addPtuCost($ptuId, $data, $rowNo)
    {
//        var_dump($data); exit;
        $ptuCost = [
            'ptu_id' => $ptuId,
            'quantity' => $data['quantity'],
            'amount' => $data['amount'],
            'cost_id' => $data['cost_id'],
            'comment' => (isset($data['comment'])) ? $data['comment']:'',
//            'info' => $data['info'],
            'row_no' => $rowNo,
        ];
        
        $connection = $this->entityManager->getConnection(); 
        $connection->insert('ptu_cost', $ptuCost);
        return;
    }
    
    /**
     * Update ptu_cost.
     * @param PtuCost $ptuCost
     * @param array $data
     * @return integer
     */
    public function updatePtuCost($ptuCost, $data)            
    {
        
        $connection = $this->entityManager->getConnection(); 
        $connection->update('ptu_cost', $data, ['id' => $ptuCost->getId()]);
        return;
    }
    
    /**
     * Удаление строк ПТУ
     * @param Ptu $ptu
     */
    public function removePtuCost($ptu)
    {
        $this->entityManager->getConnection()
                ->delete('ptu_cost', ['ptu_id' => $ptu->getId()]);
        return;
    }
    
    /**
     * Обновить сумму ПТУ
     * @param Ptu $ptu
     */
    public function updatePtuAmount($ptu)
    {
        $preLog = $this->entityManager->getRepository(Log::class)
                ->findOneByLogKey($ptu->getLogKey());
        if (!$preLog){
            $this->logManager->infoPtu($ptu, Log::STATUS_INFO);            
        }
        
        $ptuAmountTotal = $this->entityManager->getRepository(Ptu::class)
                ->ptuAmountTotal($ptu);
//        $this->entityManager->getConnection()->update('ptu', ['amount' => $ptuAmountTotal], ['id' => $ptu->getId()]);
        $ptu->setAmount($ptuAmountTotal);
        $this->entityManager->persist($ptu);
        $this->entityManager->flush($ptu);
        
        $this->entityManager->refresh($ptu);
        $this->repostPtu($ptu);
        $this->logManager->infoPtu($ptu, Log::STATUS_UPDATE);
        return;
    }
    
    /**
     * Ужаление ПТУ
     * 
     * @param Ptu $ptu
     */
    public function removePtu($ptu)
    {
        if ($ptu->getDocDate() > $this->allowDate){
            $this->logManager->infoPtu($ptu, Log::STATUS_DELETE);
            $this->entityManager->getRepository(Mutual::class)
                    ->removeDocMutuals($ptu->getLogKey());
            $this->entityManager->getRepository(Movement::class)
                    ->removeDocMovements($ptu->getLogKey());
            $this->removePtuGood($ptu);
            $this->removePtuCost($ptu);

            $this->entityManager->getConnection()->delete('ptu', ['id' => $ptu->getId()]);
        }    
        
        return;
    }
    
    /**
     * Заменить товар
     * @param Goods $oldGood
     * @param Goods $newGood
     */
    public function changeGood($oldGood, $newGood)
    {
        $rows = $this->entityManager->getRepository(PtuGood::class)
                ->findBy(['good' => $oldGood->getId()]);
        foreach ($rows as $row){
            $register = $this->entityManager->getRepository(Register::class)
                    ->findOneBy(['docKey' => $row->getPtu()->getLogKey()]);
            
            $row->setGood($newGood);
            $this->entityManager->persist($row);
            $this->entityManager->flush();
            $this->updatePtuMovement($row->getPtu(), $register->getDocStamp());
        }
        
        return;
    }    
    
    /**
     * Исправить поставщика
     */
    public function correctSupplier()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        
        $ptus = $this->entityManager->getRepository(Ptu::class)
                ->findAll();
        foreach ($ptus as $ptu){
            $supplier = $ptu->getContactSupplier();
            if ($supplier){
                $ptu->setSupplier($supplier);
                $this->entityManager->persist($ptu);
            }    
        }
        
        $this->entityManager->flush();
        return;
    }    
}

