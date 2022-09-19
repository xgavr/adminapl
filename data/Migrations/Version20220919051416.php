<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Stock\Entity\Ptu;
use Stock\Entity\Vtp;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220919051416 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('ptu');
        $table->addColumn('status_account', 'integer', ['notnull' => true, 'default' => Ptu::STATUS_ACCOUNT_NO]);

        $table = $schema->getTable('vtp');
        $table->addColumn('status_account', 'integer', ['notnull' => true, 'default' => Vtp::STATUS_ACCOUNT_NO]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('ptu');
        $table->dropColumn('status_account');

        $table = $schema->getTable('vtu');
        $table->dropColumn('status_account');
    }
}
