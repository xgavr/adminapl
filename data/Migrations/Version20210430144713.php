<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\UnknownProducer;
use Application\Entity\Producer;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210430144713 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('unknown_producer');
        $table->addColumn('status', 'integer', ['notnull' => false, 'default' => UnknownProducer::STATUS_ACTIVE]);

        $table = $schema->getTable('producer');
        $table->addColumn('status', 'integer', ['notnull' => false, 'default' => Producer::STATUS_ACTIVE]);

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('unknown_producer');
        $table->dropColumn('status');
        
        $table = $schema->getTable('producer');
        $table->dropColumn('status');
        
    }
}
