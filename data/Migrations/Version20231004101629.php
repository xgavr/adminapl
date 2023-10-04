<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231004101629 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('apl_payment');
        $table->addColumn('cash_doc_id', 'integer', ['notnull' => false, 'default' => null]);
        $table->addForeignKeyConstraint('cash_doc', ['cash_doc_id'], ['id'], 
            ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'cashdoc_id_aplpayment_cashdoc_id_fk');
}

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('apl_payment');
        $table->removeForeignKey('cashdoc_id_aplpayment_cashdoc_id_fk');
        $table->dropColumn('cash_doc_id');
    }
}
