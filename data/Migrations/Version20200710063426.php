<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Company\Entity\Contract;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200710063426 extends AbstractMigration
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
    public function preUp(Schema $schema)
    {
        parent::preUp($schema);
        $this->setForeignKeyChecks(false);
    }

    /**
     * @param Schema $schema
     */
    public function postUp(Schema $schema)
    {
        parent::postUp($schema);
        $this->setForeignKeyChecks(true);
    }

    /**
     * @param Schema $schema
     */
    public function preDown(Schema $schema)
    {
        parent::preDown($schema);
        $this->setForeignKeyChecks(false);
    }

    /**
     * @param Schema $schema
     */
    public function postDown(Schema $schema)
    {
        parent::postDown($schema);
        $this->setForeignKeyChecks(true);
    }
    
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('contract');
        $table->addColumn('company_id', 'integer', ['notnull'=>true, 'default' => 0]);
        $table->addColumn('kind', 'integer', ['notnull' => true, 'default' => Contract::KIND_SUPPLIER]);
        $table->addColumn('pay', 'integer', ['notnull' => true, 'default' => Contract::PAY_CASH]);
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'legal_id_contract_company_id_fk');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('contract');
        $table->removeForeignKey('legal_id_contract_company_id_fk');
        $table->dropColumn('company_id');
        $table->dropColumn('kind');
        $table->dropColumn('pay');

    }
}
