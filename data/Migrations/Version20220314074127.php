<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Cash\Entity\CashDoc;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220314074127 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('cash_doc');
        $table->addColumn('status_ex', 'integer', ['notnull' => true, 'default' => CashDoc::STATUS_EX_RECD]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('cash_doc');
        $table->dropColumn('status_ex');
    }
}
