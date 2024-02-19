<?php
namespace Application\Service;

use Stock\Entity\Revise;

/**
 * This service is responsible for determining which items should be in the main menu.
 * The items may be different depending on whether the user is authenticated or not.
 */
class NavManager
{
    /**
     * Auth service.
     * @var \Laminas\Authentication\Authentication
     */
    private $authService;
    
    /**
     * Url view helper.
     * @var \Laminas\View\Helper\Url
     */
    private $urlHelper;
    
    /**
     * RBAC manager.
     * @var \User\Service\RbacManager
     */
    private $rbacManager;
    
    /**
     * Constructs the service.
     */
    public function __construct($authService, $urlHelper, $rbacManager) 
    {
        $this->authService = $authService;
        $this->urlHelper = $urlHelper;
        $this->rbacManager = $rbacManager;
    }
    
    /**
     * This method returns menu items depending on whether user has logged in or not.
     */
    public function getMenuItems() 
    {
        $url = $this->urlHelper;
        $items = [];
        
        
//        $items[] = [
//            'id' => 'home',
//            'label' => 'adminapl.ru',
//            'labelHTML' => '<strong>АдминАПЛ</strong><sup>.ru</sup>',
//            'link'  => $url('home')
//        ];
            
        // Display "Login" menu item for not authorized user only. On the other hand,
        // display "Admin" and "Logout" menu items only for authorized users.
        if (!$this->authService->hasIdentity()) {
            $items[] = [
                'id' => 'login',
                'label' => 'Вход',
                'link'  => $url('login'),
                'float' => 'right'
            ];
        } else {
            
            if ($this->rbacManager->isGranted(null, 'client.manage')) {
                $clientDropdownItems = [];
//                $clientDropdownItems = [
//                    'id' => 'shop',
//                    'label' => 'Каталог',
//                    'link'  => $url('shop')
//                ];
//
                $clientDropdownItems[] = [
                    'id' => 'order',
                    'label' => 'Заказы',
                    'link'  => $url('order')
                ];
            
//                $clientDropdownItems[] = [
//                    'id' => 'client',
//                    'label' => 'Звонки',
//                    'link'  => $url('ring')
//                ];
                
                $clientDropdownItems[] = [
                    'id' => 'vt',
                    'label' => 'Возврат товаров',
                    'link'  => $url('vt')
                ];

//                $clientDropdownItems[] = [
//                    'id' => 'revise',
//                    'label' => 'Корректировка долга',
//                    'link'  => $url('revise', ['action' => 'index'], ['query' => ['kind' => Revise::KIND_REVISE_CLIENT]]),
//                ];
                
                $clientDropdownItems[] = [
                    'id' => 'client',
                    'label' => 'Покупатели',
                    'link'  => $url('client')
                ];
                
                $clientDropdownItems[] = [
                    'id' => 'courier',
                    'label' => 'Транспортные компании',
                    'link'  => $url('courier')
                ];
                
                $clientDropdownItems[] = [
                    'id' => 'market',
                    'label' => 'Прайс-листы для ТП',
                    'link'  => $url('market')
                ];

                $clientDropdownItems[] = [
                    'id' => 'marketPlace',
                    'label' => 'Маркеты',
                    'link'  => $url('market-place')
                ];

                $clientDropdownItems[] = [
                    'id' => 'comitent',
                    'label' => 'Отчеты ТП',
                    'link'  => $url('comitent')
                ];

                if (count($clientDropdownItems)!=0) {
                    $items[] = [
                        'id' => 'client',
                        'label' => 'Продажи',
                        'dropdown' => $clientDropdownItems
                    ];
                }                
            }
            
            if ($this->rbacManager->isGranted(null, 'supplier.manage')) {
                $priceDropdownItems = [];

                $priceDropdownItems[] = [
                    'id' => 'ptu',
                    'label' => 'Поступления  товаров',
                    'link'  => $url('ptu')
                ];

                $priceDropdownItems[] = [
                    'id' => 'vtp',
                    'label' => 'Возврат товаров поставщику',
                    'link'  => $url('vtp')
                ];

                $priceDropdownItems[] = [
                    'id' => 'supplier',
                    'label' => 'Поставщики',
                    'link'  => $url('supplier')
                ];

                $priceDropdownItems[] = [
                    'id' => 'supplier',
                    'label' => 'Сверка',
                    'link'  => $url('supplier-revision')
                ];

//                $priceDropdownItems[] = [
//                    'id' => 'revise',
//                    'label' => 'Корректировка долга',
//                    'link'  => $url('revise')
//                ];

//                $priceDropdownItems[] = [
//                    'id' => 'raw_queue',
//                    'label' => 'Очередь прайсов',
//                    'link'  => $url('price', ['action' => 'queue'])
//                ];
                
                $priceDropdownItems[] = [
                    'id' => 'raw_uploaded',
                    'label' => 'Загруженные прайсы',
                    'link'  => $url('raw')
                ];
                
                $priceDropdownItems[] = [
                    'id' => 'bill',
                    'label' => 'Электронные накладные',
                    'link'  => $url('bills')
                ];
                
                if ($this->rbacManager->isGranted(null, 'rate.manage')) {
                    $priceDropdownItems[] = [
                        'id' => 'rate',
                        'label' => 'Расценка',
                        'link'  => $url('rate')
                    ];
                }    

                $priceDropdownItems[] = [
                    'id' => 'cross',
                    'label' => 'Кроссы',
                    'link'  => $url('cross')
                ];
                
                
                if (count($priceDropdownItems)!=0) {
                    $items[] = [
                        'id' => 'raw',
                        'label' => 'Покупки',
                        'dropdown' => $priceDropdownItems
                    ];
                }                
            }
            //Склад
            $stockDropdownItems = [];
            if ($this->rbacManager->isGranted(null, 'stock.manage')) {
                $stockDropdownItems[] = [
                    'id' => 'goods',
                    'label' => 'Товары',
                    'link'  => $url('goods')
                ];
                $stockDropdownItems[] = [
                    'id' => 'st',
                    'label' => 'Списания',
                    'link'  => $url('st')
                ];
                $stockDropdownItems[] = [
                    'id' => 'ot',
                    'label' => 'Оприходования',
                    'link'  => $url('ot')
                ];
                $stockDropdownItems[] = [
                    'id' => 'pt',
                    'label' => 'Перемещения',
                    'link'  => $url('pt')
                ];
                $stockDropdownItems[] = [
                    'id' => 'transaction',
                    'label' => 'Журнал операций',
                    'link' => $url('admin', ['action'=>'transactions'])
                ];
            }    
            if (count($stockDropdownItems)!=0) {
                $items[] = [
                    'id' => 'stock',
                    'label' => 'Склад',
                    'dropdown' => $stockDropdownItems
                ];
            }

            //Справочники
            $rbDropdownItems = [];
            if ($this->rbacManager->isGranted(null, 'rb.manage')) {
                
                $rbDropdownItems[] = [
                    'id' => 'goods',
                    'label' => 'Товары',
                    'link'  => $url('goods')
                ];
            
                $rbDropdownItems[] = [
                            'id' => 'producer',
                            'label' => 'Производители',
                            'link' => $url('producer', [])
                        ];
                $rbDropdownItems[] = [
                            'id' => 'unknown_producer',
                            'label' => 'Неизвестные производители',
                            'link' => $url('producer', ['action'=>'unknown'])
                        ];
                
                $rbDropdownItems[] = [
                            'id' => 'article',
                            'label' => 'Артикулы производителей',
                            'link' => $url('producer', ['action'=>'article'])
                        ];
                
                $rbDropdownItems[] = [
                            'id' => 'oe',
                            'label' => 'Оригинальные номера',
                            'link' => $url('oem', ['action'=>'oem'])
                        ];
                
                $rbDropdownItems[] = [
                            'id' => 'group',
                            'label' => 'Группы ТД',
                            'link' => $url('group')
                        ];
                
                $rbDropdownItems[] = [
                            'id' => 'oem',
                            'label' => 'Кроссы',
                            'link' => $url('oem', ['action'=>'index'])
                        ];
                
                $rbDropdownItems[] = [
                            'id' => 'token',
                            'label' => 'Токены',
                            'link' => $url('name', ['action'=>'index-token'])
                        ];
                
                $rbDropdownItems[] = [
                            'id' => 'bigram',
                            'label' => 'Биграмы',
                            'link' => $url('name', ['action'=>'index-bigram'])
                        ];
                
                $rbDropdownItems[] = [
                            'id' => 'tokenGroup',
                            'label' => 'Группы наименований',
                            'link' => $url('name', ['action'=>'token-group'])
                        ];
                
                $rbDropdownItems[] = [
                            'id' => 'make',
                            'label' => 'Машины',
                            'link' => $url('make')
                        ];
                
                $rbDropdownItems[] = [
                            'id' => 'currency',
                            'label' => 'Валюты',
                            'link' => $url('currency')
                        ];
                
                $rbDropdownItems[] = [
                            'id' => 'country',
                            'label' => 'Страны',
                            'link' => $url('rb', ['action'=>'country'])
                        ];
                
                $rbDropdownItems[] = [
                            'id' => 'cost',
                            'label' => 'Статьи затрат',
                            'link' => $url('cost')
                        ];
            }
            
            if (count($rbDropdownItems)!=0) {
                $items[] = [
                    'id' => 'rb',
                    'label' => 'Справочники',
                    'dropdown' => $rbDropdownItems
                ];
            }
            
            //Предприятие
            if ($this->rbacManager->isGranted(null, 'company.manage')) {
                $companyDropdownItems = [];
    
                $companyDropdownItems[] = [
                            'id' => 'offices',
                            'label' => 'Офисы',
                            'link' => $url('offices')
                        ];
                
                $companyDropdownItems[] = [
                            'id' => 'bank',
                            'label' => 'Банк',
                            'link' => $url('bank', ['action' => 'statement'])
                        ];
                
                $companyDropdownItems[] = [
                            'id' => 'payment',
                            'label' => 'Платежи',
                            'link' => $url('payment', ['action' => 'index'])
                        ];
                
                $companyDropdownItems[] = [
                            'id' => 'cash',
                            'label' => 'Касса',
                            'link' => $url('till')
                        ];
                
                $companyDropdownItems[] = [
                            'id' => 'cash',
                            'label' => 'Подотчет',
                            'link' => $url('accountant')
                        ];

                $companyDropdownItems[] = [
                            'id' => 'regions',
                            'label' => 'Регионы',
                            'link' => $url('regions')
                        ];

                $companyDropdownItems[] = [
                            'id' => 'tax',
                            'label' => 'Налоги',
                            'link' => $url('tax')
                        ];
                $companyDropdownItems[] = [
                            'id' => 'edoOperators',
                            'label' => 'Операторы ЭДО',
                            'link' => $url('legals', ['action' => 'edo-operators'])
                        ];
                
                
                
                if (count($companyDropdownItems)!=0) {
                    $items[] = [
                        'id' => 'company',
                        'label' => 'Предприятие',
                        'dropdown' => $companyDropdownItems
                    ];
                }
            }
            
            //Поиск
//            if ($this->rbacManager->isGranted(null, 'rb.manage')) {
//                $searchDropdownItems = [];
//    
//                $searchDropdownItems[] = [
//                            'id' => 'search',
//                            'label' => 'Поиск тест',
//                            'link' => $url('search')
//                        ];
//                                                                
//                if (count($searchDropdownItems)!=0) {
//                    $items[] = [
//                        'id' => 'search',
//                        'label' => 'Поиск',
//                        'dropdown' => $searchDropdownItems
//                    ];
//                }
//            }

            //Отчеты
            if ($this->rbacManager->isGranted(null, 'client.manage')) {
                $reportDropdownItems = [];
    
//                $reportDropdownItems[] = [
//                            'id' => 'report',
//                            'label' => 'Отчеты',
//                            'link' => $url('report')
//                        ];
                                
//                $reportDropdownItems[] = [
//                            'id' => 'report',
//                            'label' => 'Подотчет',
//                            'link' => $url('report', ['action' => 'accountant'])
//                        ];
                
                $reportDropdownItems[] = [
                            'id' => 'fin',
                            'label' => 'ОПиУ',
                            'link' => $url('opu')
                        ];
                                
                if (count($reportDropdownItems)!=0) {
                    $items[] = [
                        'id' => 'report',
                        'label' => 'Отчеты',
                        'dropdown' => $reportDropdownItems
                    ];
                }
            }
            
            //Zp
            if ($this->rbacManager->isGranted(null, 'zp.manage')) {
                $zpDropdownItems = [];
    
                $zpDropdownItems[] = [
                            'id' => 'zp',
                            'label' => 'Расчетный листок',
                            'link' => $url('payslip')
                        ];
                                
                $zpDropdownItems[] = [
                            'id' => 'zp',
                            'label' => 'Операции ЗП',
                            'link' => $url('zp')
                        ];
                                
                $zpDropdownItems[] = [
                            'id' => 'zp',
                            'label' => 'Виды начислений',
                            'link' => $url('accrual')
                        ];
                                
                $zpDropdownItems[] = [
                            'id' => 'zp',
                            'label' => 'Штатное расписание',
                            'link' => $url('position')
                        ];
                $zpDropdownItems[] = [
                            'id' => 'zp',
                            'label' => 'Плановые начисления',
                            'link' => $url('personal')
                        ];
                                
                if (count($zpDropdownItems)!=0) {
                    $items[] = [
                        'id' => 'zp',
                        'label' => 'Зарплата',
                        'dropdown' => $zpDropdownItems
                    ];
                }
            }

            // Determine which items must be displayed in Admin dropdown.
            $useradminDropdownItems = [];
            
            if ($this->rbacManager->isGranted(null, 'user.manage')) {
                $useradminDropdownItems[] = [
                            'id' => 'users',
                            'label' => 'Сотрудники',
                            'link' => $url('users')
                        ];
            }
            
            if ($this->rbacManager->isGranted(null, 'permission.manage')) {
                $useradminDropdownItems[] = [
                            'id' => 'permissions',
                            'label' => 'Права',
                            'link' => $url('permissions')
                        ];
            }
            
            if ($this->rbacManager->isGranted(null, 'role.manage')) {
                $useradminDropdownItems[] = [
                            'id' => 'roles',
                            'label' => 'Роли',
                            'link' => $url('roles')
                        ];
            }
            
            if (count($useradminDropdownItems)!=0) {
                $items[] = [
                    'id' => 'users',
                    'label' => 'Сотрудники',
                    'dropdown' => $useradminDropdownItems
                ];
            }
            
            if ($this->rbacManager->isGranted(null, 'member.manage')) {
                $items[] = [
                    'id' => 'users',
                    'label' => 'Пользователи',
                    'link'  => $url('members')
                ];
            }
            
            if ($this->rbacManager->isGranted(null, 'admin.manage')) {
                
                $settingsDropDownItems[] = [
                    'id' => 'settings',
                    'label' => 'Общие',
                    'link'  => $url('settings')                    
                ];
                
                $settingsDropDownItems[] = [
                    'id' => 'raw',
                    'label' => 'Настройи загрузки прайсов',
                    'link'  => $url('admin', ['action' => 'price-settings'])                    
                ];
                
                $settingsDropDownItems[] = [
                    'id' => 'bank',
                    'label' => 'Настройи обмена с банком',
                    'link'  => $url('admin', ['action' => 'bank-settings'])                    
                ];

                $settingsDropDownItems[] = [
                    'id' => 'telegram',
                    'label' => 'Настройи telegram',
                    'link'  => $url('admin', ['action' => 'telegram-settings'])                    
                ];

                $settingsDropDownItems[] = [
                    'id' => 'aplExchange',
                    'label' => 'Настройи обмена с АПЛ',
                    'link'  => $url('admin', ['action' => 'apl-exchange-settings'])                    
                ];

                $settingsDropDownItems[] = [
                    'id' => 'zetasoft',
                    'label' => 'Настройи ZetaSoft',
                    'link'  => $url('admin', ['action' => 'zetasoft-settings'])                    
                ];
                
                $settingsDropDownItems[] = [
                    'id' => 'partsapi',
                    'label' => 'Настройи PartsApi',
                    'link'  => $url('admin', ['action' => 'parts-api-settings'])                    
                ];

                $settingsDropDownItems[] = [
                    'id' => 'tdExchange',
                    'label' => 'Настройи обмена по апи текдока',
                    'link'  => $url('admin', ['action' => 'td-exchange-settings'])                    
                ];

                $settingsDropDownItems[] = [
                    'id' => 'apiMarketPlaces',
                    'label' => 'Настройи АПИ ТП',
                    'link'  => $url('admin', ['action' => 'api-market-places'])                    
                ];

                $settingsDropDownItems[] = [
                    'id' => 'sbpSettings',
                    'label' => 'Настройи оплат по СБП',
                    'link'  => $url('admin', ['action' => 'sbp-settings'])                    
                ];

                $settingsDropDownItems[] = [
                    'id' => 'aiSettings',
                    'label' => 'Настройи подключения ИИ',
                    'link'  => $url('admin', ['action' => 'ai-settings'])                    
                ];

                if (count($settingsDropDownItems)!=0) {
                    $items[] = [
                        'id' => 'settings',
                        'label' => 'Настройки',
                        'dropdown' => $settingsDropDownItems
                    ];
                }            

                $adminDropdownItems[] = [
                    'id' => 'post',
                    'label' => 'Проверка почты',
                    'link'  => $url('post')
                ];
                
                $adminDropdownItems[] = [
                    'id' => 'apl',
                    'label' => 'Обмен c apl',
                    'link'  => $url('apl')
                ];
                
                $adminDropdownItems[] = [
                    'id' => 'apl',
                    'label' => 'Прайс-листы для ТП',
                    'link'  => $url('market')
                ];
                
                $adminDropdownItems[] = [
                    'id' => 'phpinfo',
                    'label' => 'phpinfo()',
                    'link'  => $url('admin', ['action' => 'phpinfo'])
                ];
                
                $adminDropdownItems[] = [
                    'id' => 'mem',
                    'label' => 'memcache(d)',
                    'link'  => $url('admin', ['action' => 'mem'])
                ];
                
                $adminDropdownItems[] = [
                    'id' => 'external',
                    'label' => 'Внешние базы',
                    'link'  => $url('ext')
                ];
                
                $adminDropdownItems[] = [
                    'id' => 'setting',
                    'label' => 'Процессы',
                    'link'  => $url('log', ['action' => 'setting'])
                ];
                
                $adminDropdownItems[] = [
                    'id' => 'ml',
                    'label' => 'Машинное обучение',
                    'link'  => $url('ml')
                ];

                if (file_exists('./config/development.config.php')) {
                    $adminDropdownItems[] = [
                        'id' => 'api',
                        'label' => 'Api',
                        'link'  => $url('api')
                    ];
                }    
                
                if (count($adminDropdownItems)!=0) {
                    $items[] = [
                        'id' => 'admin',
                        'label' => 'Адмнистрирование',
                        'dropdown' => $adminDropdownItems
                    ];
                }            
            }
            
            
            $items[] = [
                'id' => 'personalSettings',
//                'labelHTML' => '<span class="glyphicon glyphicon-user"></span><strong>'.$this->rbacManager->navUserName().'</strong>',
                'labelHTML' => '<strong>'.$this->rbacManager->navUserName().'</strong>',
                'float' => 'right',
                'dropdown' => [
                    [
                        'id' => 'personalSettings',
                        'label' => 'Расчетный лист (новый)',
                        'link' => $url('payslip')
                    ],
                    [
                        'id' => 'personalSettings',
                        'label' => 'Расчетный листок',
                        'link' => $url('users', ['action'=>'dd-report'])
                    ],
                    [
                        'id' => 'personalSettings',
                        'label' => 'Настройки',
                        'link' => $url('application', ['action'=>'settings'])
                    ],
                    [
                        'id' => 'personalSettings',
                        'label' => 'Выход',
                        'link' => $url('logout')
                    ],
                ]
            ];
        }
        
        return $items;
    }
}


