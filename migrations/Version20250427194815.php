<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250427194815 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE booking (id INT AUTO_INCREMENT NOT NULL, create_at DATETIME NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, total_amount NUMERIC(6, 2) NOT NULL, status VARCHAR(255) NOT NULL, payment_type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE charging_station (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, location_id INT NOT NULL, name_station VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, power NUMERIC(5, 2) NOT NULL, price_per_hour NUMERIC(6, 2) NOT NULL, picture VARCHAR(255) DEFAULT NULL, create_at DATETIME NOT NULL, is_available TINYINT(1) NOT NULL, INDEX IDX_B4D36FE5A76ED395 (user_id), INDEX IDX_B4D36FE564D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE location_station (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, postale_code VARCHAR(10) NOT NULL, city VARCHAR(255) NOT NULL, latitude DOUBLE PRECISION DEFAULT NULL, longitude DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE timeslot (id INT AUTO_INCREMENT NOT NULL, charging_station_id INT NOT NULL, day_of_week VARCHAR(255) NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, INDEX IDX_3BE452F734D723C9 (charging_station_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, name VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, postale_code VARCHAR(10) NOT NULL, city VARCHAR(255) NOT NULL, phone_number VARCHAR(20) DEFAULT NULL, picture VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, is_valid TINYINT(1) NOT NULL, code_check INT DEFAULT NULL, verification_code_expires_at DATETIME DEFAULT NULL, owns_station TINYINT(1) DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE charging_station ADD CONSTRAINT FK_B4D36FE5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE charging_station ADD CONSTRAINT FK_B4D36FE564D218E FOREIGN KEY (location_id) REFERENCES location (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE timeslot ADD CONSTRAINT FK_3BE452F734D723C9 FOREIGN KEY (charging_station_id) REFERENCES charging_station (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE charging_station DROP FOREIGN KEY FK_B4D36FE5A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE charging_station DROP FOREIGN KEY FK_B4D36FE564D218E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE timeslot DROP FOREIGN KEY FK_3BE452F734D723C9
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE booking
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE charging_station
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE location
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE location_station
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE timeslot
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
    }
}
