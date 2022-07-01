<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Stock\Entity\VtpGood;
use Stock\Entity\VtGood;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220630153413 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('vtp_good');
        $table->addColumn('take', 'integer', ['notnull' => true, 'default' => VtpGood::TAKE_NO]);
        $table->addIndex(['take'], 'take_indx');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('vtp_good');
        $table->dropIndex('take_indx');
        $table->dropColumn('take');

    }
}
