CREATE SCHEMA IF NOT EXISTS `maturita` ;
USE `maturita` ;


CREATE TABLE IF NOT EXISTS `maturita`.`users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `username_UNIQUE` (`username` ASC) VISIBLE)
ENGINE = InnoDB;


CREATE TABLE IF NOT EXISTS `maturita`.`study_set` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `title` VARCHAR(255) NULL,
  `description` TEXT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_user_idx` (`user_id` ASC) VISIBLE,
  CONSTRAINT `fk_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `maturita`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


CREATE TABLE IF NOT EXISTS `maturita`.`cards` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `study_set_id` INT NOT NULL,
  `front_text` TEXT NULL,
  `back_text` TEXT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_study_set_idx` (`study_set_id` ASC) VISIBLE,
  CONSTRAINT `fk_study_set`
    FOREIGN KEY (`study_set_id`)
    REFERENCES `maturita`.`study_set` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;
