<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Bank\Entity\Statement;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240207093411 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bank_statement');
        $table->addColumn('status_account', 'integer', ['notnull'=>true,'default' => Statement::STATUS_ACCOUNT_NO, 'comment' => 'Статус учета']);
        $table->addColumn('status_token', 'integer', ['notnull'=>true,'default' => Statement::STATUS_TOKEN_NO, 'comment' => 'Статус товенов']);
        
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bank_statement');
        $table->dropColumn('status_account');
        $table->dropColumn('status_token');
    }
}
