<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Producer;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251107095312 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('producer');
        $table->addColumn('description', 'text', ['notnull' => false, 'comment' => 'Описание']);
        $table->addColumn('is_original', 'integer', ['notnull' => true, 'default' => Producer::ORIGINAL_NO, 'comment' => 'Оригинал']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('producer');
        $table->dropColumn('description');
        $table->dropColumn('is_original');
    }
}
