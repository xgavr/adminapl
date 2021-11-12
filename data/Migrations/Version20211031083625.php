<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\MarketPriceSetting;
use Company\Entity\Office;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211031083625 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('market_price_setting');
        $table->addColumn('pricecol', 'integer', ['notnull'=>true, 'default'=> 0]);
        $table->addColumn('row_unload', 'integer', ['notnull'=>true, 'default'=> 0]);
        $table->addColumn('movement_limit', 'integer', ['notnull'=>true, 'default'=> MarketPriceSetting::MOVEMENT_LIMIT]);
        $table->addColumn('supplier_id', 'integer', ['notnull'=>false]);
        $table->addColumn('shipping_id', 'integer', ['notnull'=>false]);
        $table->addColumn('name_setting', 'integer', ['notnull'=>true, 'default'=> MarketPriceSetting::NAME_ALL]);
        $table->addColumn('rest_setting', 'integer', ['notnull'=>true, 'default'=> MarketPriceSetting::REST_ALL]);
        $table->addColumn('td_setting', 'integer', ['notnull'=>true, 'default'=> MarketPriceSetting::TD_IGNORE]);
        $table->addForeignKeyConstraint('supplier', ['supplier_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'supplier_id_mps_supplier_id_fk');
        $table->addForeignKeyConstraint('shipping', ['shipping_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'shipping_id_mps_shipping_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->createTable('market_rate');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('market_id', 'integer', ['notnull'=>true]);
        $table->addColumn('rate_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('market_price_setting', ['market_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'market_rate_id_market_id_fk');
        $table->addForeignKeyConstraint('rate', ['rate_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'market_rate_rate_id_fk');
        $table->addOption('engine' , 'InnoDB');        

        $table = $schema->createTable('supplier_region');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('supplier_id', 'integer', ['notnull'=>true]);
        $table->addColumn('region_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('supplier', ['supplier_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'supplier_region_id_supplier_id_fk');
        $table->addForeignKeyConstraint('region', ['region_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'supplier_region_id_region_id_fk');
        $table->addOption('engine' , 'InnoDB');        

        $table = $schema->getTable('office');
        $table->addColumn('shipping_limit_1', 'integer', ['notnull'=>true, 'default' => Office::DEFAULT_SHIPPING_LIMIT_1]);
        $table->addColumn('shipping_limit_2', 'integer', ['notnull'=>true, 'default' => Office::DEFAULT_SHIPPING_LIMIT_2]);

        $table = $schema->getTable('shipping');
        $table->addColumn('rate_trip_1', 'float', ['notnull'=>true, 'default' => 0]);
        $table->addColumn('rate_trip_2', 'float', ['notnull'=>true, 'default' => 0]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('market_rate');
        $schema->dropTable('supplier_region');

        $table = $schema->getTable('market_price_setting');
        $table->removeForeignKey('supplier_id_mps_supplier_id_fk');
        $table->removeForeignKey('shipping_id_mps_shipping_id_fk');
        $table->dropColumn('pricecol');
        $table->dropColumn('movement_limit');
        $table->dropColumn('row_unload');
        $table->dropColumn('name_setting');
        $table->dropColumn('rest_setting');
        $table->dropColumn('td_setting');
        $table->dropColumn('supplier_id');
        $table->dropColumn('supplier_id');

        $table = $schema->getTable('office');
        $table->dropColumn('shipping_limit_1');
        $table->dropColumn('shipping_limit_2');

        $table = $schema->getTable('shipping');
        $table->dropColumn('rate_trip_1');
        $table->dropColumn('rate_trip_2');
    }
}
