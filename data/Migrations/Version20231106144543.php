<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Stock\Entity\Revise;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231106144543 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('revise');
        $table->addColumn('status_account', 'integer', ['notnull' => true, 'default' => Revise::STATUS_ACCOUNT_NO]);
        $table->addIndex(['status_account'], 'status_account_indx');

    }

    public function down(Schema $schema) : void
    {
        $table = $schema->getTable('revise');
        $table->dropColumn('status_account');
        // this down() migration is auto-generated, please modify it to your needs

    }
}
