<?php
namespace Stock\Service;

use Application\Entity\Goods;
use Stock\Entity\Mark;
use Application\Entity\Order;

/**
 * This service is responsible for adding/editing ptu.
 */
class MarkManager
{
    
    const JWT_PUBLIC_KEY = 'markirovka_public_key.txt'; //публичный ключ OpenAPI
    const SHA1_KEY = 'markirovka_sha1.txt'; //отпечаток sha1 электронной подписи
    
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
     * Filesystem cache.
     * @var \Laminas\Cache\Storage\StorageInterface
     */
    private $cache;    
 
    /**
     * @var string
     */
    private $token_dir;
    
    /**
     * @var string
     */
    private $jwt_public_key;    
    
    /**
     * @var string
     */
    private $sha1_key;    
    
    /**
     * Constructs the service.
     */
    public function __construct($entityManager, $logManager, $adminManager, $cache) 
    {
        $this->entityManager = $entityManager;
        $this->logManager = $logManager;
        $this->adminManager = $adminManager;
        $this->cache = $cache;
        
        $this->token_dir = './data/token/';
        $this->jwt_public_key = '';
        if (file_exists($this->token_dir.self::JWT_PUBLIC_KEY)){
            $this->jwt_public_key = file_get_contents($this->token_dir.self::JWT_PUBLIC_KEY);
        }        
        
        $this->sha1_key = '';
        if (file_exists($this->token_dir.self::SHA1_KEY)){
            $this->sha1_key = file_get_contents($this->token_dir.self::SHA1_KEY);
        }        
        
    }
    

    /**
     * Adds a new Mark.
     * @param array $data
     * @return integer
     */
    public function addMark($data)
    {                
        
        $good = $this->entityManager->getRepository(Goods::class)
                ->findOneBy(['aplId' => $data['parent']]);
        
        $order = $this->entityManager->getRepository(Order::class)
                ->findOneBy(['aplId' => $data['publish']]); 
        
        if ($good && $order){
            
            $mark = $this->entityManager->getRepository(Mark::class)
                    ->findOneBy(['mark' => $data['type']]);
            
            if (empty($mark)){
                $mark = new Mark();
                $mark->setMarkStatus(Mark::MARK_UNKNOWN);
                $mark->setStatus(Mark::STATUS_ACTIVE);
            }
                           
            $mark->setAplId($data['id']);
            $mark->setCreated($data['created']);
            $mark->setGood($good);
            $mark->setMark($data['type']);
            $mark->setMarkGroup($data['sort']);
            $mark->setOrder($order);
            $mark->setUpdated(date('Y-m-d H:i:s'));

            $this->entityManager->persist($mark);        
            $this->entityManager->flush();

            return $mark;        
        }
        
        return;
    }
    
    
    /**
     * Update status.
     * @param Mark $mark
     * @param integer $status
     * @return integer
     */
    public function updateStatus($mark, $status)            
    {

        $mark->setStatus($status);

        $this->entityManager->persist($mark);
        $this->entityManager->flush();

        return;
    }
    
    /**
     * Update mark status.
     * @param Mark $mark
     * @param integer $markStatus
     * @return integer
     */
    public function updateMarkStatus($mark, $markStatus)            
    {

        $mark->setMarkStatus($markStatus);

        $this->entityManager->persist($mark);
        $this->entityManager->flush();

        return;
    }
    
    /**
     * 
     * @return токен ЧЗ
     */
    private function signToken()
    {
        $result = $this->cache->getItem('markirovka_token');
//        var_dump($result); exit;
        if (empty($result)){
            $url = "https://markirovka.crpt.ru/api/v3/true-api/auth/simpleSignIn";

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $headers = array(
                    "Accept: application/json",

                    "Content-Type: application/json",
            );
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            $data = $this->jwt_public_key;
            
//            var_dump($data); exit;

            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

            $resp = curl_exec($curl);

            curl_close($curl);
            $response = json_decode($resp, true);

            // Возвращаем uuidToken
            $result = $response['uuidToken'];
            $this->cache->setItem('markirovka_token', $result);
        } 
        
        return $result;
        
    }
    
