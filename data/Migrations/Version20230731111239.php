<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230731111239 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bank_account');
        $table->addColumn('cash_sbp_id', 'integer', ['notnull' => false]);
        $table->addForeignKeyConstraint('cash', ['cash_sbp_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'bank_account_cash_sbp_id_cash_id_fk');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bank_account');
        $table->removeForeignKey('bank_account_cash_sbp_id_cash_id_fk');
        $table->dropColumn('cash_sbp_id');
    }
}
