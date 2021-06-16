<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210615032953 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('selection');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('comment', 'string', ['notnull'=>false, 'length'=>128]);
        $table->addColumn('oem_id', 'integer', ['notnull'=>true]);
        $table->addColumn('order_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('oem', ['oem_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'oem_id_selection_oem_id_fk');
        $table->addForeignKeyConstraint('orders', ['order_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'order_id_selection_order_id_fk');
        $table->addOption('engine' , 'InnoDB');
                
        $table = $schema->createTable('comment');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('comment', 'text', ['notnull'=>false]);
        $table->addColumn('order_id', 'integer', ['notnull'=>true]);
        $table->addColumn('user_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('user', ['user_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'user_id_comment_user_id_fk');
        $table->addForeignKeyConstraint('orders', ['order_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'order_id_comment_order_id_fk');
        $table->addOption('engine' , 'InnoDB');
                

        $table = $schema->getTable('bid');
        $table->addColumn('oem_id', 'integer', ['notnull'=>false]);
        $table->addForeignKeyConstraint('oem', ['oem_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'oem_id_bid_oem_id_fk');
        
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bid');
        $table->removeForeignKey('oem_id_bid_oem_id_fk');
        $table->dropColumn('oem_id');
        
        $schema->dropTable('comment');
        $schema->dropTable('selection');
    }
}
