CREATE TABLE IF NOT EXISTS comments (cmntID INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, noteID INTEGER, uID INTEGER DEFAULT 0, ctime INTEGER DEFAULT CURRENT_TIMESTAMP, comment TEXT);
CREATE INDEX cmnt_noteid_idx ON comments (noteID);
ALTER TABLE notes ADD COLUMN cmntcnt INTEGER DEFAULT 0;
PRAGMA user_version=4