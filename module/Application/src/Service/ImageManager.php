<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service;

use Application\Validator\FileExtensionValidator;
use Application\Entity\Images;
use Zend\Validator\File\IsCompressed;
use Zend\Filter\Decompress;

/**
 * Description of ImageManager
 *
 * @author Daddy
 */
class ImageManager {
    
    const IMAGE_FILE_EXTENSIONS   = 'jpg, jpeg, bmp, png, tif'; //допустимые расширения файлов c картинками
    
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /*
     * @var Admin\Service\PostManager
     */
    private $postManager;
  
    /*
     * @var Admin\Service\FtpManager
     */
    private $ftpManager;
  
    /*
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;

    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $postManager, $ftpManager, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->postManager = $postManager;
        $this->ftpManager = $ftpManager;
        $this->adminManager = $adminManager;
    }
    
    /**
     * Проверка на файл с картинками
     * 
     * @param string $filename
     * @return bool
     */
    public function isImageFile($filename)
    {
        $validator = new FileExtensionValidator(self::IMAGE_FILE_EXTENSIONS);
        
        return $validator->isValid($filename);
    }
    
    /**
     * Проверка на файл с архивом
     * 
     * @param string $filename
     * @return bool
     */
    public function isCompressFile($filename)
    {
        $validator = new IsCompressed();
        return $validator->isValid($filename);
    }
    
    /**
     * Путь от корня
     * 
     * @param string $filename
     */
    public function publicPath($filename)
    {
        return Images::publicPath($filename);
    }
    
    /**
     * Распаковать файл архива
     * 
     * @param string $filename
     * @return null
     */
    public function decompress($filename)
    {
        $pathinfo = pathinfo($filename);
        $validator = new IsCompressed();
        if ($validator->isValid($filename)){
            setlocale(LC_ALL,'ru_RU.UTF-8');
            $filter = new Decompress([
                'adapter' => $pathinfo['extension'],
                'options' => [
                    'target' => $pathinfo['dirname'],
                ],
            ]);
            if ($filter->filter($filename)){
                unlink($filename);
            }
        }
        return;
    }
    
    /**
     * Конвертировать tiff в jpg
     * 
     * @param string $filename
     * @return string
     */
    public function tiff2jpg($filename)
    {
        $image = new \Imagick($filename);
        $image->setimageformat('jpg');
        $image->writeimage($filename);
        
        return $filename;
    }
    
    /**
     * Проверка почты в ящике для картинок
     * 
     */
    public function getImageByMail()
    {
        
        $priceSettings = $this->adminManager->getPriceSettings();
        
        if ($priceSettings['image_mail_box'] && $priceSettings['image_mail_box_password']){
            $box = [
                'host' => 'imap.yandex.ru',
                'server' => '{imap.yandex.ru:993/imap/ssl}',
                'user' => $priceSettings['image_mail_box'],
                'password' => $priceSettings['image_mail_box_password'],
                'leave_message' => false,
            ];

            $mailList = $this->postManager->readImap($box);

            if (count($mailList)){
                foreach ($mailList as $mail){
                    if (isset($mail['attachment'])){
                        foreach($mail['attachment'] as $attachment){
                            if ($attachment['filename'] && file_exists($attachment['temp_file'])){
                                if (file_exists($attachment['temp_file'])){ 
                                    $targetFolder = $this->entityManager->getRepository(Images::class)
                                            ->getTmpImageFolder();
                                    
                                    $filename = $targetFolder.'/'.$attachment['filename'];

                                    if (copy($attachment['temp_file'], $filename)){
                                        unlink($attachment['temp_file']);  
                                        
                                        $this->decompress($filename);
                                    }
                                }    
                            }
                        }
                    }
                }
            }
        }    
        
        return;
    }    
}
