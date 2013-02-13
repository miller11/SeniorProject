USE `db_rhmiller` ;
DROP TABLE IF EXISTS `db_rhmiller`.`COMMENT`;
DROP TABLE IF EXISTS `db_rhmiller`.`RATING`;
DROP TABLE IF EXISTS `db_rhmiller`.`RESEARCH_PUBLICATION`;
DROP TABLE IF EXISTS `db_rhmiller`.`LETTER_OF_REC`;
DROP TABLE IF EXISTS `db_rhmiller`.`APPLICATION`;
DROP TABLE IF EXISTS `db_rhmiller`.`JOB_LISTING`;
DROP TABLE IF EXISTS `db_rhmiller`.`UI_PERSON_LOOKUP`;
DROP TABLE IF EXISTS `db_rhmiller`.`UI_PERSON`;
DROP TABLE IF EXISTS `db_rhmiller`.`NON_UI_PERSON_LOOKUP`;
DROP TABLE IF EXISTS `db_rhmiller`.`NON_UI_PERSON`;
DROP TABLE IF EXISTS `db_rhmiller`.`PERMISSIONS`;

-- ----------------------------------------------------- 
-- Table `db_rhmiller`.`PERMISSIONS` 
-- ----------------------------------------------------- 
CREATE  TABLE IF NOT EXISTS `db_rhmiller`.`PERMISSIONS` (  
`PERMISSION` VARCHAR(1) NOT NULL ,
`DESCRIPTION` VARCHAR(25) NOT NULL,  
PRIMARY KEY (`PERMISSION`) ) 
ENGINE = InnoDB; 
-- ----------------------------------------------------- 
-- Table `db_rhmiller`.`UI_PERSON` 
-- ----------------------------------------------------- 
CREATE  TABLE IF NOT EXISTS `db_rhmiller`.`UI_PERSON` ( 
`HAWK_ID` VARCHAR(25) NOT NULL ,  
`ID` INT NOT NULL AUTO_INCREMENT ,
`NONCE` VARCHAR(45) NOT NULL ,
`NAME` VARCHAR(50) NULL ,  
PRIMARY KEY (`ID`) )
ENGINE = InnoDB;
-- -----------------------------------------------------
-- Table `db_rhmiller`.`NON_UI_PERSON`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `db_rhmiller`.`NON_UI_PERSON` (  
`ID` INT NOT NULL AUTO_INCREMENT ,  
`EMAIL` VARCHAR(60) NULL ,  
`PASSWORD` VARCHAR(512) NOT NULL ,
`RESET_PASS` BINARY  NULL ,  
`IP_ADDRESS` INT NOT NULL ,  
`NONCE` VARCHAR(45) NOT NULL ,  
`SALT` VARCHAR(9) NOT NULL ,  
PRIMARY KEY (`ID`) ,
INDEX `ak_non_ui_person_email` (`EMAIL` ASC) )
ENGINE = InnoDB;
-- -----------------------------------------------------
-- Table `db_rhmiller`.`JOB_LISTING`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `db_rhmiller`.`JOB_LISTING` (  
`LISTING_ID` INT NOT NULL AUTO_INCREMENT ,  
`DATE_POSTED` TIMESTAMP NOT NULL ,  
`JOB_DESCRIPTION` VARCHAR(300) NOT NULL ,  
PRIMARY KEY (`LISTING_ID`) )
ENGINE = InnoDB;
-- -----------------------------------------------------
-- Table `db_rhmiller`.`APPLICATION`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `db_rhmiller`.`APPLICATION` ( 
`APP_ID` INT NOT NULL AUTO_INCREMENT ,  
`RESEARCH_STATEMENT` BLOB NULL ,  
`PHD_DATE` TIMESTAMP NULL ,  
`WEBSITE` VARCHAR(250) NULL ,  
`CV` BLOB NULL ,  
`COVER_LETTER` BLOB NULL ,  
`SUBMISSION_DATE` TIMESTAMP NULL  ,  
`UPLOAD_DATE` TIMESTAMP NULL ,  
`FIRST_NAME` VARCHAR(45) NOT NULL ,  
`LAST_NAME` VARCHAR(45) NOT NULL ,  
`GENDER` VARCHAR(1) NULL ,  
`JOB_LISTING_LISTING_ID` INT NOT NULL ,  
`NON_UI_PERSON_ID` INT NOT NULL ,  
PRIMARY KEY (`APP_ID`, `JOB_LISTING_LISTING_ID`, `NON_UI_PERSON_ID`) ,  
INDEX `fk_application_job_listing1_idx` (`JOB_LISTING_LISTING_ID` ASC) ,  
INDEX `fk_application_non_UI_Person1_idx` (`NON_UI_PERSON_ID` ASC) ,  
CONSTRAINT `fk_application_job_listing1`    
FOREIGN KEY (`JOB_LISTING_LISTING_ID` )    
REFERENCES `db_rhmiller`.`JOB_LISTING` (`LISTING_ID` )    
ON DELETE NO ACTION    
ON UPDATE NO ACTION,  
CONSTRAINT `fk_application_non_UI_Person1`    
FOREIGN KEY (`NON_UI_PERSON_ID` )    
REFERENCES `db_rhmiller`.`NON_UI_PERSON` (`ID` )    
ON DELETE NO ACTION    
ON UPDATE NO ACTION) 
ENGINE = InnoDB;
-- -----------------------------------------------------
-- Table `db_rhmiller`.`LETTER_OF_REC`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `db_rhmiller`.`LETTER_OF_REC` (  
`LETTER_ID` INT NOT NULL AUTO_INCREMENT ,  
`LETTER` BLOB NULL ,  
`UPLOAD_DATE` TIMESTAMP NULL ,  
`NON_UI_PERSON_ID` INT NOT NULL ,  
`APPLICATION_APP_ID` INT NOT NULL ,
`CURRENT` BINARY NULL ,  
PRIMARY KEY (`LETTER_ID`, `NON_UI_PERSON_ID`, `APPLICATION_APP_ID`) ,  
INDEX `fk_letter_of_rec_non_UI_Person1_idx` (`NON_UI_PERSON_ID` ASC) ,  
INDEX `fk_letter_of_rec_application1_idx` (`APPLICATION_APP_ID` ASC) ,
INDEX `ak_letter_of_rec_current` (`CURRENT` ASC) ,  
CONSTRAINT `fk_letter_of_rec_non_UI_Person1`    
FOREIGN KEY (`NON_UI_PERSON_ID` )    
REFERENCES `db_rhmiller`.`NON_UI_PERSON` (`ID` )    
ON DELETE NO ACTION    
ON UPDATE NO ACTION,  
CONSTRAINT `fk_letter_of_rec_application1`    
FOREIGN KEY (`APPLICATION_APP_ID` )    
REFERENCES `db_rhmiller`.`APPLICATION` (`APP_ID` )    
ON DELETE NO ACTION    
ON UPDATE NO ACTION)
ENGINE = InnoDB;
-- -----------------------------------------------------
-- Table `db_rhmiller`.`RATING`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `db_rhmiller`.`RATING` (  
`RATING` INT NOT NULL ,  
`RATING_DATE` TIMESTAMP NULL ,  
`RATING_ID` INT NOT NULL AUTO_INCREMENT ,  
`UI_PERSON_ID` INT NOT NULL ,  
`APPLICATION_APP_ID` INT NOT NULL ,  
PRIMARY KEY (`RATING_ID`, `UI_PERSON_ID`, `APPLICATION_APP_ID`) ,  
INDEX `fk_rating_UI_Person1_idx` (`UI_PERSON_ID` ASC) ,  
INDEX `fk_rating_application1_idx` (`APPLICATION_APP_ID` ASC) ,  
CONSTRAINT `fk_rating_UI_Person1`    
FOREIGN KEY (`UI_PERSON_ID` )    
REFERENCES `db_rhmiller`.`UI_PERSON` (`ID` )    
ON DELETE NO ACTION    
ON UPDATE NO ACTION,  
CONSTRAINT `fk_rating_application1`    
FOREIGN KEY (`APPLICATION_APP_ID` )    
REFERENCES `db_rhmiller`.`APPLICATION` (`APP_ID` )    
ON DELETE NO ACTION    
ON UPDATE NO ACTION) 
ENGINE = InnoDB; 
-- -----------------------------------------------------
-- Table `db_rhmiller`.`COMMENT`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `db_rhmiller`.`COMMENT` (  
`COMMENT_ID` INT NOT NULL AUTO_INCREMENT ,  
`TEXT` VARCHAR(4000) NULL ,  
`COMMENT_DATE` TIMESTAMP NULL ,  
`RATING_RATING_ID` INT NOT NULL ,  
PRIMARY KEY (`COMMENT_ID`, `RATING_RATING_ID`) ,  
INDEX `fk_comment_rating1_idx` (`RATING_RATING_ID` ASC) ,  
CONSTRAINT `fk_comment_rating1`    
FOREIGN KEY (`RATING_RATING_ID` )   
REFERENCES `db_rhmiller`.`RATING` (`RATING_ID` )    
ON DELETE NO ACTION    
ON UPDATE NO ACTION)
ENGINE = InnoDB;
-- -----------------------------------------------------
-- Table `db_rhmiller`.`UI_PERSON_LOOKUP`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `db_rhmiller`.`UI_PERSON_LOOKUP` (  
`PERMISSIONS_PERMISSION` VARCHAR(1) NOT NULL ,  
`UI_PERSON_ID` INT NOT NULL ,  
PRIMARY KEY (`PERMISSIONS_PERMISSION`, `UI_PERSON_ID`) ,  
INDEX `fk_UI_Person_Lookup_UI_Person1_idx` (`UI_PERSON_ID` ASC) ,  
CONSTRAINT `fk_UI_Person_Lookup_permissions1`    
FOREIGN KEY (`PERMISSIONS_PERMISSION` )    
REFERENCES `db_rhmiller`.`PERMISSIONS` (`PERMISSION` )    
ON DELETE NO ACTION    
ON UPDATE NO ACTION,  
CONSTRAINT `fk_UI_Person_Lookup_UI_Person1`    
FOREIGN KEY (`UI_PERSON_ID` )    
REFERENCES `db_rhmiller`.`UI_PERSON` (`ID` )    
ON DELETE NO ACTION    
ON UPDATE NO ACTION)
ENGINE = InnoDB;
-- -----------------------------------------------------
-- Table `db_rhmiller`.`NON_UI_PERSON_LOOKUP`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `db_rhmiller`.`NON_UI_PERSON_LOOKUP` (  
`PERMISSIONS_PERMISSION` VARCHAR(1) NOT NULL ,  
`NON_UI_PERSON_ID` INT NOT NULL ,  
PRIMARY KEY (`PERMISSIONS_PERMISSION`, `NON_UI_PERSON_ID`) ,  
INDEX `fk_Non_UI_Person_Lookup_permissions1_idx` (`PERMISSIONS_PERMISSION` ASC) ,  
INDEX `fk_Non_UI_Person_Lookup_non_UI_Person1_idx` (`NON_UI_PERSON_ID` ASC) ,  
CONSTRAINT `fk_Non_UI_Person_Lookup_permissions1`    
FOREIGN KEY (`PERMISSIONS_PERMISSION` )    
REFERENCES `db_rhmiller`.`PERMISSIONS` (`PERMISSION` )    
ON DELETE NO ACTION    
ON UPDATE NO ACTION,  
CONSTRAINT `fk_Non_UI_Person_Lookup_non_UI_Person1`    
FOREIGN KEY (`NON_UI_PERSON_ID` )    
REFERENCES `db_rhmiller`.`NON_UI_PERSON` (`ID` )    
ON DELETE NO ACTION    
ON UPDATE NO ACTION)
ENGINE = InnoDB;
-- ------------------------------------------------------
-- Table `db_rhmiller`.`RESEARCH_PUBLICATION`
-- ------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_rhmiller`.`RESEARCH_PUBLICATION` (
`PUB_ID` INT NOT NULL AUTO_INCREMENT ,
`RESEARCH_PUB` BLOB NULL ,
`APPLICATION_APP_ID` INT NOT NULL ,
PRIMARY KEY (`PUB_ID`, `APPLICATION_APP_ID`) ,
INDEX `fk_RESEARCH_PUBLICATION_APPLICATION1_idx` (`APPLICATION_APP_ID` ASC) ,
CONSTRAINT `fk_RESEARCH_PUBLICATION_APPLICATION1`
FOREIGN KEY (`APPLICATION_APP_ID` )
REFERENCES `db_rhmiller`.`APPLICATION` (`APP_ID` )
ON DELETE NO ACTION
ON UPDATE NO ACTION)
ENGINE = InnoDB;
