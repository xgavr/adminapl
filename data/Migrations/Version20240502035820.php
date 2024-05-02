<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Platforms\MySqlPlatform;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240502035820 extends AbstractMigration
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
//        $table = $schema->getTable('personal_accrual');
//        $table->addColumn('position_id', 'integer', ['notnull' => false, 'default' => 0, 'comment' => 'Должность']);
//        $table->addForeignKeyConstraint('position', ['position_id'], ['id'], 
//                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'personal_accrual_position_id_position_id_fk');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
//        $table = $schema->getTable('personal_accrual');
//        $table->removeForeignKey('personal_accrual_position_id_position_id_fk');
//        $table->dropColumn('position_id');
    }
}
