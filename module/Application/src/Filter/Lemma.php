<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Zend\Filter\AbstractFilter;
use phpMorphy;

/**
 * Получить базовую форму слов в предложении
 * 
 *
 * @author Daddy
 */
class Lemma extends AbstractFilter
{
    
    // Доступные опции фильтра.
    protected $options = [
        // storage type, follow types supported
        // PHPMORPHY_STORAGE_FILE - use file operations(fread, fseek) for dictionary access, this is very slow...
        // PHPMORPHY_STORAGE_SHM - load dictionary in shared memory(using shmop php extension), this is preferred mode
        // PHPMORPHY_STORAGE_MEM - load dict to memory each time when phpMorphy intialized, this useful when shmop ext. not activated. Speed same as for PHPMORPHY_STORAGE_SHM type
        //'storage' => PHPMORPHY_STORAGE_FILE,
        // Enable prediction by suffix
        'predict_by_suffix' => true, 
        // Enable prediction by prefix
        'predict_by_db' => true,
        // TODO: comment this
        'graminfo_as_text' => true,
    ];

    protected $dictsPath = 'vendor/cijic/phpmorphy/libs/phpmorphy/dicts';

    // Конструктор.
    public function __construct($options = null) 
    {     
        // Задает опции фильтра (если они предоставлены).
        if(is_array($options)) {
            $this->options = $options;
        }    
    }
    
    public function filter($value)
    {
        
        $lang = 'ru_RU';
        
        if(function_exists('iconv')) {
            foreach($value as &$word) {
                $word = mb_strtoupper($word, 'utf-8');        
            }
            unset($word);
        }        
        
        $morphy = new phpMorphy($this->dictsPath, $lang, $this->options);
        
        $result = [];
        foreach ($value as $word){
            $base = $morphy->getBaseForm($word);
            $all = $morphy->getAllForms($word);
            $part_of_speech = $morphy->getPartOfSpeech($word);      
            // $base = $morphy->getBaseForm($word, phpMorphy::NORMAL); // normal behaviour
            // $base = $morphy->getBaseForm($word, phpMorphy::IGNORE_PREDICT); // don`t use prediction
            // $base = $morphy->getBaseForm($word, phpMorphy::ONLY_PREDICT); // always predict word
            $is_predicted = $morphy->isLastPredicted(); // or $morphy->getLastPredictionType() == phpMorphy::PREDICT_BY_NONE
            $is_predicted_by_db = $morphy->getLastPredictionType() == phpMorphy::PREDICT_BY_DB;
            $is_predicted_by_suffix = $morphy->getLastPredictionType() == phpMorphy::PREDICT_BY_SUFFIX;
            // this used for deep analysis
            $collection = $morphy->findWord($word);

            if(false === $collection) {
                $result[] = $word;
                continue;
            }

//            echo $is_predicted ? '-' : '+', $word, "\n";
//            echo 'lemmas: ', implode(', ', $base), "\n";
//            echo 'all: ', implode(', ', $all), "\n";
//            echo 'poses: ', implode(', ', $part_of_speech), "\n";

            // TODO: $collection->getByPartOfSpeech(...);
            foreach($collection as $paradigm) {
                $result[] = $paradigm->getBaseForm();
                // TODO: $paradigm->getAllForms();
                // TODO: $paradigm->hasGrammems(array('', ''));
                // TODO: $paradigm->getWordFormsByGrammems(array('', ''));
                // TODO: $paradigm->hasPartOfSpeech('');
                // TODO: $paradigm->getWordFormsByPartOfSpeech('');

//                echo "lemma: ", $paradigm[0]->getWord(), "\n";
//                foreach($paradigm->getFoundWordForm() as $found_word_form) {
//                    echo
//                        $found_word_form->getWord(), ' ',
//                        $found_word_form->getPartOfSpeech(), ' ',
//                        '(', implode(', ', $found_word_form->getGrammems()), ')',
//                        "\n";
//                }
//                echo "\n";

//                foreach($paradigm as $word_form) {
                    // TODO: $word_form->getWord();
                    // TODO: $word_form->getFormNo();
                    // TODO: $word_form->getGrammems();
                    // TODO: $word_form->getPartOfSpeech();
                    // TODO: $word_form->hasGrammems(array('', ''));
//                }
            }
        }    
        
        return $result;
    }
    
}
