<?php
namespace User\Filter;

use Laminas\Filter\AbstractFilter;

// Локализация даты.
class Rudate extends AbstractFilter 
{    
       
  // Конструктор.
  public function __construct($options = null) 
  {     
    // Задает опции фильтра (если они предоставлены).
            
  }
    
    /**
     * Отличительной чертой именно этой функции является высокая скорость работы, по сравнению с аналогами.
     * @param string $format - The format of the outputted date string.
     * F Полное наименование месяца, например Января или Марта от Января до Декабря
     * M Сокращенное наименование месяца, 3 символа От Янв до Дек
     * l (строчная 'L') Полное наименование дня недели От Воскресенье до Суббота
     * D Сокращенное наименование дня недели, 2 символа от Вс до Сб
     * остальные варианты форматирования см. функцию date() в мануале.
     * @param mixed $timestamp is optional and defaults to the value of time()
     * если в $timestamp не цифра, то функция пытается получить $timestamp при помощи strtotime($timestamp)
     * @param bool $nominative_month - Полное наименование месяца (F) в именительном падеже, влияет только если в $format присутствует 'F'
     * если $nominative_month истина, то: F Полное наименование месяца, например Январь или Март от Январь до Декабрь 
     * если $nominative_month ложь, то: F Полное наименование месяца, например Января или Марта от Января до Декабря
     * @return a string formatted according to the given format string using the given integer/string timestamp or the current time if no timestamp is given.
     */
    private function rudate($format, $timestamp = 0, $nominative_month = false)
    {
        if(!$timestamp) $timestamp = time();
        elseif(!preg_match("/^[0-9]+$/", $timestamp)) $timestamp = strtotime($timestamp);

        $F = $nominative_month ? array(1=>"Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь") : array(1=>"Января", "Февраля", "Марта", "Апреля", "Мая", "Июня", "Июля", "Августа", "Сентября", "Октября", "Ноября", "Декабря");
        $M = array(1=>"Янв", "Фев", "Мар", "Апр", "Май", "Июн", "Июл", "Авг", "Сен", "Окт", "Ноя", "Дек");
        $l = array("Воскресенье", "Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота");
        $D = array("Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб");

        $format = str_replace("F", $F[date("n", $timestamp)], $format);
        $format = str_replace("M", $M[date("n", $timestamp)], $format);
        $format = str_replace("l", $l[date("w", $timestamp)], $format);
        $format = str_replace("D", $D[date("w", $timestamp)], $format);

        return date($format, $timestamp);
    }	

    public function filter($format, $timestamp = 0, $nominative_month = true) 
    {                
        return $this->rudate($format, $timestamp, $nominative_month);
    }    
}