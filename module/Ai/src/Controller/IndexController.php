<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Ai\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;


class IndexController extends AbstractActionController
{
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
        
    /**
     * Giga manager.
     * @var \Ai\Service\GigaManager
     */
    private $gigaManager;
        
    /**
     * Constructor. Its purpose is to inject dependencies into the controller.
     */
    public function __construct($entityManager, $gigaManager) 
    {
       $this->entityManager = $entityManager;
       $this->gigaManager = $gigaManager;    }

    
    public function indexAction()
    {
        return new ViewModel([
        ]);
    }

    public function gigachatModelsAction()
    {
        $result = $this->gigaManager->models();

        return new JsonModel($result);
    }

    public function gigachatTestAction()
    {
        $messages = [];
        $messages[] = [
            'role' => 'user',
            'content' => 'Когда уже ИИ захватит этот мир?',
        ];
        
        $result = $this->gigaManager->completions($messages);

        return new JsonModel($result);
    }

    public function purposeTestAction()
    {
        $messages = [];
        $messages[] = [
            'role' => 'system',
            'content' => 'Ответь числом',
        ];
        $messages[] = [
            'role' => 'user',
            'content' => 'Зачисление денежных средств по договору об обслуживании держателей платежных карт по терминалу TID 30019734 за 2024-02-14. Сумма комиссии 401.58. НДС не предусмотрен, без НДС',
        ];
        
        $result = $this->gigaManager->completions($messages);

        return new JsonModel($result);
    }
    
    public function articleTestAction()
    {
        $messages = [];
        $messages[] = [
            'role' => 'system',
            'content' => 'Ты специалист по логистеке. Нужно найти артикул товара в описании. Ответь в формате JSON {"article":"xxxxx"}',
        ];
        $messages[] = [
            'role' => 'user',
            'content' => 'AMD.JFC85 Фильтр салона NISSAN Qashqai I J10E X-Tr',
        ];
        
        $result = $this->gigaManager->completions($messages);

        return new JsonModel($result);
    }
}
