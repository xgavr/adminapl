<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Platforms\MySqlPlatform;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200223080305 extends AbstractMigration
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
        $table = $schema->getTable('article_bigram');
        $table->dropIndex('article_id_bigram_id_uindx');
        $table->addColumn('title_id', 'bigint', ['notnull' => true, 'default' => 0]);
        $table->addColumn('display_bilemma', 'string', ['notnull' => false, 'length' => 256]);
        $table->addUniqueIndex(['article_id', 'title_id', 'bigram_id'], 'article_bigram_title_uindx');
        $table->addForeignKeyConstraint('article_title', ['title_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'art_bigram_title_id_art_title_id_fk');

        $table = $schema->getTable('article_token');
        $table->addColumn('display_lemma', 'string', ['notnull' => false, 'length' => 256]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('article_bigram');
        $table->removeForeignKey('art_bigram_title_id_art_title_id_fk');
        $table->dropIndex('article_bigram_title_uindx');
        $table->dropColumn('title_id');
        $table->dropColumn('display_bilemma');
        $table->addUniqueIndex(['article_id', 'bigram_id'], 'article_id_bigram_id_uindx');

        $table = $schema->getTable('article_token');
        $table->dropColumn('display_lemma');
    }
}
