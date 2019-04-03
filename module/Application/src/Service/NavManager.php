<?php
namespace Application\Service;

/**
 * This service is responsible for determining which items should be in the main menu.
 * The items may be different depending on whether the user is authenticated or not.
 */
class NavManager
{
    /**
     * Auth service.
     * @var Zend\Authentication\Authentication
     */
    private $authService;
    
    /**
     * Url view helper.
     * @var Zend\View\Helper\Url
     */
    private $urlHelper;
    
    /**
     * RBAC manager.
     * @var User\Service\RbacManager
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
        
        
        $items[] = [
            'id' => 'home',
            'label' => 'adminapl.ru',
            'labelHTML' => '<strong>АдминАПЛ</strong><sup>.ru</sup>',
            'link'  => $url('home')
        ];
            
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
            
            if ($this->rbacManager->isGranted(null, 'client.any.manage') || $this->rbacManager->isGranted(null, 'client.own.manage')) {
                $items[] = [
                    'id' => 'shop',
                    'label' => 'Каталог',
                    'link'  => $url('shop')
                ];

                $items[] = [
                    'id' => 'order',
                    'label' => 'Заказы',
                    'link'  => $url('order')
                ];
            
                $items[] = [
                    'id' => 'client',
                    'label' => 'Покупатели',
                    'link'  => $url('client')
                ];
            }
            
            if ($this->rbacManager->isGranted(null, 'supplier.manage')) {
                $items[] = [
                    'id' => 'supplier',
                    'label' => 'Поставщики',
                    'link'  => $url('supplier')
                ];
            }
            
            if ($this->rbacManager->isGranted(null, 'supplier.manage')) {
                $priceDropdownItems = [];

                $priceDropdownItems[] = [
                    'id' => 'raw_queue',
                    'label' => 'Очередь',
                    'link'  => $url('price', ['action' => 'queue'])
                ];
                
                $priceDropdownItems[] = [
                    'id' => 'raw_uploaded',
                    'label' => 'Загруженные',
                    'link'  => $url('raw')
                ];
                
                $priceDropdownItems[] = [
                    'id' => 'raw_settings',
                    'label' => 'Настройки',
                    'link'  => $url('admin', ['action' => 'price-settings'])
                ];
                
                if (count($priceDropdownItems)!=0) {
                    $items[] = [
                        'id' => 'raw',
                        'label' => 'Прайсы',
                        'dropdown' => $priceDropdownItems
                    ];
                }                
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
                            'label' => 'Группы',
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
                            'id' => 'tax',
                            'label' => 'Налоги',
                            'link' => $url('rb', ['action'=>'tax'])
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
                            'id' => 'regions',
                            'label' => 'Регионы',
                            'link' => $url('regions')
                        ];
                
                $companyDropdownItems[] = [
                            'id' => 'bank',
                            'label' => 'Банк',
                            'link' => $url('bank', ['action' => 'statement'])
                        ];
                
                if (count($companyDropdownItems)!=0) {
                    $items[] = [
                        'id' => 'users',
                        'label' => 'Предприятие',
                        'dropdown' => $companyDropdownItems
                    ];
                }
            }
            
            // Determine which items must be displayed in Admin dropdown.
            $useradminDropdownItems = [];
            
            if ($this->rbacManager->isGranted(null, 'user.manage')) {
                $useradminDropdownItems[] = [
                            'id' => 'users',
                            'label' => 'Пользователи',
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
                    'label' => 'Пользователи',
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
                    'id' => 'tdExchange',
                    'label' => 'Настройи обмена по апи текдока',
                    'link'  => $url('admin', ['action' => 'td-exchange-settings'])                    
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
                    'id' => 'ml',
                    'label' => 'Машинное обучение',
                    'link'  => $url('ml')
                ];
                
                if (count($adminDropdownItems)!=0) {
                    $items[] = [
                        'id' => 'admin',
                        'label' => 'Адмнистрирование',
                        'dropdown' => $adminDropdownItems
                    ];
                }            
            }
            
            
            $items[] = [
                'id' => 'logout',
//                'labelHTML' => '<span class="glyphicon glyphicon-user"></span><strong>'.$this->rbacManager->navUserName().'</strong>',
                'labelHTML' => '<strong>'.$this->rbacManager->navUserName().'</strong>',
                'float' => 'right',
                'dropdown' => [
                    [
                        'id' => 'settings',
                        'label' => 'Настройки',
                        'link' => $url('application', ['action'=>'settings'])
                    ],
                    [
                        'id' => 'logout',
                        'label' => 'Выход',
                        'link' => $url('logout')
                    ],
                ]
            ];
        }
        
        return $items;
    }
}


