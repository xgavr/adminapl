<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Filter;

/**
 * Устанавливает количество колонок для четния в файле Эксель
 *
 * @author Daddy
 */
class ExcelColumn implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
{
    
    public function readCell($column, $row, $worksheetName = '') {
        //  Read columns A to Z only
        if (in_array($column,range('A','Z'))) {
            return true;
        }

        return false;
    }
    
}
