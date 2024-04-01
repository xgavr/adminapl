<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240401170752 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('movement');
        $table->addColumn('user_id', 'integer', ['notnull' => false, 'comment' => 'Менеджер']);
        $table->addForeignKeyConstraint('user', ['user_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'movement_user_id_user_id_fk');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('movement');
        $table->removeForeignKey('movement_user_id_user_id_fk');
        $table->dropColumn('user_id');
    }
}
