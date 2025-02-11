<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250211105502 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('group_site');
        $table->addColumn('apl_id', 'integer', ['notnull' => true, 'default' => 0, 'comment' => 'Код в АПЛ']);
        $table->addIndex(['apl_id'], 'apl_id_indx');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('group_site');
        $table->dropColumn('apl_id');
    }
}
