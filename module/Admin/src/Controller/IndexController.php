<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Admin\Form\SettingsForm;
use Admin\Form\PriceSettingsForm;
use Admin\Form\BankSettingsForm;
use Admin\Form\AplExchangeForm;
use Admin\Form\TdExchangeForm;
use Admin\Form\TelegramSettingsForm;
use Admin\Form\AbcpSettings;
use Admin\Form\PartsApiSettings;
use Admin\Form\ZetasoftSettings;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;

class IndexController extends AbstractActionController
{
    
    /**
     * TelegrammManager manager.
     * @var \Admin\Service\TelegrammManager
     */
    private $telegramManager;    
    
    /**
     * AdminManager manager.
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;    
    
    /**
     * SmsManager manager.
     * @var \Admin\Service\SmsManager
     */
    private $smsManager;    

    /**
     * SmsManager manager.
     * @var \Admin\Service\TamTamManager
     */
    private $tamtamManager;    

    /**
     * AnnManager manager.
     * @var \Admin\Service\TamTamManager
     */
    private $annManager;    
    
    /**
     * AutoruManager manager.
     * @var \Admin\Service\AutoruManager
     */
    private $autoruManager;    
    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($telegramManager, $adminManager, $smsManager, $tamtamManager, $annManager, $autoruManager) 
    {
        $this->telegramManager = $telegramManager;        
        $this->adminManager = $adminManager;        
        $this->smsManager = $smsManager;        
        $this->tamtamManager = $tamtamManager;        
        $this->annManager = $annManager;        
        $this->autoruManager = $autoruManager;
    }   
    
    public function indexAction()
    {
        return new ViewModel([
        ]);
    }
        
    public function phpinfoAction()
    {
        return [];
    }
    
    public function memAction()
    {
        
        if (extension_loaded('memcached')){

            $title = 'Memcached';

            $cache  = new \Laminas\Cache\Storage\Adapter\Memcached();
            $cache->getOptions()
                    ->setTtl(3600)
                    ->setServers(
                        array(
                            array('localhost', 11211)
                        )
                    );

            $plugin = new \Laminas\Cache\Storage\Plugin\ExceptionHandler();
            $plugin->getOptions()->setThrowExceptions(false);
            $cache->addPlugin($plugin);

        } elseif (extension_loaded('memcache')){
                
            $title = 'Memcache';

            $cache  = new \Laminas\Cache\Storage\Adapter\Memcache();
            $cache->getOptions()
                    ->setTtl(3600)
                    ->setServers(
                        array(
                            array('localhost', 11211)
                        )
                    );

            $plugin = new \Laminas\Cache\Storage\Plugin\ExceptionHandler();
            $plugin->getOptions()->setThrowExceptions(false);
            $cache->addPlugin($plugin);
        }	

        return new ViewModel([
            'title' => $title,
            'mem' => $cache,
        ]);
    }
    
