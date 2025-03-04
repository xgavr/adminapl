<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250304045412 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('good_group_site');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('good_id', 'integer', ['notnull'=>true, 'comment' => 'Товар']);
        $table->addColumn('group_site_id', 'integer', ['notnull'=>true, 'comment' => 'Категория']);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['good_id', 'group_site_id'], 'good_id_group_site_id_uindx');
        $table->addForeignKeyConstraint('group_site', ['group_site_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'good_group_site_id_group_site_id_fk');
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'good_group_site_id_good_id_fk');
        $table->addOption('engine' , 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs        
        $schema->dropTable('good_group_site');
    }
}
