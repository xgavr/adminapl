<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250312054853 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('cash_doc');
        $table->addColumn('contract_id', 'integer', ['notnull'=>false, 'comment' => 'Договор']);
        $table->addForeignKeyConstraint('contract', ['contract_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'cash_doc_contract_id_contract_id_fk');        
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('cash_doc');
        $table->removeForeignKey('cash_doc_contract_id_contract_id_fk');
        $table->dropColumn('contract_id');
    }
}
