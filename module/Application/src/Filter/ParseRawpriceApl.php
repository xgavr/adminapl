<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Laminas\Filter\AbstractFilter;
use Application\Entity\Rawprice;
use Application\Filter\ArticleCode;
use Application\Filter\ProducerName;

/**
 * Строка прайса в формат АПЛ
 *
 * @author Daddy
 */
class ParseRawpriceApl extends AbstractFilter
{
    protected $codeFilter;
    
    protected $nameFilter;
    
    protected $aplGoodId;
    
    // Доступные опции фильтра.
    protected $options = [
    ];    

    // Конструктор.
    public function __construct($options = null) 
    {     
        $this->codeFilter = new ArticleCode();
        $this->nameFilter = new ProducerName();
        // Задает опции фильтра (если они предоставлены).
        if(is_array($options)) {
            if(isset($options['format'])){
            }
            if(isset($options['aplGoodId'])){
                $this->aplGoodId = $options['aplGoodId'];
            }
        }    
    }
    
    /**
     * Подготовка скрипта для фильтра АПЛ
     * @return string
     */
    protected function _pars_script()
    {
        $result  = '            if (isset($desc["col0"])) {'.PHP_EOL;
        $result .= '                if ($desc["col0"] == "adm") { // для всех поставщиков'.PHP_EOL;
        $result .= '                    $result["art"] = trim($desc["col1"]);'.PHP_EOL;
        $result .= '                    $result["makername"] = trim($desc["col2"]);'.PHP_EOL;
        $result .= '                    $result["brand"] = trim($desc["col2"]);'.PHP_EOL;
        $result .= '                    $result["artname"] = trim($desc["col3"]);'.PHP_EOL;
        $result .= '                    $result["price"] = $this->_getPrice($desc["col4"]);'.PHP_EOL;
        $result .= '                    $result["presence"]  = (trim($desc["col5"]) && trim($desc["col5"]) != "-") ? 1:0;'.PHP_EOL;
        $result .= '                    $result["rest"] = trim($desc["col5"]);'.PHP_EOL;
        $result .= '                    $result["comp"] = trim($desc["col7"]);'.PHP_EOL;
        $result .= '                    $result["order"] = trim($desc["col16"]);'.PHP_EOL;
        $result .= '                    if ($result["order"]) { // есть резерв'.PHP_EOL;
        $result .= '                        $result["reserve"] = trim($desc["col17"]);'.PHP_EOL;
        $result .= '                    }'.PHP_EOL;
        $result .= ''.PHP_EOL;
        $result .= '                    return $result;'.PHP_EOL;
        $result .= '                }'.PHP_EOL;
        $result .= '            }'.PHP_EOL;
        
        return $result;
    }
    
    /**
     * 
     * @param Rawprice $rawprice
     * @return array
     */
    public function filter($rawprice = null)
    {
        if ($rawprice == null){
            return $this->_pars_script();
        } else {
            if (!isset($this->aplGoodId)){
                return;
            }

            $key =  md5($rawprice->getRaw()->getSupplier()->getAplId().":".$this->codeFilter->filter($rawprice->getArticle()).":".$this->nameFilter->filter($rawprice->getProducer()));
            return [
                    'key' => $key,
                    'parent' => $this->aplGoodId,
                    'name' => $rawprice->getRaw()->getSupplier()->getAplId(),
                    'iid' => $rawprice->getIid(),
                    'art' => $this->codeFilter->filter($rawprice->getArticle()),
                    'price' => $rawprice->getRealPrice(),
                    'rawdate' => $rawprice->getRaw()->getDateCreated(),
                    'desc' => 'col0=adm|'
                    . 'col1='.$rawprice->getArticle().'|'
                    . 'col2='.$rawprice->getProducer().'|'
                    . 'col3='.$rawprice->getGoodname().'|'
                    . 'col4='.$rawprice->getRealPrice().'|'
                    . 'col5='.$rawprice->getRealRest().'|'
                    . 'col6='.$rawprice->getIid().'|'
                    . 'col7='.$rawprice->getLot().'|'
                    . 'col8='.$rawprice->getUnit().'|'
                    . 'col9='.$rawprice->getBar().'|'
                    . 'col10='.$rawprice->getCurrency().'|'
                    . 'col11='.$rawprice->getWeight().'|'
                    . 'col12='.$rawprice->getCountry().'|'
                    . 'col13='.$rawprice->getMarkdown().'|'
                    . 'col14='.$rawprice->getSale().'|'
                    . 'col15='.$rawprice->getPack().'|'
                    . 'col16='.$rawprice->getComment().'|'
                    . 'col17='.$rawprice->getSale().'|'
                ];         
        }    
    }
    
}
