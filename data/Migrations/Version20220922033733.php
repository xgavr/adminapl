<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Stock\Entity\Reserve;
use Stock\Entity\GoodBalance;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220922033733 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('reserve');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('good_id', 'integer', ['notnull'=>true]);
        $table->addColumn('office_id', 'integer', ['notnull' => true]);
        $table->addColumn('company_id', 'integer', ['notnull' => true]);
        $table->addColumn('user_id', 'integer', ['notnull' => false]);
        $table->addColumn('doc_key', 'string', ['notnull' => true]);
        $table->addColumn('rest', 'float', ['notnull' => true, 'default' => 0]);
        $table->addColumn('status', 'integer', ['notnull' => true, 'default' => Reserve::STATUS_RESERVE]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['good_id', 'office_id', 'company_id'], 'good_off_com_indx');
        $table->addIndex(['doc_key'], 'doc_key_indx');
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'reserve_good_id_good_id_fk');
        $table->addForeignKeyConstraint('office', ['office_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'reserve_office_id_office_id_fk');
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'reserve_company_id_legal_id_fk');
        $table->addForeignKeyConstraint('user', ['user_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'reserve_user_id_user_id_fk');
        $table->addOption('engine' , 'InnoDB');        

        $table = $schema->getTable('good_balance');
        $table->addColumn('status', 'integer', ['notnull' => true, 'default' => GoodBalance::STATUS_ACTIVE]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('reserve');
        
        $table = $schema->getTable('good_balance');
        $table->dropColumn('status');
    }
}
