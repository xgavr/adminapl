<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Entity\TokenGroup;


/**
 * Description of GroupSite
 * @ORM\Entity(repositoryClass="\Application\Repository\GroupSiteRepository")
 * @ORM\Table(name="group_site")
 * @author Daddy
 */
class GroupSite 
{
    
     // Status constants.
    const STATUS_ACTIVE       = 1; // Active user.
    const STATUS_RETIRED      = 2; // Retired user.       

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
     * @ORM\Column(name="status")  
     */
    protected $status;        

    /**
    * @ORM\OneToMany(targetEntity="\Application\Entity\GroupSite", mappedBy="siteGroups")
    * @ORM\JoinColumn(name="id", referencedColumnName="group_site_id")
    */
    private $siteGroups;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\GroupSite", inversedBy="siteGroup") 
     * @ORM\JoinColumn(name="group_site_id", referencedColumnName="id")
     */
    private $siteGroup;
    
    
    /**
    * @ORM\OneToMany(targetEntity="\Application\Entity\TokenGroup", mappedBy="groupSite")
    * @ORM\JoinColumn(name="id", referencedColumnName="group_site_id")
    */
    private $tokenGroups;

    public function __construct() {
        $this->tokenGroups = new ArrayCollection();
        $this->siteGroups = new ArrayCollection();
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
        return $this->slug;
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
     * Returns user status as string.
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
    
    public function getSiteGroup() {
        return $this->siteGroup;
    }
        
    public function getSiteGroupId() {
        return ($this->getSiteGroup()) ? $this->getSiteGroup()->getId():null;
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
            'image' => $this->getImage(),
            'level' => $this->getLevel(),
            'name' => $this->getName(),
            'slug' => $this->getSlug(),
            'sort' => $this->getSort(),
            'status' => $this->getStatus(),
            'groupSite' => $this->getSiteGroupId(),
        ];
        
        return $result;
    }            
        
}
