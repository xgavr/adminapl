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
    /**
     * 
     * @param string $columnAddress
     * @param int $row
     * @param type $worksheetName
     * @return string
     */
    public function readCell($columnAddress, $row, $worksheetName = '')
    {
        //  Read columns A to Z only
        if (in_array($columnAddress, range('A','Z'))) {
            return true;
        }

        return false;
    }
    
}
