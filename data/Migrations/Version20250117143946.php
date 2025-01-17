<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250117143946 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('personal_revise');
//        $table->addColumn('vacation_period', 'integer', ['notnull' => false, 'comment' => 'Отпуск дней']);
        $table->addColumn('vacation_from', 'date', ['notnull' => false, 'comment' => 'Отпуск с']);
        $table->addColumn('vacation_to', 'date', ['notnull' => false, 'comment' => 'Отпуск по']);
        $table->addColumn('info', 'json', ['notnull' => false, 'comment' => 'Описание']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('personal_revise');
//        $table->dropColumn('vacation_period');
        $table->dropColumn('vacation_from');
        $table->dropColumn('vacation_to');
        $table->dropColumn('info');
    }
}
