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
        $result .= '                    $result["artname"] = trim($desc["col3"]);'.PHP_EOL;
        $result .= '                    $result["price"] = $this->_getPrice($desc["col4"]);'.PHP_EOL;
        $result .= '                    $result["presence"]  = (trim($desc["col5"]) && trim($desc["col5"]) != "-") ? 1:0;'.PHP_EOL;
        $result .= '                    $result["rest"] = trim($desc["col5"]);'.PHP_EOL;
        $result .= '                    $result["comp"] = trim($desc["col7"]);'.PHP_EOL;
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
            $key =  md5($rawprice->getRaw()->getSupplier()->getAplId().":".$this->codeFilter->filter($rawprice->getCode()->getCode()).":".$this->nameFilter->filter($rawprice->getUnknownProducer()));
            return [
                    'key' => $key,
                    'parent' => $rawprice->getCode()->getGood()->getAplId(),
                    'name' => $rawprice->getRaw()->getSupplier()->getAplId(),
                    'iid' => $rawprice->getIid(),
                    'art' => $codeFilter->filter($rawprice->getCode()->getCode()),
                    'price' => $rawprice->getRealPrice(),
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
                ];         
        }    
    }
    
}
