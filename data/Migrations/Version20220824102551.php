<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Admin\Entity\Wammchat;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220824102551 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('wammchat');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('order_id', 'integer', ['notnull'=>false]);
        $table->addColumn('msg_id', 'integer', ['notnull' => true]);
        $table->addColumn('from_me', 'integer', ['notnull' => true]);
        $table->addColumn('phone', 'string', ['notnull' => true, 'length' => 24]);
        $table->addColumn('chat_name', 'string', ['notnull' => true, 'length' => 48]);
        $table->addColumn('tip_msg', 'string', ['notnull' => true, 'length' => 24]);
        $table->addColumn('msg_text', 'string', ['notnull' => true, 'length' => 256]);
        $table->addColumn('msg_link', 'string', ['notnull' => true, 'length' => 256]);
        $table->addColumn('date_ins', 'datetime', ['notnull' => true]);
        $table->addColumn('date_upd', 'datetime', ['notnull' => false]);
        $table->addColumn('state', 'string', ['notnull' => true, 'length' => 24]);
        $table->addColumn('status', 'integer', ['notnull' => true, 'default' => Wammchat::STATUS_ACTIVE]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['msg_id'], 'msg_id_uindx');
        $table->addForeignKeyConstraint('orders', ['order_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'wammchat_order_id_order_id_fk');
        $table->addOption('engine' , 'InnoDB');        
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('wammchat');
    }
}
