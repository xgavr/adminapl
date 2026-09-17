<?php
namespace Api\V1\Rest\ApiQrcode;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;
use Laminas\Filter\ToFloat;
use Bank\Entity\QrCode;
use Admin\Filter\ClickFilter;

class ApiQrcodeResource extends AbstractResourceListener
{
    
    /**
     * Sbp manager.
     * @var \Bank\Service\SbpManager
     */
    private $sbpManager;    

    public function __construct($sbpManager) 
    {
       $this->sbpManager = $sbpManager;       
    }
    
    private function getPrepaymentAmounts($total) {
        // 1. Рассчитываем чистые проценты
        $prepayment20 = $total * 0.20;
        $prepayment50 = $total * 0.50;

        // 2. Определяем шаг округления в зависимости от размера заказа
        if ($total < 10000) {
            $roundTo = 100; // Округляем до 100 руб. для заказов меньше 10 000 руб.
        } else {
            $roundTo = 1000; // Округляем до 1000 руб. для крупных заказов
        }

        // 3. Округляем в ближайшую сторону (round). 
        // Если нужно строго в большую сторону, замените round() на ceil()
        $final20 = round($prepayment20 / $roundTo) * $roundTo;
        $final50 = round($prepayment50 / $roundTo) * $roundTo;

        // Защита от нулевых сумм (если после округления получился 0, ставим минимальный шаг)
        if ($final20 < $roundTo && $prepayment20 > 0) $final20 = $roundTo;
        if ($final50 < $roundTo && $prepayment50 > 0) $final50 = $roundTo;

        return [
            'prepayment_20' => $final20,
            'prepayment_50' => $final50
        ];
    }
    
    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data)
    {
        if (is_object($data)){
            if (!empty($data->order) && !empty($data->amount)){
                $toFloat = new ToFloat();
                $qrCode = $this->sbpManager->registerQrCode([
                    'orderAplId' => $data->order,
                    'amount' => $toFloat->filter($data->amount),
                ]);
                
                if ($qrCode){
                    $clickFilter = new ClickFilter();
                    $result = $qrCode->toMsg();
                    $result['payloadShort'] = $clickFilter->filter($result['payload']);
                    
                    //Добавить предоплаты
                    $prepaymets = $this->getPrepaymentAmounts($data->amount);
//                    var_dump($prepaymets);
                    
                    if (!empty($prepaymets['prepayment_20'])){
                        $qrCodeP20 = $this->sbpManager->registerQrCode([
                            'orderAplId' => $data->order,
                            'amount' => $toFloat->filter($prepaymets['prepayment_20']),
                            'prepay' => true,
                        ]);
                        
                        if ($qrCodeP20){
                             $result['p20'] = $qrCodeP20->toMsg();
                             $result['p20']['payloadShort'] = $clickFilter->filter($result['p20']['payload']);
                        }                    
                    } 
                    
                    if (!empty($prepaymets['prepayment_50'])){
                        $qrCodeP50 = $this->sbpManager->registerQrCode([
                            'orderAplId' => $data->order,
                            'amount' => $toFloat->filter($prepaymets['prepayment_50']),
                            'prepay' => true,
                        ]);
                        
                        if ($qrCodeP50){
                             $result['p50'] = $qrCodeP50->toMsg();
                             $result['p50']['payloadShort'] = $clickFilter->filter($result['p50']['payload']);
                        }                    
                    }    
                                        
                    return $result;
                }
            }
        }
        return new ApiProblem(404, 'Не верные данные');
    }

    /**
     * Delete a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id)
    {
        return new ApiProblem(405, 'The DELETE method has not been defined for individual resources');
    }

    /**
     * Delete a collection, or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function deleteList($data)
    {
        return new ApiProblem(405, 'The DELETE method has not been defined for collections');
    }

    /**
     * Fetch a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function fetch($id)
    {
        return new ApiProblem(405, 'The GET method has not been defined for individual resources');
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = [])
    {
        if (is_object($params)){
            if (!empty($params->order) && !empty($params->amount)){
                $toFloat = new ToFloat();
                $qrCode = $this->sbpManager->registerQrCode([
                    'orderAplId' => $params->order,
                    'amount' => $toFloat->filter(round($params->amount/100, 2)),
                ]);
                
                if ($qrCode){
                    $clickFilter = new ClickFilter();
                    $result = $qrCode->toMsg();
                    $result['payloadShort'] = $clickFilter->filter($result['payload']);
                    return [
                        'qrcode' => $result,
                    ];
                }
            }
        }
        return new ApiProblem(405, 'The GET method has not been defined for collections');
    }

    /**
     * Patch (partial in-place update) a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patch($id, $data)
    {
        return new ApiProblem(405, 'The PATCH method has not been defined for individual resources');
    }

    /**
     * Patch (partial in-place update) a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patchList($data)
    {
        return new ApiProblem(405, 'The PATCH method has not been defined for collections');
    }

    /**
     * Replace a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function replaceList($data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for collections');
    }

    /**
     * Update a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function update($id, $data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for individual resources');
    }
}
