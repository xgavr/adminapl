<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Stock\Entity\Ot;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240116164428 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('ot');
        $table->addColumn('status_account', 'integer', ['notnull' => true, 'default' => Ot::STATUS_ACCOUNT_NO, 'comment' => 'Учтено']);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('ot');
        $table->dropColumn('status_account');
    }
}
