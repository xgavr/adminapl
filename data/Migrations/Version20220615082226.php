<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\SupplierOrder;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220615082226 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('supplier_order');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default' => SupplierOrder::STATUS_NEW]);
        $table->addColumn('status_order', 'integer', ['notnull'=>true, 'default' => SupplierOrder::STATUS_ORDER_NEW]);
        $table->addColumn('order_id', 'integer', ['notnull'=>true]);
        $table->addColumn('good_id', 'integer', ['notnull'=>true]);
        $table->addColumn('supplier_id', 'integer', ['notnull'=>true]);
        $table->addColumn('quantity', 'float', ['notnull'=>true]);
        $table->addColumn('comment', 'string', ['notnull'=>false, 'length' => 128]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('orders', ['order_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'so_order_id_order_id_fk');
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'so_good_id_good_id_fk');
        $table->addForeignKeyConstraint('supplier', ['supplier_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'so_supplier_id_supplier_id_fk');
        $table->addOption('engine' , 'InnoDB');        

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('supplier_order');

    }
}
