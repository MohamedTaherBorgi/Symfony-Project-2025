<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260106185049 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY `FK_52EA1F094584665A`');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY `FK_52EA1F09E238517C`');
        $this->addSql('ALTER TABLE order_item ADD product_name VARCHAR(255) DEFAULT NULL, ADD product_description LONGTEXT DEFAULT NULL, ADD product_image VARCHAR(255) DEFAULT NULL, CHANGE product_id product_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F094584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F09E238517C FOREIGN KEY (order_ref_id) REFERENCES `order` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F09E238517C');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F094584665A');
        $this->addSql('ALTER TABLE order_item DROP product_name, DROP product_description, DROP product_image, CHANGE product_id product_id INT NOT NULL');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT `FK_52EA1F09E238517C` FOREIGN KEY (order_ref_id) REFERENCES `order` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT `FK_52EA1F094584665A` FOREIGN KEY (product_id) REFERENCES product (id)');
    }
}
