CREATE TABLE IF NOT EXISTS fileatt (contentID INTEGER NOT NULL, fsize INTEGER, attached TEXT);
#$transform attachments here;
DROP TABLE IF EXISTS attach;
CREATE VIEW IF NOT EXISTS attsizsum AS SELECT SUM(fsize) AS totatt FROM fileatt;
ALTER TABLE notes ADD COLUMN secured BOOLEAN DEFAULT NULL;
ALTER TABLE notes ADD COLUMN vcount INTEGER DEFAULT 0;
ALTER TABLE notes ADD COLUMN vtotal INTEGER DEFAULT 0;
CREATE TABLE IF NOT EXISTS uratings (iid INTEGER,uid INTEGER,rdate INTEGER NOT NULL);
CREATE TABLE IF NOT EXISTS gratings (iid INTEGER,ip INTEGER,rdate INTEGER NOT NULL);
PRAGMA user_version=1