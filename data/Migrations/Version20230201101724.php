<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230201101724 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('retail');
        $table->addColumn('doc_stamp', 'float', ['notnull' => true, 'default' => 0]);
        $table->addColumn('legal_id', 'integer', ['notnull'=>false]);
        $table->addColumn('contract_id', 'integer', ['notnull'=>false]);
        $table->addIndex(['doc_stamp'], 'doc_stamp_indx');
        $table->addForeignKeyConstraint('legal', ['legal_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'legal_id_retail_legal_id_fk');
        $table->addForeignKeyConstraint('contract', ['contract_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'contract_id_retail_contract_id_fk');

        $table = $schema->getTable('mutual');
        $table->addColumn('doc_stamp', 'float', ['notnull' => true, 'default' => 0]);
        $table->addIndex(['doc_stamp'], 'doc_stamp_indx');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('mutual');
        $table->dropIndex('doc_stamp_indx');
        $table->dropColumn('doc_stamp');

        $table = $schema->getTable('retail');
        $table->removeForeignKey('legal_id_retail_legal_id_fk');
        $table->removeForeignKey('contract_id_retail_contract_id_fk');
        $table->dropIndex('doc_stamp_indx');
        $table->dropColumn('legal_id');
        $table->dropColumn('contract_id');
        $table->dropColumn('doc_stamp');
    }
}
