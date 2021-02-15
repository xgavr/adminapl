<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Admin\Entity\MailToken;
use Admin\Entity\MailPostToken;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210111195134 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('mail_token');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('lemma', 'string', ['notnull'=>true, 'length' => 64]);        
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default' => MailToken::IS_DICT]);        
        $table->addColumn('flag', 'integer', ['notnull'=>true, 'default' => MailToken::WHITE_LIST]);
        $table->addColumn('frequency', 'integer', ['notnull' => true, 'default' => -1]);
        $table->addColumn('correct', 'string', ['notnull' => false, 'length' => 256]);
        $table->addColumn('idf', 'float', ['notnull' => false]);
        $table->addColumn('gf', 'integer', ['notnull' => true, 'default' => 0]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['lemma'], 'lemma_uindx');
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('mail_post_token');
        $table->addColumn('id', 'bigint', ['autoincrement'=>true]);
        $table->addColumn('post_log_id', 'integer', ['notnull'=>true]);
        $table->addColumn('mail_token_id', 'integer', ['notnull'=>true]);
        $table->addColumn('display_lemma', 'string', ['notnull'=>true, 'length' => 64]);        
        $table->addColumn('mail_part', 'integer', ['notnull'=>true, 'default' => MailPostToken::PART_UNKNOWN]);        
        $table->addColumn('frequency_part', 'integer', ['notnull'=>true, 'default' => 0]);        
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default' => MailToken::IS_DICT]);        
        $table->addColumn('status_take', 'integer', ['notnull' => true, 'default' => MailPostToken::STATUS_TAKE_NEW]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['post_log_id', 'mail_token_id', 'mail_part'], 'mail_token_part_indx');
        $table->addForeignKeyConstraint('mail_token', ['mail_token_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'mail_token_id_mail_token_id_fk');
        $table->addForeignKeyConstraint('post_log', ['post_log_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'm_p_t_m_p_t_id_m_p_t_id_fk');
        $table->addOption('engine' , 'InnoDB'); 
        
        $table = $schema->createTable('mail_token_group');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('name', 'string', ['notnull'=>true, 'length' => 128]);        
        $table->addColumn('lemms', 'string', ['notnull'=>true, 'length' => 512]);        
        $table->addColumn('ids', 'string', ['notnull'=>true, 'length' => 512]);        
        $table->addColumn('post_count', 'integer', ['notnull'=>true, 'default' => 0]);        
        $table->setPrimaryKey(['id']);
        $table->addIndex(['ids'], 'ids_indx');
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->createTable('mail_token_group_token');
        $table->addColumn('id', 'bigint', ['autoincrement'=>true]);
        $table->addColumn('mail_token_group_id', 'integer', ['notnull'=>true]);
        $table->addColumn('mail_token_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('mail_token', ['mail_token_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'mtgroup_mt_mt_id_mt_id_fk');
        $table->addForeignKeyConstraint('mail_token_group', ['mail_token_group_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'mt_group_mt_mt_group_id_mt_group_id_fk');
        $table->addOption('engine' , 'InnoDB'); 
        
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('mail_token_group_token');        
        $schema->dropTable('mail_token_group');        
        $schema->dropTable('mail_post_token');
        $schema->dropTable('mail_token');
    }
}
