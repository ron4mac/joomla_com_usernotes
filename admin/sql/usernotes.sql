BEGIN TRANSACTION;
CREATE TABLE notes(
	itemID INTEGER PRIMARY KEY NOT NULL,
	ownerID INTEGER NOT NULL,
	shared INTEGER DEFAULT 1,
	isParent INTEGER DEFAULT 0,
	title TEXT,
	contentID INTEGER,
	parentID INTEGER NOT NULL DEFAULT 0,
	secured BOOLEAN DEFAULT NULL,
	checked_out INTEGER DEFAULT 0,
	checked_out_time DATETIME DEFAULT NULL
	);
CREATE TABLE content(
	contentID INTEGER PRIMARY KEY NOT NULL,
	serial_content TEXT
	);
CREATE TABLE fileatt (
	contentID INTEGER NOT NULL,
	fsize INTEGER,
	attached TEXT
	);
CREATE VIEW attsizsum AS SELECT SUM(fsize) AS totatt FROM fileatt;
COMMIT