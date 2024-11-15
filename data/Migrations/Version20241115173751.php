<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Stock\Entity\Movement;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241115173751 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('movement');
        $table->addColumn('parent_doc_id', 'integer', ['notnull' => false, 'comment' => 'Документ основание']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('movement');
        $table->dropColumn('parent_doc_id');
    }
}
