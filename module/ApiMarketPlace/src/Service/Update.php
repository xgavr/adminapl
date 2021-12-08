<?php
/**
 * This file is part of the ApiMarketPlace.
 *
 */

namespace ApiMarketPlace\Service;

use ApiMarketPlace\Exception\ApiMarketPlaceException;
use ApiMarketPlace\Entity\Marketplace;
use ApiMarketPlace\Entity\MarketplaceUpdate;

class Update
{
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Добавить запись от торговой площадки
     * @param array $data
     * @return null
     */
    public function add($data)
    {
        $this->entityManager->getConnection()
                ->insert('marketplace_update', [
                    'post_data' => $data['post_data'],
                    'status' => MarketplaceUpdate::STATUS_ACTIVE,
                    'date_created' => date('Y-m-d H:i:s')
                ]);
        
        return;
    }
    
}
