<?php
namespace Application\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * This view helper class displays a menu bar.
 */
class Menu extends AbstractHelper 
{
    /**
     * Menu items array.
     * @var array 
     */
    protected $items = [];
    
    /**
     * Active item's ID.
     * @var string  
     */
    protected $activeItemId = '';
    
    /**
     * Active url.
     * @var string  
     */
    protected $activeUrl = '';
    
    /**
     * Constructor.
     * @param array $items Menu items.
     */
    public function __construct($items=[]) 
    {
        $this->items = $items;
    }
    
    /**
     * Sets menu items.
     * @param array $items Menu items.
     */
    public function setItems($items) 
    {
        $this->items = $items;
    }
    
    /**
     * Sets ID of the active items.
     * @param string $activeItemId
     */
    public function setActiveItemId($activeItemId) 
    {
        $this->activeItemId = $activeItemId;
    }
    
    /**
     * Sets url of the active items.
     * @param string $url
     */
    public function setActiveUrl($url) 
    {
        $this->activeUrl = $url;
    }
    
    /**
     * Renders the menu.
     * @return string HTML code of the menu.
     */
    public function render() 
    {
        if (count($this->items)==0)
            return ''; // Do nothing if there are no items.
        
//        $result = '<nav class="navbar navbar-default navbar-fixed-top" role="navigation">';
//        $result .= '<div class="container-fluid">';
//        $result .= '<div class="navbar-header">';
//        $result .= '<button type="button" class="navbar-toggle" data-toggle="collapse"';
//        $result .= 'data-target=".navbar-ex1-collapse">';
//        $result .= '<span class="sr-only">Toggle navigation</span>';
//        $result .= '<span class="icon-bar"></span>';
//        $result .= '<span class="icon-bar"></span>';
//        $result .= '<span class="icon-bar"></span>';
//        $result .= '</button>';
//        $result .= '</div>';
        $result = '<div class="collapse navbar-collapse navbar-ex1-collapse">';        
        $result .= '<ul class="nav nav-sidebar">';
        
        // Render items
        foreach ($this->items as $item) {
            if(!isset($item['float']) || $item['float']=='left')
                $result .= $this->renderItem($item);
        }
        
        $result .= '</ul>';
        $result .= '<ul class="nav nav-sidebar">';
        
        // Render items
        foreach ($this->items as $item) {
            if(isset($item['float']) && $item['float']=='right')
                $result .= $this->renderItem($item);
        }
        
        $result .= '</ul>';
        $result .= '</div>';
//        $result .= '</nav>';
        
        return $result;
        
    }
    
    /**
     * Renders an item.
     * @param array $item The menu item info.
     * @return string HTML code of the item.
     */
    protected function renderItem($item) 
    {
        $escapeHtml = $this->getView()->plugin('escapeHtml');

        $id = isset($item['id']) ? $item['id'] : '';
        $isActive = ($id==$this->activeItemId);
        $label = isset($item['label']) ? $item['label'] : '';
        $labelHTML = isset($item['labelHTML']) ? $item['labelHTML'] : $escapeHtml($label);
        $isActiveUrl = false;
             
        $result = ''; 
     
//        var_dump($id);
        
        if (isset($item['dropdown'])) {
            
            $dropdownItems = $item['dropdown'];
            
            $result .= '<li class="dropdown' . ($isActive?' active':'') .'">';
            $result .= '<a href="#'.$id.'" class="dropdown-toggle" data-toggle="collapse" aria-expanded="'.($isActive?'true':'false') .'">';
            $result .= $labelHTML . '<b class="caret"></b>';
            $result .= '</a>';
           
            $result .= '<ul id="'.$id.'" class="nav-second collapse '.($isActive?'in':'').'">';
            foreach ($dropdownItems as $item) {
                $isActiveUrl = ($item['link']==$this->activeUrl);
                $link = isset($item['link']) ? $item['link'] : '#';
                $label = isset($item['label']) ? $escapeHtml($item['label']) : '';
                
                $result .= $isActiveUrl?'<li class="active">':'<li>';
                $result .= '<a href="'.$escapeHtml($link).'">'.$label.'</a>';
                $result .= '</li>';
            }
            $result .= '</ul>';
            $result .= '</li>';
            
        } else {        
            $link = isset($item['link']) ? $item['link'] : '#';
            
            $result .= $isActive?'<li class="active">':'<li>';
            $result .= '<a href="'.$escapeHtml($link).'">'.$labelHTML.'</a>';
            $result .= '</li>';
        }
    
        return $result;
    }
}
