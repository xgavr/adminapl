<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231127045846 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('legal');
        $table->addColumn('okato', 'string', ['notnull' => false, 'length' => 12, 'comment' => 'ОКАТО']);
        $table->addColumn('oktmo', 'string', ['notnull' => false, 'length' => 12, 'comment' => 'ОКТМО']);
        
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('legal');
        $table->dropColumn('okato');
        $table->dropColumn('oktmo');
    }
}
