-- project-root/private/db/seed.sql --
USE mkomigbo;

-- Clear existing data safely
DELETE FROM subjects;
ALTER TABLE subjects AUTO_INCREMENT = 1;

-- Insert 19 subjects
INSERT INTO subjects (id, name, slug, meta_description, meta_keywords) VALUES
(1, 'History', 'history', 'Pages related to history.', 'history, past, heritage, records'),
(2, 'Slavery', 'slavery', 'Pages related to slavery.', 'slavery, trade, bondage, history'),
(3, 'People', 'people', 'Pages related to people.', 'people, community, individuals'),
(4, 'Persons', 'persons', 'Pages related to persons.', 'persons, individuals, biographies'),
(5, 'Culture', 'culture', 'Pages related to culture.', 'culture, lifestyle, arts, heritage'),
(6, 'Religion', 'religion', 'Pages related to religion.', 'religion, faith, worship, belief'),
(7, 'Spirituality', 'spirituality', 'Pages related to spirituality.', 'spirituality, meditation, faith, soul'),
(8, 'Tradition', 'tradition', 'Pages related to tradition.', 'tradition, customs, practices, heritage'),
(9, 'Language 1', 'language1', 'Pages related to first language.', 'language, communication, dialect'),
(10, 'Language 2', 'language2', 'Pages related to second language.', 'language, communication, dialect'),
(11, 'Struggles', 'struggles', 'Pages related to struggles.', 'struggles, resistance, survival'),
(12, 'Biafra', 'biafra', 'Pages related to Biafra.', 'biafra, war, independence, nigeria'),
(13, 'Nigeria', 'nigeria', 'Pages related to Nigeria.', 'nigeria, nation, politics, history'),
(14, 'IPOB', 'ipob', 'Pages related to IPOB.', 'ipob, biafra, movement, nigeria'),
(15, 'Africa', 'africa', 'Pages related to Africa.', 'africa, continent, heritage, nations'),
(16, 'UK', 'uk', 'Pages related to the United Kingdom.', 'uk, britain, england, london'),
(17, 'Europe', 'europe', 'Pages related to Europe.', 'europe, continent, nations, history'),
(18, 'Arabs', 'arabs', 'Pages related to Arabs.', 'arabs, middle east, culture, history'),
(19, 'About', 'about', 'About this website.', 'about, information, project, overview');
