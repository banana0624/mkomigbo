/* project-root/private/db/migrations/002_seed_subjects.sql */

INSERT IGNORE INTO subjects (slug, name, is_public, nav_order) VALUES
('history','History',1,10),
('slavery','Slavery',1,20),
('people','People',1,30),
('persons','Persons',1,40),
('culture','Culture',1,50),
('religion','Religion',1,60),
('spirituality','Spirituality',1,70),
('tradition','Tradition',1,80),
('language1','Language 1',1,90),
('language2','Language 2',1,100),
('struggles','Struggles',1,110),
('biafra','Biafra',1,120),
('nigeria','Nigeria',1,130),
('ipob','IPOB',1,140),
('africa','Africa',1,150),
('uk','UK',1,160),
('europe','Europe',1,170),
('arabs','Arabs',1,180),
('about','About',1,190);
