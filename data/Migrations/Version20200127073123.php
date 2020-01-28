<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Platforms\MySqlPlatform;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200127073123 extends AbstractMigration
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
        $table = $schema->createTable('fp_tree');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('root_tree_id', 'integer', ['notnull'=>true, 'default' => 0]);
        $table->addColumn('root_token_id', 'integer', ['notnull'=>true, 'default' => 0]);
        $table->addColumn('token_id', 'integer', ['notnull'=>true]);
        $table->addColumn('frequency', 'integer', ['notnull'=>true, 'default' => 0]); //счетчик
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['root_tree_id', 'root_token_id', 'token_id'], 'rtt_uindx');
        $table->addForeignKeyConstraint('token', ['token_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'fp_tree_token_id_token_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->getTable('article_title');
        $table->addColumn('fp_tree_id', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addIndex(['fp_tree_id'], 'fp_tree_id_indx');

        $table = $schema->getTable('article_token');
        $table->addColumn('title_id', 'bigint', ['notnull' => true, 'default' => 0]);
        $table->addForeignKeyConstraint('article_title', ['title_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'art_token_title_id_art_title_id_fk');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('article_token');
        $table->removeForeignKey('article_token_title_id_article_title_id_fk');
        $table->dropColumn('title_id');

        $table = $schema->getTable('article_title');
        $table->dropColumn('fp_tree_id');
        
        $table = $schema->getTable('fp_tree');
        $table->removeForeignKey('fp_tree_token_id_token_id_fk');
        $schema->dropTable('fp_tree');
    }
}
