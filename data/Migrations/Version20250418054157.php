<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250418054157 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bill_setting');
        $table->addColumn('inn_col', 'integer', ['notnull' => false]);
        $table->addColumn('inn_row', 'integer', ['notnull' => false]);

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bill_setting');
        $table->dropColumn('inn_col');
        $table->dropColumn('inn_row');
    }
}
