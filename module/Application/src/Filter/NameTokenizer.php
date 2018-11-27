<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

declare(strict_types=1);

namespace Application\Filter;

#use Zend\Filter\AbstractFilter;
use Phpml\Exception\InvalidArgumentException;
use Phpml\Tokenization\Tokenizer;
use Application\Filter\Lemma;


/**
 * Разбивает наименование товара на токены
 * 
 *
 * @author Daddy
 */
class NameTokenizer implements Tokenizer
{
    
    // Доступные опции фильтра.
    protected $options = [
    ];  
    
    protected $searchReplace = ['(', ')'];
    
    protected $lemmaFilter;

    // Конструктор.
    public function __construct($options = null) 
    {     
        // Задает опции фильтра (если они предоставлены).
        if(is_array($options)) {
            $this->options = $options;
        }    
        
        $this->lemmaFilter = new Lemma();
    }
    
    public function tokenize($value): array
    {
        //$text = mb_strtolower(str_replace($this->searchReplace, '', $value), 'utf-8');
        
        $result = preg_split('/[\pZ\pC]+/u', $value, -1, PREG_SPLIT_NO_EMPTY);

        if ($result === false) {
            throw new InvalidArgumentException('preg_split failed on: '.$text);
        }
        
        return $this->lemmaFilter->filter($result);
    }
    
}
