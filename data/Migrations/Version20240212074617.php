<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Zp\Entity\Position;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240212074617 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('position');
        $table->addColumn('kind', 'integer', ['notnull' => true, 'default' => Position::KIND_ADM, 'comment' => 'Тип позиции']);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('position');
        $table->dropColumn('kind');
    }
}
