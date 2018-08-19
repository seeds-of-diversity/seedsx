--  KeyFrame example tables
--
--  Run this script in your database to create the tables for the KeyFrame examples

CREATE TABLE keyframe_example_people (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    name            VARCHAR(200),
    age             INTEGER
);

CREATE TABLE keyframe_example_pets (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_keyframe_example_people  INTEGER NOT NULL,

    name            VARCHAR(200),
    age             INTEGER
);


INSERT INTO keyframe_example_people (_key,_created,_created_by,_updated,_updated_by,_status,name,age) VALUES (NULL,NOW(),1,NOW(),1,0,'Alice',40);
INSERT INTO keyframe_example_people (_key,_created,_created_by,_updated,_updated_by,_status,name,age) VALUES (NULL,NOW(),1,NOW(),1,0,'Bob',39);
INSERT INTO keyframe_example_people (_key,_created,_created_by,_updated,_updated_by,_status,name,age) VALUES (NULL,NOW(),1,NOW(),1,0,'Cathy',37);
INSERT INTO keyframe_example_people (_key,_created,_created_by,_updated,_updated_by,_status,name,age) VALUES (NULL,NOW(),1,NOW(),1,0,'Dave',42);

INSERT INTO keyframe_example_pets (_key,_created,_created_by,_updated,_updated_by,_status,fk_keyframe_example_people,name,age) VALUES (NULL,NOW(),1,NOW(),1,0,1,'Apple',4);
INSERT INTO keyframe_example_pets (_key,_created,_created_by,_updated,_updated_by,_status,fk_keyframe_example_people,name,age) VALUES (NULL,NOW(),1,NOW(),1,0,1,'Brutus',2);
INSERT INTO keyframe_example_pets (_key,_created,_created_by,_updated,_updated_by,_status,fk_keyframe_example_people,name,age) VALUES (NULL,NOW(),1,NOW(),1,0,2,'Cutie',5);
INSERT INTO keyframe_example_pets (_key,_created,_created_by,_updated,_updated_by,_status,fk_keyframe_example_people,name,age) VALUES (NULL,NOW(),1,NOW(),1,0,3,'Dutch',7);
INSERT INTO keyframe_example_pets (_key,_created,_created_by,_updated,_updated_by,_status,fk_keyframe_example_people,name,age) VALUES (NULL,NOW(),1,NOW(),1,0,3,'Egghead',1);
INSERT INTO keyframe_example_pets (_key,_created,_created_by,_updated,_updated_by,_status,fk_keyframe_example_people,name,age) VALUES (NULL,NOW(),1,NOW(),1,0,4,'Fluffy',8);


