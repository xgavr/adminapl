<?php
namespace User\Filter;

use Laminas\Filter\AbstractFilter;

// Этот класс фильтра предназначен для преобразования произвольного номера телефона в 
// локальный или международный формат.
class PhoneFilter extends AbstractFilter 
{    
  // Константы форматов номера.
  const PHONE_FORMAT_LOCAL = 'local'; // Local phone format 
  const PHONE_FORMAT_INTL  = 'intl';  // International phone format 
  const PHONE_FORMAT_DB  = 'db';  // формат для записи в бд 
  const PHONE_FORMAT_RU  = 'ru';  // формат рф 
    
  // Доступные опции фильтра.
  protected $options = [
    'format' => self::PHONE_FORMAT_DB
  ];
    
  // Конструктор.
  public function __construct($options = null) 
  {     
    // Задает опции фильтра (если они предоставлены).
    if(is_array($options)) {
            
      if(isset($options['format']))
        $this->setFormat($options['format']);
    }
  }
    
  // Задает формат номера.
  public function setFormat($format) 
  {        
    // Проверяет входной аргумент.
    if( $format!=self::PHONE_FORMAT_LOCAL &&
       $format!=self::PHONE_FORMAT_DB &&     
       $format!=self::PHONE_FORMAT_RU &&     
       $format!=self::PHONE_FORMAT_INTL ) {            
      throw new \Exception('Invalid format argument passed.');
    }
        
    $this->options['format'] = $format;
  }

  // Возвращает формат номера.
  public function getFormat() 
  {
    return $this->format;
  }  
	
  // Фильтрует телефонный номер.
  public function filter($value) 
  {                
    if(!is_scalar($value)) {
      // Возвращаем нескалярное значение неотфильтрованным.
      return $value;
    }
            
    $value = (string)$value;
        
    if(strlen($value)==0) {
      // Возвращаем пустое значение неотфильтрованным.
      return $value;
    }
        
    // Сперва удаляем все нецифровые символы.
    $digits = preg_replace('#[^0-9]#', '', $value);
        
    $format = $this->options['format'];
        
    if($format == self::PHONE_FORMAT_INTL) {            
      // Дополняем нулями, если число цифр некорректно.
      $digits = str_pad($digits, 11, "0", STR_PAD_LEFT);

      // Добавляем скобки, пробелы и тире.
      $phoneNumber = substr($digits, 0, 1) . ' (' . 
                     substr($digits, 1, 3) . ') ' .
                     substr($digits, 4, 3) . '-' . 
                     substr($digits, 7, 4);
    } elseif ($format == self::PHONE_FORMAT_DB) { 
        
      $digits = substr($digits, -10);  
      // Дополняем нулями, если число цифр некорректно.
      $digits = str_pad($digits, 10, "0", STR_PAD_LEFT);

      $phoneNumber = $digits;
      
    } elseif ($format == self::PHONE_FORMAT_RU) { 
        
      $digits = substr($digits, -10);  
      // Дополняем нулями, если число цифр некорректно.
      $digits = str_pad($digits, 10, "0", STR_PAD_LEFT);

      // Добавляем скобки, пробелы и тире.
      $phoneNumber = '8 (' . 
                     substr($digits, 0, 3) . ') ' .
                     substr($digits, 3, 3) . '-' . 
                     substr($digits, 6, 4);
      
    } else { // self::PHONE_FORMAT_LOCAL
      // Дополняем нулями, если число цифр некорректно
      $digits = str_pad($digits, 7, "0", STR_PAD_LEFT);

      // Добавляем тире.
      $phoneNumber = substr($digits, 0, 3) . '-'. substr($digits, 3, 4);
    }
        
    return $phoneNumber;                
  }    
}