    private function signToken2() {
        $result = $this->cache->getItem('markirovka_token_1');

        if (empty($result)) {
            // --- ШАГ 1: Получаем случайную строку (данные для подписи) от ЧЗ ---
            $urlCertKey = "https://markirovka.crpt.ru/api/v3/true-api/auth/key"; // В зависимости от контура v3/true-api/auth/key или v3/auth/cert/key
            $curl = curl_init($urlCertKey);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ["Accept: application/json"]);

            $respCert = curl_exec($curl);
            curl_close($curl);

            $certData = json_decode($respCert, true);

            if (empty($certData['data']) || empty($certData['uuid'])) {
                throw new \Exception("Не удалось получить данные для подписи от Честного Знака");
            }

            $serverDataToSign = $certData['data']; // Строка, которую нужно подписать
            $uuid = $certData['uuid'];

            // --- ШАГ 2: Программное подписание строки через КриптоПро (cryptcp) ---
            // Замените НАШ_ОТПЕЧАТАК_СЕРТИФИКАТА на отпечаток вашей новой подписи (SHA-1) из КриптоПро CSP
            $certThumbprint = $this->sha1_key; 

            // Временные файлы для работы с консольной утилитой cryptcp
            $inputFile = sys_get_temp_dir() . '/cz_data.txt';
            $outputFile = sys_get_temp_dir() . '/cz_data.txt.sgn';

            // Декодируем входящую строку из base64 и пишем в файл
            file_put_contents($inputFile, base64_decode($serverDataToSign));

            if (file_exists($outputFile)) { unlink($outputFile); }

            // Вызов утилиты cryptcp для создания отсоединенной (-detached) подписи в кодировке base64 (-base64)
            $command = "cryptcp -sign -detached -base64 -thumbprint " . escapeshellarg($certThumbprint) . " " . escapeshellarg($inputFile) . " " . escapeshellarg($outputFile) . " 2>&1";
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new Exception("Ошибка подписания через cryptcp: " . implode("\n", $output));
            }

            // Читаем полученную подпись, удаляя лишние переносы строк
            $signatureBase64 = str_replace(["\r", "\n"], "", file_get_contents($outputFile));

            // Чистим временные файлы
            unlink($inputFile);
            unlink($outputFile);

            // --- ШАГ 3: Отправка подписанного токена в Честный Знак ---
            $urlSignIn = "https://crpt.ru";

            // Формируем payload, как в вашем примере, но динамически
            $postData = json_encode([
                "uuid" => $uuid,
                "data" => $signatureBase64,
                "unitedToken" => true
            ]);

            $curl = curl_init($urlSignIn);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                "Accept: application/json",
                "Content-Type: application/json",
            ]);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);

            $resp = curl_exec($curl);
            curl_close($curl);

            $response = json_decode($resp, true);

            if (empty($response['uuidToken'])) {
                throw new Exception("Ошибка авторизации simpleSignIn: " . var_export($response, true));
            }

            // Возвращаем uuidToken и кэшируем его
            $result = $response['uuidToken'];
            $this->cache->setItem('markirovka_token_1', $result);
        }

        return $result;
    }
    
    
    /**
     * 
     * @param string|array $qrCodes
     */
    public function signQr($qrCodes)
    {
        $uuidToken = $this->signToken2();
        
        var_dump($uuidToken); exit;
        
        if (is_string($qrCodes)){
            $qrCodes = [$qrCodes];
        }

        $payload = json_encode($qrCodes); 

        $url = "https://markirovka.crpt.ru/api/v3/true-api/cises/info";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
                "Accept: application/json",
                "Authorization: Bearer " . $uuidToken,
                "Content-Type: application/json",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);

        $resp = curl_exec($curl);
        curl_close($curl);
        
//        var_dump($resp);
        
        try{
            $result = json_decode($resp, \Laminas\Json\Json::TYPE_ARRAY);
        } catch (Throwable $e){
            var_dump($e->getMessage());
        }
        
//        var_dump($result); exit;
        
        if (is_array($result)){
            foreach ($result as $value){
//                var_dump($value['cisInfo']['cis'], $value['cisInfo']['status']); //exit;
                                                
                if (!empty($value['cisInfo']['cis'])){
                    $mark = $this->entityManager->getRepository(Mark::class)
                            ->findMarkByMark31($value['cisInfo']['cis']);
                    
                    if ($mark){
                        if (!empty($value['cisInfo']['status'])){

                            $newMark = Mark::getRemoteMarkStatus($value['cisInfo']['status']);
                        } else {
 
                            $newMark = Mark::MARK_NOT_FOUND;
                        }
                        
                        $upd = [
                            'mark_status' => $newMark,
                        ];
                        
                        if ($mark->getStatus() === Mark::STATUS_ACTIVE && $mark->getMarkStatus() !== $newMark){
                            $upd['date_updated'] = date('Y-m-d H:i:s');
                        }

                        $this->entityManager->getConnection()->update('marks', $upd, ['id' => $mark->getId()]);
                    }
                }        
            }
            
        }
        
        //echo $resp;
        return $result;        
    }
    
}

