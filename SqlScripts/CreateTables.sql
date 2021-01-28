/**
* MySQL script for creating TABLEs for AXIS  
*/


-- Basic data about each Article
CREATE TABLE IF NOT EXISTS `_articles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` LONGTEXT NOT NULL,
  `published` int unsigned,
  `language` varchar(20),
  `pdf` varchar(300),
  `doi` varchar(100),
  `abstract` MEDIUMTEXT,
  `score` int NOT NULL,
  -- Each Article has an Origine
  `origine_id` int REFERENCES origines(id),
  PRIMARY KEY (`id`)
);



-- An article may reference another article or oppositly be cited by another article
CREATE TABLE IF NOT EXISTS `_references` (
  `citation` varchar(300),
  `reference_id` int NOT NULL REFERENCES articles(id),
  `citation_id` int NOT NULL REFERENCES articles(id),
  PRIMARY KEY (`reference_id`,`citation_id`)
);

-- Data on each Editor
CREATE TABLE IF NOT EXISTS `_editors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(200) UNIQUE NOT NULL,
  `link` varchar(200) UNIQUE,
  PRIMARY KEY (`id`)
);

-- Each Article can have an Editor, an Editor can have multiple Articles
CREATE TABLE IF NOT EXISTS `_articles_editors` (
  `article_id` int NOT NULL REFERENCES articles(id),
  `editor_id` int NOT NULL REFERENCES editors(id),
  PRIMARY KEY (`article_id`,`editor_id`)
);

-- Data on each Researcher
CREATE TABLE IF NOT EXISTS `_researchers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `gender` varchar(50),
  `orcid` int UNIQUE,
  PRIMARY KEY (`id`)
);

-- Each Researcher is the Author of at least one Article and has a position in the credited section
CREATE TABLE IF NOT EXISTS `_authors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `article_id` int NOT NULL REFERENCES articles(id),
  `researcher_id` int NOT NULL REFERENCES researchers(id),
  `position` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE(`article_id`,`position`),
  UNIQUE(`article_id`,`researcher_id`)
);

-- Data on the Affiliaton of Authors
CREATE TABLE IF NOT EXISTS `_affiliations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` TEXT NOT NULL,
  PRIMARY KEY (`id`)
);

-- Each Author has an Affiliation
CREATE TABLE IF NOT EXISTS `_authors_affiliations`(
  `author_id` int NOT NULL REFERENCES authors(id),
  `affiliation_id` int NOT NULL REFERENCES affiliations(id),
  PRIMARY KEY(`author_id`,`affiliation_id`)
);

-- Data on Categories
CREATE TABLE IF NOT EXISTS `_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL UNIQUE,
  PRIMARY KEY (`id`)
);

-- Each Article can have Categories associated with it
CREATE TABLE IF NOT EXISTS `_articles_categories` (
  `article_id` int NOT NULL REFERENCES articles(id),
  `category_id` int NOT NULL REFERENCES categories(id),
  PRIMARY KEY (`article_id`,`category_id`)
);

-- Data on Origines, each Article has an Orgine
CREATE TABLE IF NOT EXISTS `_origines` (
  `id` int NOT NULL AUTO_INCREMENT,
  `origine_type` varchar(50),
  PRIMARY KEY (`id`)
);

-- Data on Journals which are a type of Origine
CREATE TABLE IF NOT EXISTS `_journals` (
  `id` int NOT NULL REFERENCES origines(id),
  `name` varchar(500) NOT NULL,
  `volume` varchar(50),
  `number` varchar(50),
  `pages` varchar(50),
  PRIMARY KEY (`id`)
);

-- Data on Colloque which are a type of Origine
CREATE TABLE IF NOT EXISTS `_colloques` (
  `id` int NOT NULL REFERENCES origines(id),
  `name` varchar(500) NOT NULL,
  `acronym` varchar(20) NOT NULL,
  `url` varchar(100) NOT NULL,
  `location` varchar(200),
  -- each Colloque has a type : Workshop, Conference, Symposium
  `type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)

);
