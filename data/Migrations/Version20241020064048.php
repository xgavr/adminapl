<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Raw;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241020064048 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('raw');
        $table->addColumn('tmp_file', 'string', ['notnull' => false, 'length' => 256, 'comment' => 'Временный файл']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('raw');
        $table->dropColumn('tmp_file');
    }
}
