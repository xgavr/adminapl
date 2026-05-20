<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260520110329 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('car_fill_volume');
        $table->addColumn('volume_norm', 'string', ['notnull' => false, 'comment' => 'Норм. значение']);
        
        $table->addIndex(['volume_norm'], 'volume_norm_indx');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('car_fill_volume');
        $table->dropColumn('volume_norm');
    }
}
