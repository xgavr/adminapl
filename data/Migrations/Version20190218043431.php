<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Platforms\MySqlPlatform;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190218043431 extends AbstractMigration
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
        $table = $schema->createTable('good_car');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]); 
        $table->addColumn('good_id', 'integer', ['notnull'=>true]);
        $table->addColumn('car_id', 'integer', ['notnull'=>true]);
//        $table->addIndex(['good_id'], 'good_id_indx');
//        $table->addIndex(['car_id'], 'car_id_indx');
        $table->addForeignKeyConstraint('good', ['good_id'], ['id'], ['onUpdate'=>'CASCADE', 'onDelete' => 'CASCADE'], 'good_id_good_car_good_id_fk');
        $table->addForeignKeyConstraint('car', ['car_id'], ['id'], ['onUpdate'=>'CASCADE', 'onDelete' => 'CASCADE'], 'car_id_good_car_car_id_fk');
        $table->setPrimaryKey(['id']);
        $table->addOption('engine' , 'InnoDB');  
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('good_car');
    }
}
