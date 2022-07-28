<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220728144326 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bid');
        $table->removeForeignKey('oem_id_bid_oem_id_fk');
        $table->addForeignKeyConstraint('oem', ['oem_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'oem_id_bid_oem_id_fk');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bid');
        $table->removeForeignKey('oem_id_bid_oem_id_fk');
        $table->addForeignKeyConstraint('oem', ['oem_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'oem_id_bid_oem_id_fk');
    }
}
