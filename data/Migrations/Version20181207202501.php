<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Platforms\MySqlPlatform;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181207202501 extends AbstractMigration
{
    /**
     * @param boolean $enabled
     */
    protected function setForeignKeyChecks($enabled)
    {
        $connection = $this->connection;
        $platform = $connection->getDatabasePlatform();
        if ($platform instanceof MySqlPlatform) {
            $connection->exec(sprintf('SET foreign_key_checks = %s;', (int)$enabled));
        }
    }

    /**
     * @param Schema $schema
     */
    public function preUp(Schema $schema): void
    {
        parent::preUp($schema);
        $this->setForeignKeyChecks(false);
    }

    /**
     * @param Schema $schema
     */
    public function postUp(Schema $schema): void
    {
        parent::postUp($schema);
        $this->setForeignKeyChecks(true);
    }

    /**
     * @param Schema $schema
     */
    public function preDown(Schema $schema): void
    {
        parent::preDown($schema);
        $this->setForeignKeyChecks(false);
    }

    /**
     * @param Schema $schema
     */
    public function postDown(Schema $schema): void
    {
        parent::postDown($schema);
        $this->setForeignKeyChecks(true);
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('producer');
        $table->addUniqueIndex(['name'], 'name_uinx');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('producer');
        $table->dropIndex('name_uinx');
    }
}
