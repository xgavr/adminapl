<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

use Bank\Entity\Statement;
use Company\Entity\BankAccount;
use Laminas\Http\Client;


/**
 * Description of AplBankService
 *
 * @author Daddy
 */
class AplBankService {

    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Admin manager
     * @var Admin\Service\AdminManager
     */
    private $adminManager;

    public function __construct($entityManager, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
    }
    
    protected function aplApi()
    {
        return 'https://autopartslist.ru/api/';
        
    }
    
    protected function aplApiKey()
    {
        $settings = $this->adminManager->getAplExchangeSettings();
        return md5(date('Y-m-d').'#'.$settings['apl_secret_key']);
    }
    
    /**
     * Преобразовать в формат АПЛ
     * 
     * @param Statement $statement
     * @return array
     */
    public function convertStatementToAplFormat($statement)
    {
        $bankAccount = $this->entityManager->getRepository(BankAccount::class)
                ->findOneByRs($statement->getAccount());
        
        if (!$bankAccount) {
            return;
        }

        $result['valueDate']         = $statement->getChargeDate();     //ДатаСписано ДатаПоступило
        $result['docNum']            = $statement->getPaymentNumber();  //Номер
        $result['docDate']           = $statement->getPaymentDate();    //Дата
        $result['docSum']            = number_format(abs($statement->getАmount()), 2, '.', ''); //Сумма
        $result['purpose']           = $statement->getPaymentPurpose(); //НазначениеПлатежа
        $result['bankSistemId']      = $statement->getBankSystemId(); //уникальный номер в банке
        
        if ($statement->getАmount() > 0){ //Поступление на счет
            
            $result['dc'] = 0;
            $result['payerAcc']          = $statement->getCounterpartyAccountNumber(); //ПлательщикРасчСчет
            $result['payerINN']          = $statement->getСounterpartyInn();           //ПлательщикИНН
            $result['payerKPP']          = $statement->getСounterpartyKpp();           //ПлательщикКПП
            $result['payerName']         = $statement->getCounterpartyName();          //Плательщик1 Плательщик
            $result['payerBankName']     = $statement->getСounterpartyBankName();      //ПлательщикБанк1
            $result['payerBankBic']      = $statement->getCounterpartyBankBik();       //ПлательщикБИК
            //$result['payerBankCorrAcc']  = //
        
            $result['payeeAcc']          = $statement->getAccount();            //ПолучательРасчСчет
            $result['payeeINN']          = $bankAccount->getLegal()->getInn();  //ПолучательИНН
            $result['payeeKPP']          = $bankAccount->getLegal()->getKpp();  //ПолучательКПП
            $result['payeeName']         = $bankAccount->getLegal()->getName(); //Получатель1 Получатель
            $result['payeeBankName']     = $bankAccount->getName();             //ПолучательБанк1
            $result['payeeBankBic']      = $statement->getBik();                //ПолучательБИК
            $result['payeeBankCorrAcc']  = $bankAccount->getKs();               //ПолучательКорсчет
            
        } else { //Списание со счета
            
            $result['dc'] = 1;
            $result['payeeAcc']          = $statement->getCounterpartyAccountNumber(); //ПлательщикРасчСчет
            $result['payeeINN']          = $statement->getСounterpartyInn();           //ПлательщикИНН
            $result['payeeKPP']          = $statement->getСounterpartyKpp();           //ПлательщикКПП
            $result['payeeName']         = $statement->getCounterpartyName();          //Плательщик1 Плательщик
            $result['payeeBankName']     = $statement->getСounterpartyBankName();      //ПлательщикБанк1
            $result['payeeBankBic']      = $statement->getCounterpartyBankBik();       //ПлательщикБИК
            //$result['payeeBankCorrAcc']  = //
        
            $result['payerAcc']          = $statement->getAccount();            //ПолучательРасчСчет
            $result['payerINN']          = $bankAccount->getLegal()->getInn();  //ПолучательИНН
            $result['payerKPP']          = $bankAccount->getLegal()->getKpp();  //ПолучательКПП
            $result['payerName']         = $bankAccount->getLegal()->getName(); //Получатель1 Получатель
            $result['payerBankName']     = $bankAccount->getName();             //ПолучательБанк1
            $result['payerBankBic']      = $statement->getBik();                //ПолучательБИК
            $result['payerBankCorrAcc']  = $bankAccount->getKs();               //ПолучательКорсчет
            
        }            
        return ['statement' => $result];
    }
    
    public function dispathBankStatement($statement)
    {
        $transferData = $this->convertStatementToAplFormat($statement);
        
        if (is_array($transferData)){
            $url = $this->aplApi().'bank?api='.$this->aplApiKey();
    
            $client = new Client();
            $client->setUri($url);
            $client->setMethod('POST');
            $client->setOptions(['timeout' => 60]);
            $client->setParameterPost($transferData);

            $response = $client->send();

            if ($response->isOk()){
                $statement->setSwap1(Statement::SWAP1_TRANSFERED);
                $this->entityManager->persist($statement);
                $this->entityManager->flush($statement);
            }
        }    
        return;
    }


    /**
     * Передача выписки
     * 
     * @return void
     */
    public function sendBankStatement()
    {
        $statements = $this->entityManager->getRepository(Statement::class)
                ->findBy(['swap1' => Statement::SWAP1_TO_TRANSFER])
                ;
        if (count($statements)){
            foreach ($statements as $statement){
                $this->dispathBankStatement($statement);
            }
        }
        
        return;
    }
    
}
