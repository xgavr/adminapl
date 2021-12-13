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
        $conn = $this->entityManager->getConnection();
        $conn->insert('marketplace_update', [
                    'post_data' => $data['post_data'],
                    'status' => MarketplaceUpdate::STATUS_ACTIVE,
                    'date_created' => date('Y-m-d H:i:s'),
                    'remote_addr' => $_SERVER['remote_addr'],
                ]);
        
        return $conn->lastInsertId();
    }
    
}
