<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

declare(strict_types=1);

namespace Application\Filter;

use Phpml\Exception\InvalidArgumentException;
use Phpml\Tokenization\Tokenizer;
use Application\Filter\Tokenizer as TokenizerFilter;
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
    
    protected $lemmaFilter;

    protected $tokenFilter;

    // Конструктор.
    public function __construct($options = null) 
    {     
        // Задает опции фильтра (если они предоставлены).
        if(is_array($options)) {
            $this->options = $options;
        }    
        
        $this->tokenFilter = new TokenizerFilter();
        $this->lemmaFilter = new Lemma();
    }
    
    public function tokenize($value): array
    {
        $lemms = $this->lemmaFilter->filter($this->tokenFilter->filter($value));

        return array_merge($lemms[1], $lemms[0]);
    }
    
}
