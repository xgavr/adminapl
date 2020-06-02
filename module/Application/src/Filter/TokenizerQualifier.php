<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Laminas\Filter\AbstractFilter;
use Phpml\Tokenization\WhitespaceTokenizer;

/**
 * Разбивает предложение товара на токены и определяем характеристики токена
 * 
 *
 * @author Daddy
 */
class TokenizerQualifier extends AbstractFilter
{
    
    // Доступные опции фильтра.
    protected $options = [
    ];  
    
    protected $searchReplace = ['(', ')', '_', '/', '\\'];
    
    protected $tokenizeFilter;
    
    
    protected function isPattern($word, $patten)
    {
        $patternWord = mb_ereg_replace($patten, '', $word);
        if (mb_strlen($word) == mb_strlen($patternWord) || mb_strlen($patternWord) > 0){
            return 1;
        }
        
        return 0;
    }
    
    // Конструктор.
    public function __construct($options = null) 
    {     
        // Задает опции фильтра (если они предоставлены).
        if(is_array($options)) {
            $this->options = $options;
        }    
        
        $this->tokenizeFilter = new WhitespaceTokenizer();
    }    
    
    public function filter($value)
    {
        $result = [];
        $tokens = $this->tokenizeFilter->tokenize(mb_strtoupper($value));
        
        foreach ($tokens as $key => $token){
            $row = [
                $key, //позиция в предложении
                mb_strlen($token), //длина слова
                $this->isPattern($token, '[^А-ЯЁ]'), // наличие русских букв
                $this->isPattern($token, '[^A-Z]'), // наличие аннглийских букв
                $this->isPattern($token, '[^0-9]'), // наличие цифр
                $this->isPattern($token, '[A-ZА-ЯЁ0-9]'), // наличие прочих символов
                $token, //само слово
            ];
            $result[] = $row;
        }

        return $result;
    }
    
}
