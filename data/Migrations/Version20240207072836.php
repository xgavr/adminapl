<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240207072836 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('statement_token_token');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('statement_id', 'integer', ['notnull'=>true, 'comment' => 'Строка выписки']);        
        $table->addColumn('statement_token_id', 'integer', ['notnull'=>true, 'comment' => 'Токен']);        
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('bank_statement', ['statement_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'statement_token_token_statement_id_bank_statement_id_fk');
        $table->addForeignKeyConstraint('statement_token', ['statement_token_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'statement_token_token_statement_token_id_statement_token_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('statement_token_token');
    }
}
