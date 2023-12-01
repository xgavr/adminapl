<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231201133521 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('make');
        $table->addColumn('name_ru', 'string', ['notnull' => false, 'length' => 128, 'comment' => 'Наименование Рус']);

        $table = $schema->getTable('model');
        $table->addColumn('name_ru', 'string', ['notnull' => false, 'length' => 128, 'comment' => 'Наименование Рус']);

        $table = $schema->createTable('search_title');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('title', 'string', ['notnull'=>true, 'length' => 512, 'comment' => 'Поисковый запрос']);
        $table->addColumn('title_md5', 'string', ['notnull'=>true, 'length' => 128, 'comment' => 'Поисковый запрос MD5']);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['title_md5'], 'title_md4_unindx');
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->createTable('search_token');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('search_title_id', 'integer', ['notnull'=>true]);
        $table->addColumn('lemma', 'string', ['notnull'=>true, 'length' => 64]);        
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('search_title', ['search_title_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'search_token_title_id_search_title_id_fk');
        $table->addForeignKeyConstraint('token', ['lemma'], ['lemma'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'search_token_lemma_token_lemma_fk');
        $table->addOption('engine' , 'InnoDB');         
        
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('make');
        $table->dropColumn('name_ru');

        $table = $schema->getTable('model');
        $table->dropColumn('name_ru');
        
        $schema->dropTable('search_token');
        
        $schema->dropTable('search_title');
    }
}
