<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Images
 * @ORM\Entity(repositoryClass="\Application\Repository\ImageRepository")
 * @ORM\Table(name="images")
 * @author Daddy
 */
class Images {
    
    const STATUS_UNKNOWN    = 1; // источник картики неопределен.
    const STATUS_TD   = 2; // картинка из текдока.
    const STATUS_SUP   =3; //картинка от поставщика
    const STATUS_HAND   =4; //картинка добавлена вручную
    
    const SIMILAR_UNKNOWN = 1; //картинка не определена 
    const SIMILAR_MATCH = 2; //картинка совпадает
    const SIMILAR_SIMILAR = 3; //картинка похожа
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="name")   
     */
    protected $name;

    /**
     * @ORM\Column(name="path")   
     */
    protected $path;

    /**
     * @ORM\Column(name="status")   
     */
    protected $status;
    
    /**
     * @ORM\Column(name="similar")   
     */
    protected $similar;
    
    /**
    * @ORM\ManyToOne(targetEntity="Application\Entity\Goods", inversedBy="images")
    * @ORM\JoinColumn(name="good_id", referencedColumnName="id")    
    * 
    */
    protected $good;
     
    /**
    * @ORM\ManyToOne(targetEntity="Application\Entity\Producer", inversedBy="images")
    * @ORM\JoinColumn(name="producer_id", referencedColumnName="id")    
    * 
    */
//    protected $producer;
     
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getName() 
    {
        return $this->name;
    }

    public function setName($name) 
    {
        $this->name = $name;
    }     

    public function getPath() 
    {
        return $this->path;
    }

    public static function publicPath($path)
    {
        return str_replace('./public', '', $path);
    }
    
    public function getPublicPath() 
    {
        if (file_exists($this->path)){
            $fileInfo = pathinfo($this->path);
           // if ($fileInfo['extension'] && strtoupper($fileInfo['extension']) != 'PDF'){
            if ($fileInfo['extension']){
                return $this->publicPath($this->path);
            }                        
        }
        $uriValidator = new \Laminas\Validator\Uri();
        if ($uriValidator->isValid($this->path)){
            return $this->path;
        }
        
        return;
    }
    
    public function getTransferPath() 
    {
        if (file_exists($this->path)){
            $fileInfo = pathinfo($this->publicPath($this->path));
           // if ($fileInfo['extension'] && strtoupper($fileInfo['extension']) != 'PDF'){
            if ($fileInfo['extension']){
                return $fileInfo['dirname'].'/'. preg_replace('/\s/i', '%20', htmlentities($fileInfo['basename']));
            }                        
        }
        return;
    }
    
    /**
     * Проверка расширения
     * @param string $filename
     * @return boolean
     */
    public static function isToTransfer($filename)
    {
        $fileInfo = pathinfo($filename);
        $ext = null;
        if (isset($fileInfo['extension'])){
            $ext = $fileInfo['extension'];
        }
        
        if (!$ext){
            return FALSE;
        }
        
        if (strtoupper($ext) == 'PDF'){
            return false;
        }
        if (strtoupper($ext) == 'SPIN'){
            return false;
        }

        $uriValidator = new \Laminas\Validator\Uri();
        if (!$uriValidator->isValid($filename)){
            return FALSE;
        }

//        var_dump($ext);
        return true;
    }
    
    public function allowTransfer()
    {
        return $this->isToTransfer($this->path);
    }

    public function setPath($path) 
    {
        $this->path = $path;
    }     
    
    public function getStatus() 
    {
        return $this->status;
    }

    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getStatusList() 
    {
        return [
            self::STATUS_UNKNOWN => 'Неизвестно',
            self::STATUS_TD => 'ТекДок',
            self::STATUS_SUP => 'Поставщик',
            self::STATUS_HAND => 'Вручную',
        ];
    }    
    
    /**
     * Returns image status as string.
     * @return string
     */
    public function getStatusAsString()
    {
        $list = self::getStatusList();
        if (isset($list[$this->status])) {
            return $list[$this->status];
        }

        return 'Unknown';
    }    
    
    public function setStatus($status) 
    {
        $this->status = $status;
    }     
    
    public function getSimilar() 
    {
        return $this->similar;
    }

    /**
     * Returns possible similar as array.
     * @return array
     */
    public static function getSimilarList() 
    {
        return [
            self::SIMILAR_UNKNOWN => 'Неизвестно',
            self::SIMILAR_MATCH => 'Точно',
            self::SIMILAR_SIMILAR => 'Похоже',
        ];
    }    
    
    /**
     * Returns image similar as string.
     * @return string
     */
    public function getSimilarAsString()
    {
        $list = self::getSimilarList();
        if (isset($list[$this->similar])) {
            return $list[$this->similar];
        }

        return 'Unknown';
    }    

    /**
     * Returns possible similar as array.
     * @return array
     */
    public static function getSimilarAplList() 
    {
        return [
            self::SIMILAR_UNKNOWN => '333',
            self::SIMILAR_MATCH => '111',
            self::SIMILAR_SIMILAR => '222',
        ];
    }    
    
    /**
     * Returns image similar as string.
     * @return string
     */
    public function getSimilarAplAsString()
    {
        $list = self::getSimilarAplList();
        if (isset($list[$this->similar])) {
            return $list[$this->similar];
        }

        return 'Unknown';
    }    


    public function setSimilar($similar) 
    {
        $this->similar = $similar;
    }   
    
    /**
     * Возвращает связанный товар.
     * @return \Application\Entity\Goods
     */
    public function getGood() 
    {
        return $this->good;
    }
    
    /**
     * Задает связанный товар.
     * @param \Application\Entity\Goods $good
     */
    public function setGood($good) 
    {
        $this->good = $good;
        $good->addImage($this);
    }   
    
    /**
     * Возвращает связанный производитель.
     * @return \Application\Entity\Producer
     */
//    public function getProducer() 
//    {
//        return $this->producer;
//    }
    
    /**
     * Задает связанный производитель.
     * @param \Application\Entity\Producer $producer
     */
//    public function setProducer($producer) 
//    {
//        $this->producer = $producer;
//    }    
}
