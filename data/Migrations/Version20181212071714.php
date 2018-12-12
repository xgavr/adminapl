<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181212071714 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('unknown_producer_intersect');
        $table->addColumn('code', 'string', ['length' => 24, 'notnull' => true]);
        $table->addColumn('unknown_producer', 'integer', ['notnull' => true]);
        $table->addColumn('unknown_producer_intersect', 'integer', ['notnull' => true]);
        $table->addIndex(['unknown_producer'], 'unknownProducer_indx');
        $table->addIndex(['unknown_producer_intersect'], 'unknownProducerIntersect_indx');
        $table->addOption('engine' , 'InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('unknown_producer_intersect');
    }
}
