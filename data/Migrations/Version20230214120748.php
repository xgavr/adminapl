<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Company\Entity\LegalLocation;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230214120748 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('legal_location');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('address', 'text', ['notnull'=>true]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default' => LegalLocation::STATUS_ACTIVE]);
        $table->addColumn('date_start', 'date', ['notnull'=>true]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->addColumn('legal_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('legal', ['legal_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete'=>'CASCADE'], 'legal_id_location_legal_id_fk');
        $table->addOption('engine' , 'InnoDB');        
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('legal_location');
    }
}
