<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Bank\Entity\Statement;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240416134248 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bank_statement');
        $table->addColumn('status', 'string', ['notnull' => true, 'default' => Statement::STATUS_ACTIVE, 'comment' => 'Статус']);
    }   

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bank_statement');
        $table->dropColumn('status');
    }
}
