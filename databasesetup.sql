-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema fullstack
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema fullstack
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `fullstack` ;
USE `fullstack` ;

-- -----------------------------------------------------
-- Table `fullstack`.`BestellingProducten`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fullstack`.`BestellingProducten` (
  `idBestellingen` INT(11) NOT NULL,
  `idVoorraad` INT(11) NOT NULL,
  `Prijs` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`idBestellingen`, `idVoorraad`),
  INDEX `idx_BestellingProducten_idVoorraad` (`idVoorraad` ASC) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fullstack`.`Voorraad`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fullstack`.`Voorraad` (
  `idVoorraad` INT(11) NOT NULL AUTO_INCREMENT,
  `Product` VARCHAR(45) NOT NULL,
  `Kleur` VARCHAR(45) NOT NULL,
  `Gewicht` FLOAT NOT NULL,
  `Maat` VARCHAR(45) NOT NULL,
  `Looiing` VARCHAR(45) NOT NULL,
  `Status` VARCHAR(45) NOT NULL,
  `Prijs` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`idVoorraad`),
  UNIQUE INDEX `idVoorraad_UNIQUE` (`idVoorraad` ASC) VISIBLE)
ENGINE = InnoDB
AUTO_INCREMENT = 19;


-- -----------------------------------------------------
-- Table `fullstack`.`Bestellingen`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fullstack`.`Bestellingen` (
  `idBestellingen` INT(11) NOT NULL AUTO_INCREMENT,
  `Datum` DATE NOT NULL,
  `Klant` VARCHAR(45) NOT NULL,
  `Totaal` DECIMAL(10,2) NULL DEFAULT NULL,
  `Status` VARCHAR(45) NULL DEFAULT NULL,
  `idVoorraad` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`idBestellingen`),
  UNIQUE INDEX `idBestellingen_UNIQUE` (`idBestellingen` ASC) VISIBLE,
  INDEX `idVoorraad_idx` (`idVoorraad` ASC) VISIBLE,
  CONSTRAINT `fk_bestelling_voorraad`
    FOREIGN KEY (`idVoorraad`)
    REFERENCES `fullstack`.`Voorraad` (`idVoorraad`))
ENGINE = InnoDB
AUTO_INCREMENT = 13;


-- -----------------------------------------------------
-- Table `fullstack`.`Users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fullstack`.`Users` (
  `ID` INT(11) NOT NULL AUTO_INCREMENT,
  `Username` VARCHAR(16) NOT NULL,
  `Naam` VARCHAR(50) NOT NULL,
  `Email` VARCHAR(50) NOT NULL,
  `WW` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE INDEX `ID_UNIQUE` (`ID` ASC) VISIBLE,
  UNIQUE INDEX `Username_UNIQUE` (`Username` ASC) VISIBLE)
ENGINE = InnoDB
AUTO_INCREMENT = 31;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
