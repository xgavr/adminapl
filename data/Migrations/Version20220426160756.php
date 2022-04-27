<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Company\Entity\Commission;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220426160756 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('commission');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('office_id', 'integer', ['notnull'=>true]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> Commission::STATUS_MEMBER]);
        $table->addColumn('position', 'string', ['notnull'=>false, 'length'=>64]);
        $table->addColumn('name', 'string', ['notnull'=>false, 'length'=>64]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('office', ['office_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'office_id_commission_office_id_fk');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('commission');
    }
}
