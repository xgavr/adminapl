<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250730175821 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('orders');
        $table->addColumn('fasade_id', 'integer', ['notnull' => false, 'comment' => 'Номер заказа фасада']);
        $table->addIndex(['fasade_id'], 'fasade_id_indx');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('orders');
        $table->dropColumn('fasade_id');
    }
}
