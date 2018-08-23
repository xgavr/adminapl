<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180822132751 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('bank_balance');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('bic', 'string', ['notnull' => true, 'length' => 9]);
        $table->addColumn('account', 'string', ['notnull' => true, 'length' => 20]);
        $table->addColumn('date_balance', 'date', ['notnull' => true]);
        $table->addColumn('balance', 'float', ['notnull' => true, 'default' => 0]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['bic', 'account', 'date_balance'], 'bic_account_date_balance_idx');
        $table->addOption('engine' , 'InnoDB');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('bank_balance');
    }
}
