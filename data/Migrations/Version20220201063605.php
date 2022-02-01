<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220201063605 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('comment');
        $table->addColumn('client_id', 'integer', ['notnull'=>false]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->addForeignKeyConstraint('client', ['client_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'client_id_comment_client_id_fk');        
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('comment');
        $table->removeForeignKey('client_id_comment_client_id_fk');
        $table->dropColumn('client_id');
        $table->dropColumn('date_created');
    }
}
