<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

Namespace Admin\Service;

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
        $ann = fann_create_standard($num_layers, $num_input, $num_neurons_hidden, $num_output);
        if ($ann) {
            fann_set_activation_function_hidden($ann, FANN_SIGMOID_SYMMETRIC);
            fann_set_activation_function_output($ann, FANN_SIGMOID_SYMMETRIC);
            $filename = realpath(self::DATA_DIR . "xor.data");
            
            if (file_exists($filename)){
                $target = self::DATA_DIR . "xor_float.net";

                if (!file_exists($target)){
                    $fh = fopen($target, 'w') or die("Can't create file");
                    fclose($fh);
                }
                if (fann_train_on_file($ann, $filename, $max_epochs, $epochs_between_reports, $desired_error))
                    fann_save($ann, realpath($target));
            }    
            
            fann_destroy($ann);
        }        
    }
    
    public function test()
    {
        $train_file = (self::DATA_DIR  . "xor_float.net");
        if (!is_file($train_file))
            die("The file xor_float.net has not been created! Please run simple_train.php to generate it");

        $ann = fann_create_from_file(realpath($train_file));
        if (!$ann)
            die("ANN could not be created");

        $input = array(11, 1);
        $calc_out = fann_run($ann, $input);
        printf("xor test (%f,%f) -> %f\n", $input[0], $input[1], $calc_out[0]);
        fann_destroy($ann);        
    }
    
    /*
     * Подготовка обучающей выборки для решения по удалению старых прайсов
     * @return file - файл с данными для обучения в формате fann
     */
    public function deleteRawTrain()            
    {

        $num_input = 2;
        $num_output = 1;
        $num_layers = 3;
        $num_neurons_hidden = 3;
        $desired_error = 0.001;
        $max_epochs = 500000;
        $epochs_between_reports = 1000;
        $ann = fann_create_standard($num_layers, $num_input, $num_neurons_hidden, $num_output);
        if ($ann) {
            fann_set_activation_function_hidden($ann, FANN_SIGMOID_SYMMETRIC);
            fann_set_activation_function_output($ann, FANN_SIGMOID_SYMMETRIC);
            $filename = realpath(self::DATA_DIR . "delete_raw.data");
            
            if (file_exists($filename)){
                $target = self::DATA_DIR . "delete_raw.net";

                if (!file_exists($target)){
                    $fh = fopen($target, 'w') or die("Can't create file");
                    fclose($fh);
                }
                if (fann_train_on_file($ann, $filename, $max_epochs, $epochs_between_reports, $desired_error))
                    fann_save($ann, realpath($target));
            }    
            
            fann_destroy($ann);
        }        
    }
    
    
    
    public function createAndRun($input, $netFilename)
    {
        $train_file = (self::DATA_DIR  . $netFilename);
        if (!is_file($train_file))
            die("The file $netFilename has not been created! Please run $netFilename to generate it");

        $ann = fann_create_from_file(realpath($train_file));
        if (!$ann)
            die("ANN could not be created");

        $calc_out = fann_run($ann, $input);
        //printf("xor test (%f,%f) -> %f\n", $input[0], $input[1], $calc_out[0]);
        fann_destroy($ann);        
        
        return $calc_out;
    }
    
    public function deleteRawTest()
    {
        $input = array(1, 1);
        $train_file = './data/ann/delete_raw.net';
        if (!is_file($train_file))
            die("The file $train_file has not been created! Please run $train_file to generate it");

        $ann = fann_create_from_file(realpath($train_file));
        if (!$ann)
            die("ANN could not be created");

        $calc_out = fann_run($ann, $input);
        //printf("xor test (%f,%f) -> %f\n", $input[0], $input[1], $calc_out[0]);
        fann_destroy($ann);        
        
        return $calc_out[0];
    }
    
    
    
}
