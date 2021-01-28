/**
* MySQL script for creating VIEWs for AXIS  
*/

-- Returns all article
CREATE OR REPLACE VIEW article as
SELECT id,name,published,pdf,doi,full_text,abstract
FROM _article;

-- Returns all origines combining journals and colloques
CREATE OR REPLACE VIEW origine as
SELECT a.id as article_id,CONCAT_WS('',j.name,c.name) as name,CONCAT_WS('',j.volume,c.location) as first,CONCAT_WS('',j.number,c.type) as second,CONCAT_WS('',j.pages) as third
FROM _origine o
INNER JOIN _journal j
ON o.id = j.id
INNER JOIN _colloque c
ON o.id=c.id
INNER JOIN _article a
ON a.origine_id=o.id;

-- Returns all editors
CREATE OR REPLACE ViEW editor as
SELECT a.id as article_id,e.name as editor_name,e.link as editor_link,
FROM _editor e
INNER JOIN _article_editor ae
ON a.id=ae.editor_id
INNER JOIN _article a
ON ae.article_id = a.id;

CREATE OR REPLACE VIEW reference as
SELECT citation, reference_id,citation_id
FROM _reference;

CREATE OR REPLACE VIEW researcher as
SELECT first_name,last_name,gender,orcid
FROM `_researcher`;

CREATE OR REPLACE VIEW affiliation as
SELECT name
FROM _affiliation;

CREATE OR REPLACE VIEW author as
SELECT a.article_id,a.potision,r.first_name,r.last_name,r.gender,r.orcid
FROM _author a
INNER JOIN _researcher r
ON a.researcher_id=r.id;

CREATE OR REPLACE VIEW author_affiliation as
SELECT f.name,u.author_id
FROM _affiliation f
INNER JOIN _author_affiliation u
ON f.id=u.affiliation_id;
