<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\SupplierApiSetting;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210304130234 extends AbstractMigration
{
    public function getDescription()
    {
        $description = 'A migration which creates the `supplier_api_setting` table.';
        return $description;
    }
    
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Create 'price_description' table
        $table = $schema->createTable('supplier_api_setting');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('supplier_id', 'integer', ['notnull'=>true]);        
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default' => SupplierApiSetting::STATUS_RETIRED]);        
        $table->addColumn('name', 'string', ['notnull'=>false, 'length' => 128]);        
        $table->addColumn('login', 'string', ['notnull'=>false, 'length' => 128]);        
        $table->addColumn('password', 'string', ['notnull'=>false, 'length' => 128]);        
        $table->addColumn('user_id', 'string', ['notnull'=>false, 'length' => 128]);        
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('supplier', ['supplier_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'supplier_id_sas_supplier_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('supplier_api_setting');
    }
}
