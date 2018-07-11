<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

Namespace Admin\Service;

use Zend\Filter\File\RenameUpload;

class AnnManager
{

    const DATA_DIR = './data/ann/'; //Папака с данными
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    public function __construct($entityManager)
    {
        if (!is_dir(self::DATA_DIR)){
            mkdir(self::DATA_DIR);
        }        
        
        $this->entityManager = $entityManager;
    }
    
    public function simpleTrain()
    {
        $num_input = 2;
        $num_output = 1;
        $num_layers = 3;
        $num_neurons_hidden = 3;
        $desired_error = 0.001;
        $max_epochs = 500000;
        $epochs_between_reports = 1000;
        $ann = fann_create_standart($num_layers, $num_input, $num_neurons_hidden, $num_output);
        if ($ann) {
            fann_set_activation_function_hidden($ann, FANN_SIGMOID_SYMMETRIC);
            fann_set_activation_function_output($ann, FANN_SIGMOID_SYMMETRIC);
            $filename = self::DATA_DIR . "xor.data";
            
            if (file_exists($filename)){
                if (fann_train_on_file($ann, $filename, $max_epochs, $epochs_between_reports, $desired_error))
                    fann_save($ann, self::DATA_DIR . "xor_float.net");
            }    
            
            fann_destroy($ann);
        }        
    }
    
}
