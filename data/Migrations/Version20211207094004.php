<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211207094004 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('marketplace');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('name', 'string', ['notnull'=>true, 'length' => 64]);
        $table->addColumn('site', 'string', ['notnull'=>true, 'length' => 256]);
        $table->addColumn('login', 'string', ['notnull'=>true, 'length' => 256]);
        $table->addColumn('password', 'string', ['notnull'=>true, 'length' => 256]);
        $table->addColumn('comment', 'string', ['notnull'=>false, 'length' => 512]);
        $table->addColumn('merchantId', 'integer', ['notnull'=>true]);
        $table->addColumn('status', 'integer', ['notnull'=>true]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addOption('engine' , 'InnoDB');        

        $table = $schema->createTable('marketplace_update');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('marketplace_id', 'integer', ['notnull'=>false]);
        $table->addColumn('order_id', 'integer', ['notnull'=>false]);
        $table->addColumn('post_data', 'json', ['notnull'=>false]);
        $table->addColumn('status', 'integer', ['notnull'=>true]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('marketplace', ['marketplace_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'mpu_marketplace_id_mp_id_fk');
        $table->addForeignKeyConstraint('orders', ['order_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'mpu_order_id_orders_id_fk');
        $table->addOption('engine' , 'InnoDB');        

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('marketplace');
        $table = $schema->createTable('marketplace_update');
    }
}
