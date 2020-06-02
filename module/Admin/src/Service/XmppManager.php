<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

use Laminas\Http\Client;
use Laminas\Json\Json;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Fabiang\Xmpp\Options;
use Fabiang\Xmpp\Client;
use Fabiang\Xmpp\Protocol\Roster;
use Fabiang\Xmpp\Protocol\Presence;
use Fabiang\Xmpp\Protocol\Message;
/**
 * Description of SmsManager
 * send sms from sms.ru
 * @author Daddy
 */
class XmppManager {
    
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    private $adminManager;

    public function __construct($entityManager, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
    }
    
    protected function api()
    {
        return 'https://api.ok.ru/graph/me/';
        
    }
    
    public function chats()
    {
        $settings = $this->adminManager->getSettings();
        if ($settings['tamtam_access_token']){
            
            $url = $this->api()."chats?access_token=".$settings['tamtam_access_token'];
            var_dump($url);
           
            $client = new Client();
            $client->setUri($url);
            $client->setMethod('GET');

            $response = $client->send();

            if ($response->isSuccess()) {
                $body = $response->getBody();
                return (array) Json::decode($body);
            }

            return;
        }
    }
    /*
     * @var $params array
     * chat_id string
     * text string
     * https://apiok.ru/dev/graph_api/methods/graph.user/graph.user.messages/post
     */
    public function message($params)
    {
        $settings = $this->adminManager->getSettings();
        if ($settings['tamtam_chat_id'] && $settings['tamtam_access_token']){
            
            $url = $this->api()."chat:".$settings['tamtam_chat_id'].'?access_token='.$settings['tamtam_access_token'];
            
            $data = [
                'recipient' => ['chat_id' => 'chat:'.$params['chat_id']],
                'message' => ['text' => $params['text']],
            ];
            
            $client = new Client();
            $client->setUri($url);
            $client->setMethod('POST');
            $client->setHeaders(['Content-Type: application/json;charset=utf-8']);
            //$client->setParameterPost(Json::encode($data));
            $client->setRawBody(Json::encode($data));
            $client->setEncType('application/json');

            $response = $client->send();

            if ($response->isSuccess()) {
                $body = $response->getBody();
                return (array) Json::decode($body);
            }

            return;
        }    
        
        return;
    }
}
