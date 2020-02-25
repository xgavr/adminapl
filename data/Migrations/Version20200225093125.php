<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200225093125 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('title_token');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('group_id', 'integer', ['notnull'=>true]);
        $table->addColumn('title_md5', 'string', ['notnull' => true, 'length' => 128]);
        $table->addColumn('token_id', 'integer', ['notnull' => true]);
        $table->addColumn('display_lemma', 'string', ['notnull' => false, 'length' => 256]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['group_id', 'title_md5', 'token_id'], 'group_title_token_uindx');
        $table->addForeignKeyConstraint('token_group', ['group_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'title_token_group_id_token_group_id_fk');
        $table->addForeignKeyConstraint('token', ['token_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'title_token_token_id_token_id_fk');
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('title_bigram');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('group_id', 'integer', ['notnull'=>true]);
        $table->addColumn('title_md5', 'string', ['notnull' => true, 'length' => 128]);
        $table->addColumn('bigram_id', 'integer', ['notnull' => true]);
        $table->addColumn('display_bilemma', 'string', ['notnull' => false, 'length' => 256]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['group_id', 'title_md5', 'bigram_id'], 'group_title_bigram_uindx');
        $table->addForeignKeyConstraint('token_group', ['group_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'title_bigram_group_id_token_group_id_fk');
        $table->addForeignKeyConstraint('bigram', ['bigram_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'title_bigram_bigram_id_bigram_id_fk');
        $table->addOption('engine' , 'InnoDB');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('title_token');
        $table->removeForeignKey('title_token_group_id_token_group_id_fk');
        $table->removeForeignKey('title_token_token_id_token_id_fk');
        $schema->dropTable('title_token');

        $table = $schema->getTable('title_bigram');
        $table->removeForeignKey('title_bigram_group_id_token_group_id_fk');
        $table->removeForeignKey('title_bigram_bigram_id_bigram_id_fk');
        $schema->dropTable('title_bigram');
    }
}
