-- ****************** SqlDBM: MySQL ******************;
-- ***************************************************;
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS ArticlesModify;
DROP TABLE IF EXISTS Administrators;
DROP TABLE IF EXISTS Articles;
DROP TABLE IF EXISTS Images;

-- ************************************** Administrators

CREATE TABLE IF NOT EXISTS Administrators
(
    Id       int PRIMARY KEY AUTO_INCREMENT,
    Username varchar(40) NOT NULL UNIQUE,
    Email    varchar(40) NOT NULL UNIQUE,
    Password varchar(64) NOT NULL
);

-- ************************************** Images

CREATE TABLE IF NOT EXISTS Images
(
    FileName varchar(64) PRIMARY KEY,
    Alt text,
    Url  varchar(128) NOT NULL UNIQUE
);

-- ************************************** Articles

CREATE TABLE IF NOT EXISTS Articles
(
 Id                 int PRIMARY KEY AUTO_INCREMENT,
 Title              text NOT NULL UNIQUE,
 ArticleTextContent text NOT NULL,
 Summary            text NOT NULL,
 InsertDate         datetime NOT NULL,
 Image              varchar(64),
 FOREIGN KEY (Image) REFERENCES Images(FileName)
 );


-- ************************************** ArticlesModify

CREATE TABLE IF NOT EXISTS ArticlesModify
(
    IdArticle       int NOT NULL,
    IdAdministrator int NOT NULL,
    ModifyDate      datetime NOT NULL,
    CommentChanges  text,

    PRIMARY KEY (IdArticle, IdAdministrator, ModifyDate),
    FOREIGN KEY (IdArticle) REFERENCES Articles(Id) ON DELETE CASCADE,
    FOREIGN KEY (IdAdministrator) REFERENCES Administrators(Id) ON DELETE CASCADE
);
