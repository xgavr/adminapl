<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Fasade\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Entity\TokenGroup;
use Application\Entity\Goods;


/**
 * Description of GroupSite
 * @ORM\Entity(repositoryClass="\Fasade\Repository\GroupSiteRepository")
 * @ORM\Table(name="group_site")
 * @author Daddy
 */
class GroupSite 
{
    
     // Status constants.
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.       

    const HAS_CHILD       = 1; // имеет подгруппу.
    const HAS_NO_CHILD      = 2; // конечная группа.       

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
     * @ORM\Column(name="full_name")   
     */
    protected $fullName;

    /**
     * @ORM\Column(name="apl_id")   
     */
    protected $aplId;

    /**
     * @ORM\Column(name="code")   
     */
    protected $code;

    /**
     * @ORM\Column(name="level")   
     */
    protected $level;
    
    /**
     * @ORM\Column(name="sort")   
     */
    protected $sort;

    /**
     * @ORM\Column(name="slug")   
     */
    protected $slug;

    /**
     * @ORM\Column(name="description")   
     */
    protected $description;

    /**
     * @ORM\Column(name="image")   
     */
    protected $image;

    /**
     * @ORM\Column(name="good_count")   
     */
    protected $goodCount = 0;
    
    /**
     * @ORM\Column(name="sale_count")   
     */
    protected $saleCount = 0;
    
    /**
     * @ORM\Column(name="status")  
     */
    protected $status;        

    /**
     * @ORM\Column(name="has_child")  
     */
    protected $hasChild;        

    /**
    * @ORM\OneToMany(targetEntity="\Fasade\Entity\GroupSite", mappedBy="siteGroups")
    * @ORM\JoinColumn(name="id", referencedColumnName="group_site_id")
    */
    private $siteGroups;

    /**
     * @ORM\ManyToOne(targetEntity="Fasade\Entity\GroupSite", inversedBy="siteGroup") 
     * @ORM\JoinColumn(name="group_site_id", referencedColumnName="id")
     */
    private $siteGroup;
    
    
    /**
    * @ORM\OneToMany(targetEntity="\Application\Entity\TokenGroup", mappedBy="groupSite")
    * @ORM\JoinColumn(name="id", referencedColumnName="group_site_id")
    */
    private $tokenGroups;

    /**
     * @ORM\ManyToMany(targetEntity="\Application\Entity\Goods", mappedBy="categories")
     */
    protected $goods;    
    
    public function __construct() {
        $this->tokenGroups = new ArrayCollection();
        $this->siteGroups = new ArrayCollection();
        $this->goods = new ArrayCollection();
    }

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

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function getFullName() {
        return $this->fullName;
    }

    public function setFullName($fullName) {
        $this->fullName = $fullName;
        return $this;
    }

    public function getAplId() {
        return $this->aplId;
    }

    public function setAplId($aplId) {
        $this->aplId = $aplId;
        return $this;
    }
    
    public function getCode() {
        return $this->code;
    }

    public function setCode($code) {
        $this->code = $code;
        return $this;
    }

    public function getLevel() {
        return $this->level;
    }

    public function setLevel($level) {
        $this->level = $level;
        return $this;
    }

    public function getSort() {
        return $this->sort;
    }

    public function setSort($sort) {
        $this->sort = $sort;
        return $this;
    }
    
    public function getSlug() {
        return $this->slug ?? $this->name;
    }

    public function setSlug($slug) {
        $this->slug = $slug;
        return $this;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    public function getImage() {
        return $this->image;
    }

    public function setImage($image) {
        $this->image = $image;
        return $this;
    }

    public function getGoodCount() {
        return $this->goodCount;
    }

    public function setGoodCount($goodCount) {
        $this->goodCount = $goodCount;
        return $this;
    }

    public function getSaleCount() {
        return $this->saleCount;
    }

    public function setSaleCount($saleCount) {
        $this->saleCount = $saleCount;
    }

    public function getStatus() {
        return $this->status;
    }

    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getStatusList() 
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_RETIRED => 'Retired'
        ];
    }    
    
    /**
     * Returns status as string.
     * @return string
     */
    public function getStatusAsString()
    {
        $list = self::getStatusList();
        if (isset($list[$this->status]))
            return $list[$this->status];
        
        return 'Unknown';
    }       
    
    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    public function getHasChild() {
        return $this->hasChild;
    }

    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getHasChildList() 
    {
        return [
            self::HAS_CHILD => 'Есть подгруппы',
            self::HAS_NO_CHILD => 'Без подгрупп'
        ];
    }    
    
    /**
     * Returns status as string.
     * @return string
     */
    public function getHasChildAsString()
    {
        $list = self::getHasChildList();
        if (isset($list[$this->hasChild]))
            return $list[$this->hasChild];
        
        return 'Unknown';
    }       
    
    public function setHasChild($hasChild) {
        $this->hasChild = $hasChild;
        return $this;
    }
    
    public function getTokenGroups() {
        return $this->tokenGroups;
    }

    /**
     * 
     * @param TokenGroup $tokenGroup
     */
    public function addTokenGroup($tokenGroup) 
    {
        $this->tokenGroups->add($tokenGroup);
    }
    
    /**
     * 
     * @return ArrayCollection
     */
    public function getSiteGroups() {
        return $this->siteGroups;
    }

    /**
     * 
     * @param GroupSite $groupSite
     */
    public function addGroupSite($groupSite){
        $this->siteGroups->add($groupSite);
    }
    
    /**
     * 
     * @return GroupSite
     */
    public function getSiteGroup() {
        return $this->siteGroup;
    }
        
    public function getSiteGroupId() {
        return ($this->getSiteGroup()) ? $this->getSiteGroup()->getId():null;
    }
        
    public function getSiteGroupAsArray() {
        return ($this->getSiteGroup()) ? $this->getSiteGroup()->toArray():null;
    }
        
    /**
     * 
     * @param GroupSite $siteGroup
     * @return $this
     */
    public function setSiteGroup($siteGroup) {
        $this->siteGroup = $siteGroup;
        if ($siteGroup){
            $siteGroup->addGroupSite($this);
        }    
        
        return $this;
    }
     
    // Возвращает товары, связанные с данной категорией.
    public function getGoods() 
    {
        return $this->goods;
    }
    
    // Добавляет товар в коллекцию товаров, связанных с этой категорией.
    public function addGood($good) 
    {
        $this->goods[] = $good;        
    }   
    
    /**
     * 
     * @param Goods $good
     */
    public function removeGoodAssociation($good) 
    {
        $good->removeCategoryAssociation($this);
    } 
    
    
    /**
     * Массив для формы
     * @return array 
     */
    public function toArray()
    {
        $result = [
            'id' => $this->getId(),
            'code' => $this->getCode(),
            'description' => $this->getDescription(),
            'goodCount' => $this->getGoodCount(),
            'saleCount' => $this->getSaleCount(),
            'image' => $this->getImage(),
            'level' => $this->getLevel(),
            'name' => $this->getName(),
            'slug' => str_replace('/', '_', $this->getFullName()),
            'sort' => $this->getSort(),
            'status' => $this->getStatus(),
            'groupSite' => $this->getSiteGroupAsArray(),
        ];
        
        return $result;
    }            
        
}
