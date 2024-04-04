<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Zp\Entity\PersonalAccrual;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240402074050 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('personal_accrual');
        $table->addColumn('taxed_ndfl', 'integer', ['notnull' => true, 'default' => PersonalAccrual::TAXED_NDFL_YES, 'comment' => 'Облагается НДФЛ']);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('personal_accrual');
        $table->dropColumn('taxed_ndfl');
    }
}
