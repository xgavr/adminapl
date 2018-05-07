<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Admin\Form\SettingsForm;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class IndexController extends AbstractActionController
{
    
    /**
     * TelegrammManager manager.
     * @var Admin\Service\TelegrammManager
     */
    private $telegrammManager;    
    
    /**
     * AdminManager manager.
     * @var Admin\Service\AdminManager
     */
    private $adminManager;    
    
    /**
     * SmsManager manager.
     * @var Admin\Service\SmsManager
     */
    private $smsManager;    

    /**
     * SmsManager manager.
     * @var Admin\Service\TamTamManager
     */
    private $tamtamManager;    

    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($telegrammManager, $adminManager, $smsManager, $tamtamManager) 
    {
        $this->telegrammManager = $telegrammManager;        
        $this->adminManager = $adminManager;        
        $this->smsManager = $smsManager;        
        $this->tamtamManager = $tamtamManager;        
    }   
    
    public function indexAction()
    {
        return [];
    }
        
    public function phpinfoAction()
    {
        return [];
    }
    
    public function memAction()
    {
        
        if (extension_loaded('memcached')){

            $title = 'Memcached';

            $cache  = new \Zend\Cache\Storage\Adapter\Memcached();
            $cache->getOptions()
                    ->setTtl(3600)
                    ->setServers(
                        array(
                            array('localhost', 11211)
                        )
                    );

            $plugin = new \Zend\Cache\Storage\Plugin\ExceptionHandler();
            $plugin->getOptions()->setThrowExceptions(false);
            $cache->addPlugin($plugin);

        } elseif (extension_loaded('memcache')){
                
            $title = 'Memcache';

            $cache  = new \Zend\Cache\Storage\Adapter\Memcache();
            $cache->getOptions()
                    ->setTtl(3600)
                    ->setServers(
                        array(
                            array('localhost', 11211)
                        )
                    );

            $plugin = new \Zend\Cache\Storage\Plugin\ExceptionHandler();
            $plugin->getOptions()->setThrowExceptions(false);
            $cache->addPlugin($plugin);
        }	

        return new ViewModel([
            'title' => $title,
            'mem' => $cache,
        ]);
    }
    
    public function settingsAction()
    {
        $form = new SettingsForm($this->entityManager);
    
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
    
    public function testSmsAction()
    {
        $this->smsManager->send(['phone' => '89096319425', 'text' => 'тест']);
        
        return new JsonModel([
            'ok'
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
}
