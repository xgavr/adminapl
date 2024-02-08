<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Company\Entity\Tax;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240208074412 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('tax');
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default' => Tax::STATUS_ACTIVE, 'comment' => 'Статус']);
        $table->addColumn('kind', 'integer', ['notnull'=>true,'default' => Tax::KIND_NDS, 'comment' => 'Тип']);
        $table->addColumn('date_start', 'date', ['notnull'=>true, 'comment' => 'Дата начала']);

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('tax');
        $table->dropColumn('status');
        $table->dropColumn('kind');
        $table->dropColumn('date_start');
    }
}
