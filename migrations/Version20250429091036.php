<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250429091036 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE charging_station DROP FOREIGN KEY FK_B4D36FE564D218E
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE location
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_B4D36FE564D218E ON charging_station
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE charging_station CHANGE location_id location_station_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE charging_station ADD CONSTRAINT FK_B4D36FE59895B3EA FOREIGN KEY (location_station_id) REFERENCES location_station (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B4D36FE59895B3EA ON charging_station (location_station_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE charging_station DROP FOREIGN KEY FK_B4D36FE59895B3EA
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_B4D36FE59895B3EA ON charging_station
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE charging_station CHANGE location_station_id location_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE charging_station ADD CONSTRAINT FK_B4D36FE564D218E FOREIGN KEY (location_id) REFERENCES location (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B4D36FE564D218E ON charging_station (location_id)
        SQL);
    }
}
