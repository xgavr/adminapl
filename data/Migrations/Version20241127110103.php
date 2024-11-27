<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fin\Entity\FinDds;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241127110103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('fin_dds');
        $table->addColumn('deposit_begin', 'float', ['notnull' => true, 'default' => 0, 'comment' => 'Депозит на начало']);
        $table->addColumn('deposit_end', 'float', ['notnull' => true, 'default' => 0, 'comment' => 'Депозит на конец']);

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('fin_dds');
        $table->dropColumn('deposit_begin');
        $table->dropColumn('deposit_end');
    }
}
