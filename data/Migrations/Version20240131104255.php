<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240131104255 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('cash_doc');
        $table->addColumn('statement_id', 'integer', ['notnull' => false, 'comment' => 'Строка выписки']);
        $table->addForeignKeyConstraint('bank_statement', ['statement_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'cash_doc_statement_id_bank_statement_id_fk');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('cash_doc');
        $table->removeForeignKey('cash_doc_statement_id_bank_statement_id_fk');
        $table->dropColumn('statement_id');
    }
}
