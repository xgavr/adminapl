<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231009205931 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('contact_legal');
        $table->addForeignKeyConstraint('contact', ['contact_id'], ['id'], 
            ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'contact_legal_contact_id_contact_id_fk');
        $table->addForeignKeyConstraint('legal', ['legal_id'], ['id'], 
            ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'contact_legal_legal_id_legal_id_fk');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('contact_legal');
        $table->removeForeignKey('contact_legal_contact_id_contact_id_fk');
        $table->removeForeignKey('contact_legal_legal_id_legal_id_fk');
    }
}
