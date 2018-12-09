<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Platforms\MySqlPlatform;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181207202501 extends AbstractMigration
{
    /**
     * @param boolean $enabled
     */
    protected function setForeignKeyChecks($enabled)
    {
        $connection = $this->connection;
        $platform = $connection->getDatabasePlatform();
        if ($platform instanceof MySqlPlatform) {
            $connection->exec(sprintf('SET foreign_key_checks = %s;', (int)$enabled));
        }
    }

    /**
     * @param Schema $schema
     */
    public function preUp(Schema $schema)
    {
        parent::preUp($schema);
        $this->setForeignKeyChecks(false);
    }

    /**
     * @param Schema $schema
     */
    public function postUp(Schema $schema)
    {
        parent::postUp($schema);
        $this->setForeignKeyChecks(true);
    }

    /**
     * @param Schema $schema
     */
    public function preDown(Schema $schema)
    {
        parent::preDown($schema);
        $this->setForeignKeyChecks(false);
    }

    /**
     * @param Schema $schema
     */
    public function postDown(Schema $schema)
    {
        parent::postDown($schema);
        $this->setForeignKeyChecks(true);
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
//        $table = $schema->createTable('producer_unknown_producer');
//        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
//        $table->addColumn('producer_id', 'integer', ['notnull'=>true]);
//        $table->addColumn('unknown_producer_id', 'integer', ['notnull'=>true]);
//        $table->setPrimaryKey(['id']);
//        $table->addForeignKeyConstraint('producer', ['producer_id'], ['id'], 
//                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'producer_unknown_producer_producer_id_fk');
//        $table->addForeignKeyConstraint('unknown_producer', ['unknown_producer_id'], ['id'], 
//                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'unknown_producer_producer_unknown_producer_id_fk');
//        $table->addOption('engine' , 'InnoDB'); 

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
//        $schema->dropTable('producer_unknown_producer');        

    }
}
