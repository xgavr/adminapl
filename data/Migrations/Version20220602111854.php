<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220602111854 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('orders');
//        $table->removeForeignKey('client_id_orders_client_id_fk');
        $table->addColumn('bank_account_id', 'integer', ['notnull' => false]);
        $table->addForeignKeyConstraint('bank_account', ['bank_account_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'ba_id_orders_ba_id_fk');
        
        $table = $schema->getTable('user');
        $table->dropColumn('sign');
        $table->dropColumn('mail_password');
        
        $table = $schema->getTable('email');
        $table->addColumn('mail_password', 'string', ['notnull' => false, 'length' => 256]);        
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('orders');
        $table->removeForeignKey('ba_id_orders_ba_id_fk');
        $table->dropColumn('bank_account_id');
        
        $table = $schema->getTable('user');
        $table->addColumn('sign', 'text', ['notnull' => false]);
        $table->addColumn('mail_password', 'string', ['notnull' => false, 'length' => 64]);        

        $table = $schema->getTable('email');
        $table->dropColumn('mail_password');
    }
}
