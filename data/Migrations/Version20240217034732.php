<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Bank\Entity\Statement;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240217034732 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bank_statement');
        $table->addColumn('amount_service', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Сумма услуг']);        
        $table->addColumn('kind', 'integer', ['notnull'=>true, 'default' => Statement::KIND_UNKNOWN, 'comment' => 'Вид операции']);        

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bank_statement');
        $table->dropColumn('amount_service');
        $table->dropColumn('kind');
    }
}
