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
];
