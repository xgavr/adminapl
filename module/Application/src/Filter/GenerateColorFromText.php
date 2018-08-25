<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

use Zend\Filter\AbstractFilter;

/**
 * Генерирует  color (#000000) на основание введенного текста
 * https://gist.github.com/mrkmg/1607621
 * @author Daddy
 */
class GenerateColorFromText extends AbstractFilter
{
    
    // Доступные опции фильтра.
    protected $options = [
        'min_brightness' => 100,
        'spec' => 10,
    ];    

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
        
	// Check inputs
	if(!is_int($this->options['min_brightness'])) throw new \Exception($this->options['min_brightness']." is not an integer");
	if(!is_int($this->options['spec'])) throw new \Exception($this->options['spec']. " is not an integer");
	if($this->options['spec'] < 2 or $this->options['spec'] > 10) throw new \Exception($this->options['spec'] . " is out of range");
	if($this->options['min_brightness'] < 0 or $this->options['min_brightness'] > 255) throw new \Exception($this->options['min_brightness'] . " is out of range");
	
	
	$hash = md5($value);  //Gen hash of text
	$colors = array();
	for($i=0;$i<3;$i++)
		$colors[$i] = max(array(round(((hexdec(substr($hash,$this->options['spec']*$i,$this->options['spec'])))/hexdec(str_pad('',$this->options['spec'],'F')))*255),$this->options['min_brightness'])); //convert hash into 3 decimal values between 0 and 255
		
	if($this->options['min_brightness'] > 0)  //only check brightness requirements if min_brightness is about 100
		while( array_sum($colors)/3 < $this->options['min_brightness'] )  //loop until brightness is above or equal to min_brightness
			for($i=0;$i<3;$i++)
				$colors[$i] += 10;	//increase each color by 10
				
	$output = '';
	
	for($i=0;$i<3;$i++)
		$output .= str_pad(dechex($colors[$i]),2,0,STR_PAD_LEFT);  //convert each color to hex and append to output
	
	return '#'.$output;
        
    }
    
}
