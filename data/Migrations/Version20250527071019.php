<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250527071019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('car');
        $table->addColumn('sale_count', 'integer', ['notnull' => true, 'default' => 0, 'comment' => 'Количество продаж']);
        $table->addColumn('sale_month', 'integer', ['notnull' => true, 'default' => 0, 'comment' => 'Количество продаж за месяц']);
        $table->addIndex(['sale_count'], 'sale_count_indx');
        $table->addIndex(['sale_month'], 'sale_month_indx');
        
        $table = $schema->getTable('model');
        $table->addColumn('sale_count', 'integer', ['notnull' => true, 'default' => 0, 'comment' => 'Количество продаж']);
        $table->addColumn('sale_month', 'integer', ['notnull' => true, 'default' => 0, 'comment' => 'Количество продаж за месяц']);
        $table->addIndex(['sale_count'], 'sale_count_indx');
        $table->addIndex(['sale_month'], 'sale_month_indx');
        
        $table = $schema->getTable('make');
        $table->addColumn('sale_count', 'integer', ['notnull' => true, 'default' => 0, 'comment' => 'Количество продаж']);
        $table->addColumn('sale_month', 'integer', ['notnull' => true, 'default' => 0, 'comment' => 'Количество продаж за месяц']);
        $table->addIndex(['sale_count'], 'sale_count_indx');
        $table->addIndex(['sale_month'], 'sale_month_indx');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('car');
        $table->dropColumn('sale_count');
        $table->dropColumn('sale_month');
        
        $table = $schema->getTable('model');
        $table->dropColumn('sale_count');
        $table->dropColumn('sale_month');
        
        $table = $schema->getTable('make');
        $table->dropColumn('sale_count');
        $table->dropColumn('sale_month');
    }
}
