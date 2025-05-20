<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250520031006 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('goods');
        $table->addColumn('sale_month', 'integer', ['notnull' => true, 'default' => 0, 'comment' => 'Продажи за последний месяц']);
        
        $table = $schema->getTable('producer');
        $table->addColumn('sale_month', 'integer', ['notnull' => true, 'default' => 0, 'comment' => 'Продажи за последний месяц']);
        
        $table = $schema->getTable('group_site');
        $table->addColumn('sale_month', 'integer', ['notnull' => true, 'default' => 0, 'comment' => 'Продажи за последний месяц']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('goods');
        $table->dropColumn('sale_month');
        
        $table = $schema->getTable('producer');
        $table->dropColumn('sale_month');
        
        $table = $schema->getTable('group_site');
        $table->dropColumn('sale_month');
    }
}
