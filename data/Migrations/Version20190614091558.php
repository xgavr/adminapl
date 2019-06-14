<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Platforms\MySqlPlatform;
/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190614091558 extends AbstractMigration
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
        $table = $schema->createTable('article_title');
        $table->addColumn('id', 'bigint', ['autoincrement'=>true]);
        $table->addColumn('article_id', 'integer', ['notnull'=>true]);
        $table->addColumn('title', 'string', ['notnull'=>true, 'length' => 512]);        
        $table->addColumn('title_md5', 'string', ['notnull'=>true, 'length' => 128]);        
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['article_id', 'title_md5'], 'art_id_ttl_md5_uindx');
        $table->addForeignKeyConstraint('article', ['article_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'article_title_article_id_article_id_fk');
        $table->addOption('engine' , 'InnoDB'); 

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('article_title');        

    }
}
