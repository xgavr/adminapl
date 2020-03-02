<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200302094352 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('token_group_token');
        $table->addColumn('frequency', 'integer', ['notnull' => true, 'default' => 0]);

        $table = $schema->createTable('token_group_bigram');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('token_group_id', 'integer', ['notnull'=>true]);
        $table->addColumn('bigram_id', 'integer', ['notnull' => true]);
        $table->addColumn('frequency', 'integer', ['notnull' => true, 'default' => 0]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['token_group_id', 'bigram_id'], 'group_bigram_uindx');
        $table->addForeignKeyConstraint('token_group', ['token_group_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'tgb_token_group_id_token_group_id_fk');
        $table->addForeignKeyConstraint('bigram', ['bigram_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'tgb_bigram_id_bigram_id_fk');
        $table->addOption('engine' , 'InnoDB');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('token_group_token');
        $table->dropColumn('frequency');

        $table = $schema->getTable('token_group_bigram');
        $table->removeForeignKey('tgb_token_group_id_token_group_id_fk');
        $table->removeForeignKey('tgb_bigram_id_bigram_id_fk');
        $schema->dropTable('token_group_bigram');
    }
}
