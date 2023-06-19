<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230619071235 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('legal');
        $table->addColumn('sbp_legal_id', 'string', ['notnull' => false, 'length' => 64, 'comment' => 'Уникальный код клиента в СПБ']);

        $table = $schema->getTable('office');
        $table->addColumn('sbp_merchant_id', 'string', ['notnull' => false, 'length' => 64, 'comment' => 'Идентификатор ТСП в СПБ']);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('legal');
        $table->dropColumn('sbp_legal_id');

        $table = $schema->getTable('office');
        $table->dropColumn('sbp_merchant_id');
    }
}
