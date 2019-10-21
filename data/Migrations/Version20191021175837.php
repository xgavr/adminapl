<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Platforms\MySqlPlatform;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191021175837 extends AbstractMigration
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
        $table = $schema->createTable('bigram');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('bilemma_md5', 'string', ['notnull'=>true, 'length' => 128]);        
        $table->addColumn('bilemma', 'string', ['notnull'=>true, 'length' => 256]);        
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default' => 10]);        
        $table->addColumn('flag', 'integer', ['notnull'=>true, 'default' => 1]);
        $table->addColumn('frequency', 'integer', ['notnull' => true, 'default' => -1]);
        $table->addColumn('idf', 'float', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['bilemma_md5'], 'bilemma_md5_indx');
        $table->addOption('engine' , 'InnoDB');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('bigram');
    }
}
