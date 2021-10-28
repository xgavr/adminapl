<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\MarketPriceSetting;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211025143422 extends AbstractMigration
{
    
    
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('market_price_setting');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> MarketPriceSetting::STATUS_ACTIVE]);
        $table->addColumn('name', 'string', ['notnull'=>true, 'length' => 128]);
        $table->addColumn('filename', 'string', ['notnull'=>true, 'length' => 128]);
        $table->addColumn('format', 'integer', ['notnull'=>true, 'default'=> MarketPriceSetting::FORMAT_YML]);
        $table->addColumn('good_setting', 'integer', ['notnull'=>true, 'default'=> MarketPriceSetting::IMAGE_ALL]);
        $table->addColumn('image_count', 'integer', ['notnull'=>true, 'default'=> 0]);
        $table->addColumn('supplier_setting', 'integer', ['notnull'=>true, 'default'=> MarketPriceSetting::SUPPLIER_ALL]);
        $table->addColumn('producer_setting', 'integer', ['notnull'=>true, 'default'=> MarketPriceSetting::PRODUCER_ALL]);
        $table->addColumn('group_setting', 'integer', ['notnull'=>true, 'default'=> MarketPriceSetting::GROUP_ALL]);
        $table->addColumn('token_group_setting', 'integer', ['notnull'=>true, 'default'=> MarketPriceSetting::TOKEN_GROUP_ALL]);
        $table->addColumn('min_price', 'integer', ['notnull'=>true, 'default'=> 0]);
        $table->addColumn('max_price', 'integer', ['notnull'=>true, 'default'=> 0]);
        $table->addColumn('max_row_count', 'integer', ['notnull'=>true, 'default'=> 0]);
        $table->addColumn('block_row_count', 'integer', ['notnull'=>true, 'default'=> 0]);
        $table->addColumn('info', 'string', ['notnull'=>false, 'length' => 512]);
        $table->addColumn('date_unload', 'datetime', ['notnull'=>false]);
        $table->addColumn('region_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('region', ['region_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'region_id_mps_region_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->getTable('producer');
        $table->addColumn('movement', 'integer', ['notnull'=>true, 'default'=> 0]);
        
        $table = $schema->getTable('generic_group');
        $table->addColumn('movement', 'integer', ['notnull'=>true, 'default'=> 0]);
                
        $table = $schema->getTable('token_group');
        $table->addColumn('movement', 'integer', ['notnull'=>true, 'default'=> 0]);
                
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('market_price_setting');

        $table = $schema->getTable('producer');
        $table->dropColumn('movement');
        
        $table = $schema->getTable('generic_group');
        $table->dropColumn('movement');
        
        $table = $schema->getTable('token_group');
        $table->dropColumn('movement');
    }
}
