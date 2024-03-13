<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240313121548 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('fin_opu');
        $table->addColumn('margin_retail', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Маржа розничная']);
        $table->addColumn('margin_tp', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Маржа ТП']);        
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('fin_opu');
        $table->dropColumn('margin_retail');
        $table->dropColumn('margin_tp');
    }
}
