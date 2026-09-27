-- Back up your existing database first. Select that database in phpMyAdmin.
-- This migration preserves existing rows. Run once; successful repeats are skipped.
CREATE TABLE IF NOT EXISTS schema_versions (version INT PRIMARY KEY, applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS carousel (
 carousel_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 title VARCHAR(255) NOT NULL, description TEXT, image_url VARCHAR(700) NOT NULL,
 button_text VARCHAR(80) DEFAULT 'Explore Now', button_url VARCHAR(700),
 sort_order INT NOT NULL DEFAULT 0, status ENUM('active','inactive') DEFAULT 'active',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS upcoming_releases (
 release_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, category_id INT UNSIGNED NOT NULL,
 title VARCHAR(255) NOT NULL, release_type ENUM('anime','game','movie','tv','comic','manga','music','merchandise','other') NOT NULL,
 description TEXT, release_date DATE NOT NULL, image_url VARCHAR(700), resource_url VARCHAR(700),
 status ENUM('draft','published') NOT NULL DEFAULT 'draft', created_by INT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(category_id) REFERENCES categories(category_id), FOREIGN KEY(created_by) REFERENCES users(user_id) ON DELETE SET NULL,
 INDEX(release_date,status)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS login_attempts (
 attempt_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 identity_hash CHAR(64) NOT NULL, attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX(identity_hash,attempted_at)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS merchandise_images (
 image_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, item_id INT UNSIGNED NOT NULL,
 image_url VARCHAR(700) NOT NULL, FOREIGN KEY(item_id) REFERENCES merchandise(item_id) ON DELETE CASCADE
) ENGINE=InnoDB;
DELIMITER $$
DROP PROCEDURE IF EXISTS fh_upgrade_2$$
CREATE PROCEDURE fh_upgrade_2()
BEGIN
 IF NOT EXISTS (SELECT 1 FROM schema_versions WHERE version=2) THEN
  ALTER TABLE users ADD COLUMN session_version INT UNSIGNED NOT NULL DEFAULT 1;
  ALTER TABLE characters ADD COLUMN fandom VARCHAR(180) NULL, ADD COLUMN status ENUM('draft','published') DEFAULT 'published';
  ALTER TABLE media ADD COLUMN tags VARCHAR(500) NULL, ADD COLUMN status ENUM('draft','published') DEFAULT 'published';
  ALTER TABLE merchandise ADD COLUMN fandom VARCHAR(180) NULL, ADD COLUMN resource_url VARCHAR(700) NULL, ADD COLUMN status ENUM('draft','published') DEFAULT 'published';
  ALTER TABLE content ADD COLUMN trailer_url VARCHAR(700) NULL;
  ALTER TABLE bookmarks MODIFY content_id INT UNSIGNED NULL,
   ADD COLUMN article_id INT UNSIGNED NULL, ADD COLUMN character_id INT UNSIGNED NULL,
   ADD COLUMN media_id INT UNSIGNED NULL, ADD COLUMN item_id INT UNSIGNED NULL,
   ADD UNIQUE KEY uq_bookmark_article(user_id,article_id), ADD UNIQUE KEY uq_bookmark_character(user_id,character_id),
   ADD UNIQUE KEY uq_bookmark_media(user_id,media_id), ADD UNIQUE KEY uq_bookmark_item(user_id,item_id),
   ADD FOREIGN KEY(article_id) REFERENCES articles(article_id) ON DELETE CASCADE,
   ADD FOREIGN KEY(character_id) REFERENCES characters(character_id) ON DELETE CASCADE,
   ADD FOREIGN KEY(media_id) REFERENCES media(media_id) ON DELETE CASCADE,
   ADD FOREIGN KEY(item_id) REFERENCES merchandise(item_id) ON DELETE CASCADE,
   ADD CONSTRAINT ck_bookmark_target CHECK ((content_id IS NOT NULL)+(article_id IS NOT NULL)+(character_id IS NOT NULL)+(media_id IS NOT NULL)+(item_id IS NOT NULL)=1);
  ALTER TABLE ratings MODIFY content_id INT UNSIGNED NULL,
   ADD COLUMN media_id INT UNSIGNED NULL, ADD UNIQUE KEY uq_rating_media(user_id,media_id),
   ADD FOREIGN KEY(media_id) REFERENCES media(media_id) ON DELETE CASCADE,
   ADD CONSTRAINT ck_rating_target CHECK ((content_id IS NOT NULL)+(media_id IS NOT NULL)=1);
  ALTER TABLE fan_submissions ADD COLUMN article_id INT UNSIGNED NULL,
   ADD FOREIGN KEY(article_id) REFERENCES articles(article_id) ON DELETE SET NULL;
  CREATE INDEX idx_activity_user ON activity_logs(user_id,log_id);
  CREATE INDEX idx_content_category_visibility ON content(category_id,visibility,created_at);
  INSERT INTO schema_versions(version) VALUES(2);
 END IF;
END$$
CALL fh_upgrade_2()$$
DROP PROCEDURE fh_upgrade_2$$
DELIMITER ;
