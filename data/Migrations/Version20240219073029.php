<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Company\Entity\Cost;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240219073029 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('cost');
        $table->addColumn('kind_fin', 'integer', ['notnull'=>true, 'default' => Cost::KIND_FIN_EXP, 'comment' => 'Вид']);

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('cost');
        $table->dropColumn('kind_fin');

    }
}
