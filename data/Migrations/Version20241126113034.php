<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Company\Entity\Contract;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241126113034 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('contract');
        $table->addColumn('date_revision', 'date', ['notnull' => false, 'comment' => 'Дата сверки']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('contract');
        $table->dropColumn('date_revision');
    }
}
