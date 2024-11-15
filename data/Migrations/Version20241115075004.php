<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Stock\Entity\Movement;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241115075004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавить поле client в movements';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('movement');
        $table->addColumn('client_id', 'integer', ['notnull' => false, 'comment' => 'Клиент']);
        $table->addForeignKeyConstraint('client', ['client_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'movement_client_id_client_id_fk');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('movement');
        $table->removeForeignKey('movement_client_id_client_id_fk');
        $table->dropColumn('client_id');
    }
}
