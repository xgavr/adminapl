<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240406152315 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('cash');
        $table->addColumn('account_number', 'string', ['notnull' => false, 'length' => 24, 'comment' => 'Бухсчет']);
        $table->addColumn('bank_inn', 'string', ['notnull' => false, 'length' => 24, 'comment' => 'ИНН Банка платежного сервиса']);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('cash');
        $table->dropColumn('account_number');
        $table->dropColumn('bank_inn');
    }
}
