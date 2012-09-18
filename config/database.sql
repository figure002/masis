CREATE DATABASE masis
  WITH ENCODING = 'UTF8'
       TABLESPACE = pg_default
       LC_COLLATE = 'en_US.UTF-8'
       LC_CTYPE = 'en_US.UTF-8'
       CONNECTION LIMIT = -1;

/* Create functions */

CREATE FUNCTION round (DOUBLE PRECISION, INTEGER) RETURNS DOUBLE PRECISION AS
'select cast(round(cast($1 as numeric),$2) as double precision);'
LANGUAGE SQL with(iscachable);

/* Create data types */

CREATE TYPE annstat AS ENUM ('incomplete','complete');
CREATE TYPE substrdom AS ENUM ('dominant','subdominant');

/* Create tables */

CREATE TABLE image_info
(
    id SERIAL,
    altitude DOUBLE PRECISION NOT NULL,
    "depth" DOUBLE PRECISION NOT NULL,
    area DOUBLE PRECISION,
    img_dir VARCHAR NOT NULL,
    file_name VARCHAR NOT NULL,
    annotation_status annstat,

    PRIMARY KEY (id),
    UNIQUE (img_dir, file_name)
);
CREATE INDEX ON image_info (img_dir);

CREATE TABLE substrate_types
(
    name VARCHAR NOT NULL,
    description VARCHAR,

    PRIMARY KEY (name)
);

INSERT INTO substrate_types (name) VALUES ('boulder');
INSERT INTO substrate_types (name) VALUES ('cobble');
INSERT INTO substrate_types (name) VALUES ('Didemnum');
INSERT INTO substrate_types (name) VALUES ('epifauna (unspecified)');
INSERT INTO substrate_types (name) VALUES ('gravel');
INSERT INTO substrate_types (name) VALUES ('manmade object');
INSERT INTO substrate_types (name) VALUES ('mud');
INSERT INTO substrate_types (name) VALUES ('mussels');
INSERT INTO substrate_types (name) VALUES ('pebble');
INSERT INTO substrate_types (name) VALUES ('sand');
INSERT INTO substrate_types (name) VALUES ('shells');
INSERT INTO substrate_types (name) VALUES ('silt');

CREATE TABLE image_substrate
(
    image_info_id INTEGER NOT NULL,
    substrate_type VARCHAR NOT NULL,
    dominance substrdom NOT NULL,

    UNIQUE (image_info_id, substrate_type),
    FOREIGN KEY (image_info_id) REFERENCES image_info (id),
    FOREIGN KEY (substrate_type) REFERENCES substrate_types (name)
);

CREATE TABLE image_tag_types
(
    name VARCHAR NOT NULL,
    description VARCHAR,

    PRIMARY KEY (name)
);

INSERT INTO image_tag_types (name) VALUES ('altitude too high');
INSERT INTO image_tag_types (name) VALUES ('cannot see seafloor');
INSERT INTO image_tag_types (name) VALUES ('difficult image');
INSERT INTO image_tag_types (name) VALUES ('flag for review');
INSERT INTO image_tag_types (name) VALUES ('highlight');
INSERT INTO image_tag_types (name) VALUES ('image corrupt');
INSERT INTO image_tag_types (name) VALUES ('out of focus');
INSERT INTO image_tag_types (name) VALUES ('reviewed');
INSERT INTO image_tag_types (name) VALUES ('turbid');
INSERT INTO image_tag_types (name) VALUES ('unusable');
INSERT INTO image_tag_types (name) VALUES ('usable');

CREATE TABLE image_tags
(
    image_info_id INTEGER NOT NULL,
    image_tag VARCHAR NOT NULL,

    UNIQUE (image_info_id, image_tag),
    FOREIGN KEY (image_info_id) REFERENCES image_info (id),
    FOREIGN KEY (image_tag) REFERENCES image_tag_types (name)
);

CREATE TABLE species
(
    aphia_id INTEGER NOT NULL,
    lsid VARCHAR,
    scientific_name VARCHAR NOT NULL,
    status VARCHAR,
    valid_aphia_id INTEGER,
    valid_name VARCHAR,
    kingdom VARCHAR,
    phylum VARCHAR,
    class VARCHAR,
    "order" VARCHAR,
    family VARCHAR,
    genus VARCHAR,

    PRIMARY KEY (aphia_id)
);

CREATE TABLE vectors
(
    id SERIAL,
    image_info_id INTEGER NOT NULL,
    aphia_id INTEGER,
    vector_id VARCHAR NOT NULL,
    vector_wkt VARCHAR NOT NULL,
    area_pixels INTEGER,
    area_m2 DOUBLE PRECISION,
    created_by VARCHAR NOT NULL, -- creator user ID
    updated_by VARCHAR, -- updater user ID
    updated_on TIMESTAMP NOT NULL DEFAULT NOW(), -- create/update time
    remarks VARCHAR(50),

    PRIMARY KEY (id),
    UNIQUE (image_info_id,vector_id),
    FOREIGN KEY (image_info_id) REFERENCES image_info (id),
    FOREIGN KEY (aphia_id) REFERENCES species (aphia_id)
);

CREATE TABLE areas_image_grouped
(
    id SERIAL,
    image_info_id INTEGER NOT NULL,
    aphia_id INTEGER NOT NULL,
    species_area DOUBLE PRECISION NOT NULL,
    image_area DOUBLE PRECISION NOT NULL,

    PRIMARY KEY (id),
    UNIQUE (image_info_id,aphia_id),
    FOREIGN KEY (image_info_id) REFERENCES image_info (id),
    FOREIGN KEY (aphia_id) REFERENCES species (aphia_id)
);

CREATE TABLE IF NOT EXISTS users (
  user_id VARCHAR NOT NULL, -- this is an email address (user@mit.edu)
  pass_hash VARCHAR NOT NULL, -- preferably bcrypt hash
  first_name VARCHAR,

  PRIMARY KEY (user_id)
);

CREATE TABLE IF NOT EXISTS users_logged (
  user_id VARCHAR NOT NULL,
  hash VARCHAR NOT NULL, -- remember_me_hash cookie hash for the login session

  FOREIGN KEY (user_id) REFERENCES users (user_id)
);
