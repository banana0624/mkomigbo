/* project-root/private/db/sample_data.sql */

USE mkomigbo;

INSERT INTO subjects (id, name, slug, meta_description, meta_keywords) VALUES
(1, 'History', 'history', 'Pages related to history.', 'history, past, heritage, records'),
(2, 'Slavery', 'slavery', 'Pages related to slavery.', 'slavery, trade, freedom, heritage'),
(3, 'Culture', 'culture', 'Pages related to culture.', 'culture, arts, tradition, heritage');

INSERT INTO pages (id, subject_id, title, slug, content) VALUES
(1, 1, 'Ancient Civilizations', 'ancient-civilizations', 'Content about ancient civilizations.'),
(2, 2, 'Transatlantic Slave Trade', 'slave-trade', 'Content about the transatlantic slave trade.'),
(3, 3, 'African Music Traditions', 'african-music', 'Content about African music.');

INSERT INTO users (id, username, email, password_hash, role) VALUES
(1, 'admin', 'admin@example.com', 'hashedpassword123', 'admin'),
(2, 'editor1', 'editor1@example.com', 'hashedpassword456', 'editor'),
(3, 'viewer1', 'viewer1@example.com', 'hashedpassword789', 'viewer');

INSERT INTO contributors (id, name, bio) VALUES
(1, 'John Doe', 'Historian and researcher.'),
(2, 'Jane Smith', 'Cultural anthropologist.'),
(3, 'Ali Musa', 'Writer on African heritage.');

INSERT INTO platforms (id, name, url) VALUES
(1, 'YouTube', 'https://www.youtube.com'),
(2, 'Medium', 'https://medium.com'),
(3, 'WordPress', 'https://wordpress.com');
