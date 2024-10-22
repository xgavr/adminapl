<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241022073138 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('post_log');
        $table->addColumn('message_id', 'string', ['notnull' => false, 'length' => 256, 'comment' => 'Ид письма']);
        $table->addIndex(['message_id'], 'message_id_indx');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('post_log');
        $table->dropIndex('message_id_indx');
        $table->dropColumn('message_id');
    }
}
