<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Oem;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241022075928 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('oem');
        $table->addColumn('rating', 'integer', ['notnull' => true, 'default' => 0, 'comment' => 'Рейтинг']);
        $table->addColumn('order_count', 'integer', ['notnull' => true, 'default' => 0, 'comment' => 'Количество продаж']);
        $table->addColumn('return_count', 'integer', ['notnull' => true, 'default' => 0, 'comment' => 'Количество возвратов']);
        $table->addIndex(['rating', 'status', 'oe'], 'rating_indx');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('oem');
        $table->dropIndex('rating_indx');
        $table->dropColumn('rating');
        $table->dropColumn('order_count');
        $table->dropColumn('return_count');
    }
}
