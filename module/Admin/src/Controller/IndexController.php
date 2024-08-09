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
use Admin\Form\ApiMarketPlaces;
use Admin\Form\SmsForm;
use Application\Entity\Order;
use User\Filter\PhoneFilter;
use Stock\Entity\Register;
use Application\Entity\Producer;
use Application\Entity\UnknownProducer;
use Application\Entity\Goods;
use Admin\Form\ProducerUnionForm;
use Hackzilla\PasswordGenerator\Generator\ComputerPasswordGenerator;
use Admin\Form\SbpSettings;
use Company\Entity\BankAccount;
use Admin\Form\AiSettings;
use Admin\Form\IaSettings;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;

class IndexController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
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
    
    /**
     * RegisterManager manager.
     * @var \Stock\Service\RegisterManager
     */
    private $registerManager;    
    
    /**
     * Job manager.
     * @var \Admin\Service\JobManager
     */
    private $jobManager;    
    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $telegramManager, $adminManager, 
            $smsManager, $tamtamManager, $annManager, $autoruManager, $registerManager, 
            $jobManager) 
    {
        $this->entityManager = $entityManager;
        $this->telegramManager = $telegramManager;        
        $this->adminManager = $adminManager;        
        $this->smsManager = $smsManager;        
        $this->tamtamManager = $tamtamManager;        
        $this->annManager = $annManager;        
        $this->autoruManager = $autoruManager;
        $this->registerManager = $registerManager;
        $this->jobManager = $jobManager;
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

    /**
     * Управление настройками апи тп
     * 
     * @return ViewModel
     */
    public function apiMarketPlacesAction()
    {
        $form = new ApiMarketPlaces();
    
        $settings = $this->adminManager->getApiMarketPlaces();
        
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
                $this->adminManager->setApiMarketPlaces($data);
                
                $this->flashMessenger()->addSuccessMessage(
                        'Настройки сохранены.');

                $this->redirect()->toRoute('admin', ['action' => 'api-market-places']);
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
     * Управление настройками оплат по СБП
     * 
     * @return ViewModel
     */
    public function sbpSettingsAction()
    {
        $form = new SbpSettings();
    
        $settings = $this->adminManager->getSbpSettings();
        
        $accounts = [0 => ''];
        $bankAcounts = $this->entityManager->getRepository(BankAccount::class)
                    ->findBy(['status' => BankAccount::STATUS_ACTIVE, 'statement' => BankAccount::STATEMENT_ACTIVE]);
        foreach ($bankAcounts as $bankAccount) {
            $accounts[$bankAccount->getId()] = $bankAccount->getNameWithShortRs();
        }
        $form->get('account')->setValueOptions($accounts);

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
                $this->adminManager->setSbpSettings($data);
                
                $this->flashMessenger()->addSuccessMessage(
                        'Настройки сохранены.');

                $this->redirect()->toRoute('admin', ['action' => 'sbp-settings']);
            } else {
                $this->flashMessenger()->addInfoMessage(
                        'Настройки не сохранены.');                
            }
        } else {
            if ($settings){
                $form->setData($settings);
            }                
        }
        
        return new ViewModel([
            'form' => $form,
        ]);  
        
    }
    
    /**
     * Управление настройками ai
     * 
     * @return ViewModel
     */
    public function aiSettingsAction()
    {
        $form = new AiSettings();
    
        $settings = $this->adminManager->getAiSettings();
        
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
                $this->adminManager->setAiSettings($data);
                
                $this->flashMessenger()->addSuccessMessage(
                        'Настройки сохранены.');

                $this->redirect()->toRoute('admin', ['action' => 'ai-settings']);
            } else {
                $this->flashMessenger()->addInfoMessage(
                        'Настройки не сохранены.');                
            }
        } else {
            if ($settings){
                $form->setData($settings);
            }                
        }
        
        return new ViewModel([
            'form' => $form,
        ]);  
        
    }
    
    /**
     * Управление настройками ia
     * 
     * @return ViewModel
     */
    public function iaSettingsAction()
    {
        $form = new IaSettings();
    
        $settings = $this->adminManager->getInternetAcquiringSettings();
        
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
                $this->adminManager->setInternetAcquiringSettings($data);
                
                $this->flashMessenger()->addSuccessMessage(
                        'Настройки сохранены.');

                $this->redirect()->toRoute('admin', ['action' => 'ia-settings']);
            } else {
                $this->flashMessenger()->addInfoMessage(
                        'Настройки не сохранены.');                
            }
        } else {
            if ($settings){
                $form->setData($settings);
            }                
        }
        
        return new ViewModel([
            'form' => $form,
        ]);  
        
    }
    
    public function smsFormAction()
    {        
        $orderId = (int)$this->params()->fromRoute('id', -1);
        $phone = $this->params()->fromQuery('phone');
        
        $order = null;
        if ($orderId > 0){
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);                    
        }    
        
        $settings = $this->adminManager->getSettings();
        $turbo_passphrase = $settings['turbo_passphrase'];

        $form = new SmsForm();
        $form->get('phone')->setValue($phone);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {

                return new JsonModel(
                   ['ok']
                );           
            }
        }    
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'order' => $order,
            'currentUser' => $this->smsManager->currentUser(),
        ]);                        
    }
    
    public function orderPrepayAction()
    {
        $orderId = (int)$this->params()->fromRoute('id', -1);
        $prepay = $this->params()->fromQuery('prepay', 0);
        
        $result = [];
        if ($orderId > 0){
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);
            $result['prepayLink'] = $order->getAplPaymentLinkClick($prepay);
        }
        
        return new JsonModel($result);                   
    }
    
    public function smsAction()
    {
        if ($this->getRequest()->isPost()) {
            $result = 'Не ушло! Проверте данные';
            $data = $this->params()->fromPost();
            if (!empty($data['phone']) && !empty($data['message']) && !empty($data['mode'])){
                $filter = new PhoneFilter(['filter' => PhoneFilter::PHONE_FORMAT_DB]);
                $phone = '7'.$filter->filter($data['phone']);
                if ($data['mode'] == 1){
                    $result = $this->smsManager->send(['phone' => $phone, 'text' => $data['message']]);
                }    
                if ($data['mode'] == 2){
                    $result = $this->smsManager->wamm(['phone' => $phone, 'text' => $data['message'], 'name' => $data['orderId'], 'attachment' => $data['attachment']]);
                }    
            }    

            return new JsonModel([
                'result' => $result
            ]);        
        }    
        exit;    
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
    
    public function wammToCommentsAction() 
    {
        $this->smsManager->wammchatToOrderComments();
        return new JsonModel([
            'ok'
        ]);        
    }

    public function testGetAndUpdateWammAction()
    {
        $this->smsManager->getAndUpdateWammchat(10);
        
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
            $this->telegramManager->addPostponeMessage([
                'chat_id' => $settings['telegram_admin_chat_id'], 
                'text' => 'Привет! Это отложенное сообщение!',
                ]);       
        }    
        
        return new JsonModel([
            'ok',
        ]);
    }
    
    public function docRegisterAction()
    {
        $this->entityManager->getRepository(Register::class)
                ->allRegister();
        
        return new JsonModel([
            'result' => 'ok',
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

    public function varactAction()
    {       
        $this->registerManager->actualize(100);

        return new JsonModel(
            ['ok']
        );        
    }
    
    /**
     * Выполнение заданий
     * @return JsonModel
     */
    public function jobrunAction()
    {

        $this->jobManager->run();            
                
        return new JsonModel(
            ['ok']
        );        
    }        
    
    public function syslogAction()
    {
        return new JsonModel(
            sys_getloadavg()
        );        
        
    }
    
    public function passwordGeneratorAction()
    {
        $generator = new ComputerPasswordGenerator();

        $generator
          ->setUppercase()
          ->setLowercase()
          ->setNumbers(true)
          ->setSymbols(false)
          ->setLength(8);

        $password = $generator->generatePassword();
        
        return new JsonModel([
            'password' => $password,
        ]);                
    }
    
    public function producerUnionFormAction()
    {
        $producerId = (int)$this->params()->fromRoute('id', -1);
        $goodId = (int)$this->params()->fromQuery('good', -1);
        
        $producer = $good = null;
        if ($producerId > 0){
            $producer = $this->entityManager->getRepository(Producer::class)
                    ->find($producerId);
        }    
        if ($goodId > 0){
            $good = $this->entityManager->getRepository(Goods::class)
                    ->find($goodId);
        }    
        
        $form = new ProducerUnionForm($this->entityManager);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                $newUnknownProducer = $this->entityManager->getRepository(UnknownProducer::class)
                        ->findOneBy(['name' => $data['newProducer']]);
                if ($producer && $newUnknownProducer){
                    if ($newUnknownProducer->getProducer()){
                        if ($good){
                            $this->registerManager->changeProducer($good, $newUnknownProducer->getProducer());
                        } else {
                            $this->registerManager->uniteProducer($newUnknownProducer->getProducer(), $producer);
                        }    
                    }    
                }
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($producer){
                $data = [
                    'producer' => $producer->getName(),
                ];
                $form->setData($data);
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'producer' => $producer,
            'good' => $good,
        ]);        
    }
    
    public function transactionsAction()
    {
        return new ViewModel([
            'allowDate' => $this->registerManager->getAllowDate(),
        ]);        
    }
    
    public function transactionsContentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort', 'docDate');
        $order = $this->params()->fromQuery('order', 'DESC');
        $year_month = $this->params()->fromQuery('month');
        $statusDoc = $this->params()->fromQuery('statusDoc');
        $status = $this->params()->fromQuery('status');
        $doc = $this->params()->fromQuery('doc');
        
        $year = $month = null;
        if ($year_month){
            $year = date('Y', strtotime($year_month));
            $month = date('m', strtotime($year_month));
        }        
        $params = [
            'q' => trim($q), 'sort' => $sort, 'order' => $order,             
            'year' => $year, 'month' => $month, 'statusDoc' => $statusDoc,
            'status' => $status, 'doc' => $doc,
        ];
        
        $query = $this->entityManager->getRepository(Register::class)
                        ->transactions($params);
        
        $total = $this->entityManager->getRepository(Register::class)
                        ->transactionsTotal($params);
        
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);          
    }         
    
    public function repostDocAction()
    {
        $registerId = $this->params()->fromRoute('id', -1);
        
        $register = $this->entityManager->getRepository(Register::class)
                ->find($registerId);
        
        if ($register == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->registerManager->repostDoc($register);
        $query = $this->entityManager->getRepository(Register::class)
                ->transactions(['registerId' => $register->getId()]);
        $result = $query->getOneOrNullResult(2);

        return new JsonModel(
           $result
        );           
    }            
}
