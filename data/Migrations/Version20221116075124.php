<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Stock\Entity\Vtp;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221116075124 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('vtp');
        $table->addColumn('vtp_type', 'integer', ['notnull' => true, 'default' => Vtp::TYPE_NO_NEED]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('vtp');
        $table->dropColumn('vtp_type');
    }
}
