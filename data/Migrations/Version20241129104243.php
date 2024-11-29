<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241129104243 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('office');
        $table->addColumn('address', 'string', ['notnull' => false, 'length' => 256, 'comment' => 'Адрес']);
        $table->addColumn('address_sms', 'string', ['notnull' => false, 'length' => 128, 'comment' => 'Короткий адрес']);

        $table = $schema->getTable('bid');
        $table->addColumn('price0', 'float', ['notnull' => true, 'default' => 0, 'comment' => 'Цена розница']);

        $table = $schema->getTable('token_group');
        $table->addColumn('parent_id', 'integer', ['notnull' => false, 'default' => 0, 'comment' => 'Группа выше']);
        $table->addIndex(['parent_id'], 'parent_id_indx');
//        $table->addForeignKeyConstraint('token_group', ['parent_id'], ['id'], 
//                ['onUpdate'=>'CASCADE'], 'token_group_parent_id_token_group_id_fk');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('office');
        $table->dropColumn('address');
        $table->dropColumn('address_sms');
        
        $table = $schema->getTable('bid');
        $table->dropColumn('price0');

        $table = $schema->getTable('token_group');
//        $table->removeForeignKey('token_group_parent_id_token_group_id_fk');
        $table->dropIndex('parent_id_indx');
        $table->dropColumn('parent_id');
    }
}
