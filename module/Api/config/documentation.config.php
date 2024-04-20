<?php
return [
    'Api\\V1\\Rpc\\Ping\\Controller' => [
        'GET' => [
            'response' => '{
   "ask": "Подтвердить запрос с отметкой времени"
}',
        ],
    ],
    'Api\\V1\\Rest\\ApiOrderInfo\\Controller' => [
        'entity' => [
            'GET' => [
                'response' => '{
   "_links": {
       "self": {
           "href": "/api-order-info[/:api_order_info_id]"
       }
   }
   "orderId": "Номер заказа в adminapl",
   "orderAplId": "Номер заказа в apl",
   "supplierAplId": "Номер поставщика в apl",
   "goodId": "Id товара в adminapl",
   "goodAplId": "Id товара в apl",
   "quantity": "Количество заказано",
   "status": "Заказано - 2, не заказано - 1 (не добавляется)",
   "supplierId": "Id поставщика в adminapl"
}',
            ],
            'PATCH' => [
                'request' => '{
   "orderId": "Номер заказа в adminapl",
   "orderAplId": "Номер заказа в apl",
   "supplierAplId": "Номер поставщика в apl",
   "goodId": "Id товара в adminapl",
   "goodAplId": "Id товара в apl",
   "quantity": "Количество заказано",
   "status": "Заказано - 2, не заказано - 1 (не добавляется)",
   "supplierId": "Id поставщика в adminapl"
}',
                'response' => '{
   "_links": {
       "self": {
           "href": "/api-order-info[/:api_order_info_id]"
       }
   }
   "orderId": "Номер заказа в adminapl",
   "orderAplId": "Номер заказа в apl",
   "supplierAplId": "Номер поставщика в apl",
   "goodId": "Id товара в adminapl",
   "goodAplId": "Id товара в apl",
   "quantity": "Количество заказано",
   "status": "Заказано - 2, не заказано - 1 (не добавляется)",
   "supplierId": "Id поставщика в adminapl"
}',
            ],
        ],
        'collection' => [
            'GET' => [
                'response' => '{
   "_links": {
       "self": {
           "href": "/api-order-info"
       },
       "first": {
           "href": "/api-order-info?page={page}"
       },
       "prev": {
           "href": "/api-order-info?page={page}"
       },
       "next": {
           "href": "/api-order-info?page={page}"
       },
       "last": {
           "href": "/api-order-info?page={page}"
       }
   }
   "_embedded": {
       "api_order_info": [
           {
               "_links": {
                   "self": {
                       "href": "/api-order-info[/:api_order_info_id]"
                   }
               }
              "orderId": "Номер заказа в adminapl",
              "orderAplId": "Номер заказа в apl",
              "supplierAplId": "Номер поставщика в apl",
              "goodId": "Id товара в adminapl",
              "goodAplId": "Id товара в apl",
              "quantity": "Количество заказано",
              "status": "Заказано - 2, не заказано - 1 (не добавляется)",
              "supplierId": "Id поставщика в adminapl"
           }
       ]
   }
}',
            ],
            'POST' => [
                'request' => '{
   "orderId": "Номер заказа в adminapl",
   "orderAplId": "Номер заказа в apl",
   "supplierAplId": "Номер поставщика в apl",
   "goodId": "Id товара в adminapl",
   "goodAplId": "Id товара в apl",
   "quantity": "Количество заказано",
   "status": "Заказано - 2, не заказано - 1 (не добавляется)",
   "supplierId": "Id поставщика в adminapl"
}',
                'response' => '{
   "_links": {
       "self": {
           "href": "/api-order-info[/:api_order_info_id]"
       }
   }
   "orderId": "Номер заказа в adminapl",
   "orderAplId": "Номер заказа в apl",
   "supplierAplId": "Номер поставщика в apl",
   "goodId": "Id товара в adminapl",
   "goodAplId": "Id товара в apl",
   "quantity": "Количество заказано",
   "status": "Заказано - 2, не заказано - 1 (не добавляется)",
   "supplierId": "Id поставщика в adminapl"
}',
            ],
        ],
    ],
    'Api\\V1\\Rest\\ApiClientInfo\\Controller' => [
        'description' => 'Получить информацию о покупателе',
        'collection' => [
            'GET' => [
                'response' => '{
   "_links": {
       "self": {
           "href": "/api-client-info"
       },
       "first": {
           "href": "/api-client-info?page={page}"
       },
       "prev": {
           "href": "/api-client-info?page={page}"
       },
       "next": {
           "href": "/api-client-info?page={page}"
       },
       "last": {
           "href": "/api-client-info?page={page}"
       }
   }
   "_embedded": {
       "api_client_info": [
           {
               "_links": {
                   "self": {
                       "href": "/api-client-info[/:api_client_info_id]"
                   }
               }
              "phone": "Номер телефона покупателя",
              "orderStatus": "выгружать заказы с указанным статусом, либо все, если не указан
STATUS_PROCESSED   = 20; // Обработа
STATUS_CONFIRMED   = 30; // Подтвержден.
STATUS_DELIVERY   = 40; // Доставка.
STATUS_SHIPPED   = 50; // Отгружен.
STATUS_CANCELED  = -10; // Отменен."
           }
       ]
   }
}',
            ],
        ],
    ],
    'Api\\V1\\Rest\\Good\\Controller' => [
        'collection' => [
            'GET' => [
                'response' => '{
   "_links": {
       "self": {
           "href": "/api-good"
       },
       "first": {
           "href": "/api-good?page={page}"
       },
       "prev": {
           "href": "/api-good?page={page}"
       },
       "next": {
           "href": "/api-good?page={page}"
       },
       "last": {
           "href": "/api-good?page={page}"
       }
   }
   "_embedded": {
       "good": [
           {
               "_links": {
                   "self": {
                       "href": "/api-good[/:good_id]"
                   }
               }
              "article": "Артикул",
              "producer": "производитель",
              "detail": "Выводить больше данных о товаре:
- резерв по заказам
- данные о поступлениях",
              "inv": "Выводить больше данных о товаре:
- резерв по заказам
- данные о поступлениях"
           }
       ]
   }
}',
                'description' => 'good - массив товаров найденных по запросу
    
    details - дополнительная информация по товарам
        
        purchases - массив поступлении товара
            docDate - дата поступления
            supplierName - поставщик
            daysTotal - Макс дней на возврат
            daysPassed - прошло дней от поставки
            daysLeft - осталось дней до возврата
        
        rest - массив остатков в разрезе офисов и компаний
             available - доступное количество (остаток - резерв - доставка - на возврате)
             reserve - резерв в заказах
             rest - остаток
             delivery - на доставке
            vozvrat - на возврате

        reserves - массив заказов подтвержденных и на доставке, где присутствует товар',
            ],
        ],
    ],
];