    /*
     * Управление общими настройками
     */
    public function settingsAction()
    {
        $form = new SettingsForm();
    
        $settings = $this->adminManager->getSettings();
        
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                // Используем менеджер постов, чтобы добавить новый пост в базу данных.                
                $this->adminManager->setSettings($data);
                
                $this->flashMessenger()->addSuccessMessage(
                        'Настройки сохранены.');
                // Перенаправляем пользователя на страницу "goods".
                return $this->redirect()->toRoute('settings');
            } else {
                $this->flashMessenger()->addInfoMessage(
                        'Настройки не сохранены.');                
            }
        } else {
            if ($settings){
                $form->setData($settings);
            }    
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
        ]);  
        
    }
    
    public function priceSettingsAction()
    {
        $form = new PriceSettingsForm();
    
        $settings = $this->adminManager->getPriceSettings();
        
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                //                 
                $this->adminManager->setPriceSettings($data);
                
                $this->flashMessenger()->addSuccessMessage(
                        'Настройки сохранены.');
                // Перенаправляем пользователя на страницу "goods".
                return $this->redirect()->toRoute('admin', ['action' => 'price-settings']);
            } else {
                $this->flashMessenger()->addInfoMessage(
                        'Настройки не сохранены.');                
            }
        } else {
            if ($settings){
                $form->setData($settings);
            }    
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
        ]);  
        
    }

    /**
     * Управление настройками загрузки прайсов
     */    
    public function priceSettingsFormAction()
    {
        $form = new PriceSettingsForm();
    
        $settings = $this->adminManager->getPriceSettings();
        
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                // Используем менеджер постов, чтобы добавить новый пост в базу данных.                
                $this->adminManager->setPriceSettings($data);                
            }
        } else {
            if ($settings){
                $form->setData($settings);
            }    
        }
        
        $this->layout()->setTemplate('layout/terminal');
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
        ]);  
        
    }
    
    public function bankSettingsAction()
    {
        $form = new BankSettingsForm();
    
        $settings = $this->adminManager->getBankTransferSettings();
        
        if ($settings){
            $form->setData($settings);
        }    
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                //                 
                $this->adminManager->setBankTransferSettings($data);
                
                $this->flashMessenger()->addSuccessMessage(
                        'Настройки сохранены.');
                // Перенаправляем пользователя на страницу "goods".
                return $this->redirect()->toRoute('admin', ['action' => 'bank-settings']);
            } else {
                $this->flashMessenger()->addInfoMessage(
                        'Настройки не сохранены.');                
            }
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
        ]);  
        
    }

    /**
     * Управление настройками обмена с банком
     */    
    public function bankSettingsFormAction()
    {
        $form = new BankSettingsForm();
    
        $settings = $this->adminManager->getBankTransferSettings();
        
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                // Используем менеджер постов, чтобы добавить новый пост в базу данных.                
                $this->adminManager->setBankTransferSettings($data);                
            }
        } else {
            if ($settings){
                $form->setData($settings);
            }    
        }
        
        $this->layout()->setTemplate('layout/terminal');
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
        ]);  
        
    }

    

    /**
     * Управление настройками обмена с АПЛ
     * 
     * @return ViewModel
     */
    public function aplExchangeSettingsAction()
    {
        $form = new AplExchangeForm();
    
        $settings = $this->adminManager->getAplExchangeSettings();
        
        if ($settings){
            $form->setData($settings);
        }    
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                //                 
                $this->adminManager->setAplExchangeSettings($data);
                
                $this->flashMessenger()->addSuccessMessage(
                        'Настройки сохранены.');
                // Перенаправляем пользователя на страницу "goods".
                return $this->redirect()->toRoute('admin', ['action' => 'apl-exchange-settings']);
            } else {
                $this->flashMessenger()->addInfoMessage(
                        'Настройки не сохранены.');                
            }
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
        ]);  
        
    }

    /**
     * Управление настройками обмена с АПЛ
     */    
    public function aplExchangeSettingsFormAction()
    {
        $form = new AplExchangeForm();
    
        $settings = $this->adminManager->getAplExchangeSettings();
        
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                // Используем менеджер постов, чтобы добавить новый пост в базу данных.                
                $this->adminManager->setAplExchangeSettings($data);                
            }
        } else {
            if ($settings){
                $form->setData($settings);
            }    
        }
        
        $this->layout()->setTemplate('layout/terminal');
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
        ]);  
        
    }

    /**
     * Управление настройками обмена по апи текдока
     * 
     * @return ViewModel
     */
    public function tdExchangeSettingsAction()
    {
        $form = new TdExchangeForm();
    
        $settings = $this->adminManager->getTdExchangeSettings();
        
        if ($settings){
            $form->setData($settings);
        }    
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                //                 
                $this->adminManager->setTdExchangeSettings($data);
                
                $this->flashMessenger()->addSuccessMessage(
                        'Настройки сохранены.');
                // Перенаправляем пользователя на страницу "goods".
                return $this->redirect()->toRoute('admin', ['action' => 'td-exchange-settings']);
            } else {
                $this->flashMessenger()->addInfoMessage(
                        'Настройки не сохранены.');                
            }
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
        ]);  
        
    }

    /**
     * Управление настройками обмена по апи текдока
     */    
    public function tdExchangeSettingsFormAction()
    {
        $form = new TdExchangeForm();
    
        $settings = $this->adminManager->getTdExchangeSettings();
        
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                // Используем менеджер постов, чтобы добавить новый пост в базу данных.                
                $this->adminManager->setTdExchangeSettings($data);                
            }
        } else {
            if ($settings){
                $form->setData($settings);
            }    
        }
        
        $this->layout()->setTemplate('layout/terminal');
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
        ]);  
        
    }

    /**
     * Управление настройками telegram
     * 
     * @return ViewModel
     */
    public function telegramSettingsAction()
    {
        $form = new TelegramSettingsForm();
    
        $settings = $this->adminManager->getTelegramSettings();
        
        if ($settings){
            $form->setData($settings);
        }    
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                //                 
                $this->adminManager->setTelegramSettings($data);
                
                $this->flashMessenger()->addSuccessMessage(
                        'Настройки сохранены.');

                $this->redirect()->toRoute('admin', ['action' => 'telegram-settings']);
            } else {
                $this->flashMessenger()->addInfoMessage(
                        'Настройки не сохранены.');                
            }
        }
        
        $geoIpFilter = new \Admin\Filter\GeoIp();
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
            'geoIpData' => $geoIpFilter->filter($settings['telegram_proxy']),
        ]);  
        
    }

    /**
     * Управление настройками telegram
     */    
    public function telegramSettingsFormAction()
    {
        $form = new TelegramSettingsForm();
    
        $settings = $this->adminManager->getTelegramSettings();
        
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                // Используем менеджер постов, чтобы добавить новый пост в базу данных.                
                $this->adminManager->setTelegramSettings($data);                
            }
        } else {
            if ($settings){
                $form->setData($settings);
            }    
        }
        
        $this->layout()->setTemplate('layout/terminal');
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
        ]);  
        
    }

    /**
     * Управление настройками abcp
     * 
     * @return ViewModel
     */
    public function abcpSettingsAction()
    {
        $form = new AbcpSettings();
    
        $settings = $this->adminManager->getAbcpSettings();
        
        if ($settings){
            $form->setData($settings);
        }    
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                //                 
                $this->adminManager->setAbcpSettings($data);
                
                $this->flashMessenger()->addSuccessMessage(
                        'Настройки сохранены.');

                $this->redirect()->toRoute('admin', ['action' => 'abcp-settings']);
            } else {
                $this->flashMessenger()->addInfoMessage(
                        'Настройки не сохранены.');                
            }
        }
        
        return new ViewModel([
            'form' => $form,
        ]);  
        
    }

    /**
     * Управление настройками avtoit
     * 
     * @return ViewModel
     */
    public function avtoitSettingsAction()
    {
        $form = new AbcpSettings();
    
        $settings = $this->adminManager->getAvtoitSettings();
        
        if ($settings){
            $form->setData($settings);
        }    
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                //                 
                $this->adminManager->setAvtoitSettings($data);
                
                $this->flashMessenger()->addSuccessMessage(
                        'Настройки сохранены.');

                $this->redirect()->toRoute('admin', ['action' => 'avtoit-settings']);
            } else {
                $this->flashMessenger()->addInfoMessage(
                        'Настройки не сохранены.');                
            }
        }
        
        return new ViewModel([
            'form' => $form,
        ]);  
        
    }

    /**
     * Управление настройками zetasoft
     * 
     * @return ViewModel
     */
    public function zetasoftSettingsAction()
    {
        $form = new ZetasoftSettings();
    
        $settings = $this->adminManager->getZetasoftSettings();
        
        if ($settings){
            $form->setData($settings);
        }    
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                //                 
                $this->adminManager->setZetasoftSettings($data);
                
                $this->flashMessenger()->addSuccessMessage(
                        'Настройки сохранены.');

                $this->redirect()->toRoute('admin', ['action' => 'zetasoft-settings']);
            } else {
                $this->flashMessenger()->addInfoMessage(
                        'Настройки не сохранены.');                
            }
        }
        
        return new ViewModel([
            'form' => $form,
        ]);  
        
    }

    /**
     * Управление настройками parts-api
     * 
     * @return ViewModel
     */
    public function partsApiSettingsAction()
    {
        $form = new PartsApiSettings();
    
        $settings = $this->adminManager->getPartsApiSettings();
        
        if ($settings){
            $form->setData($settings);
        }    
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                //                 
                $this->adminManager->setPartsApiSettings($data);
                
                $this->flashMessenger()->addSuccessMessage(
                        'Настройки сохранены.');

                $this->redirect()->toRoute('admin', ['action' => 'parts-api-settings']);
            } else {
                $this->flashMessenger()->addInfoMessage(
                        'Настройки не сохранены.');                
            }
        }
        
        return new ViewModel([
            'form' => $form,
        ]);  
        
    }

    public function testSmsAction()
    {
        $this->smsManager->send(['phone' => '79096319425', 'text' => 'тест']);
        
        return new JsonModel([
            'ok'
        ]);        
    }

    public function testWammAction()
    {
        $this->smsManager->wamm(['phone' => '79096319425', 'text' => 'Мама мыла раму!']);
        
        return new JsonModel([
            'ok'
        ]);        
    }

    public function testTelegramAction()
    {
        $settings = $this->adminManager->getTelegramSettings();
        $result = 'not';
        if ($settings['telegram_admin_chat_id']){
            if ($this->telegramManager->sendMessage([
                'chat_id' => $settings['telegram_admin_chat_id'], 
                'text' => 'Привет!',
            ])){
                $result = 'ok';
            }
        }    
        return new JsonModel([
            $result,
        ]);        
    }
    
    public function testPostponeTelegramAction()
    {
        $settings = $this->adminManager->getTelegramSettings();
        if ($settings['telegram_admin_chat_id']){
            $this->telegramManager->addPostponeMesage([
                'chat_id' => $settings['telegram_admin_chat_id'], 
                'text' => 'Привет! Это отложенное сообщение!',
                ]);       
        }    
        
        return new JsonModel([
            'ok',
        ]);
    }
    
    public function checkProxyAction()
    {
        $proxy = $this->telegramManager->getProxy();
        
        return new JsonModel([
            'result' => 'ok',
            'message' => $proxy,
        ]);        
    }


    public function testTamTamAction()
    {
        $result = $this->tamtamManager->message(['chat_id' => '55672109400089', 'text' => 'Привет!']);
        var_dump($result);
        return new JsonModel([
            'ok'
        ]);        
    }
    
    public function tamTamChatsAction()
    {
        $result = $this->tamtamManager->chats();
        var_dump($result);
        return new JsonModel([
            'ok'
        ]);                
    }
    
    public function trainAnnAction()
    {
        
        $result = $this->annManager->deleteRawTrain();
        var_dump($result);
        
        return new JsonModel([
            'ok'
        ]);                
                
    }

    public function testAnnAction()
    {
        $result = $this->annManager->deleteRawTest();
        var_dump($result);
        
        return new JsonModel([
            'ok'
        ]);                
        
    }
    
}
