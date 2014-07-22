DROP TRIGGER IF EXISTS update_registration;
delimiter |
CREATE TRIGGER update_registration AFTER UPDATE ON cas_user
FOR EACH ROW 
BEGIN
INSERT INTO users_roles (uid) SELECT users.uid FROM users
    JOIN cas_user ON cas_user.uid = users.uid
    LEFT JOIN users_roles ON users_roles.uid = users.uid
    WHERE users_roles.uid is null;
UPDATE users_roles
    JOIN users ON users_roles.uid = users.uid
    JOIN cas_user ON cas_user.uid = users.uid
    JOIN _maui_students ON cas_user.cas_name = _maui_students.hawkid
    LEFT JOIN _maui_ignore ON _maui_ignore.uid = users.uid
    SET users_roles.rid = 5
    WHERE _maui_ignore.uid is null;
UPDATE  `field_data_field_last_name`
    JOIN `cas_user` ON `field_data_field_last_name`.`entity_id` = `cas_user`.`uid`
    JOIN `_maui_students` ON `cas_user`.`cas_name` =`_maui_students`.`hawkid`
    SET `field_last_name_value` = `_maui_students`.`LAST_NAME`;
UPDATE  `field_data_field_first_name`
    JOIN `cas_user` ON `field_data_field_first_name`.`entity_id` = `cas_user`.`uid`
    JOIN `_maui_students` ON `cas_user`.`cas_name` =`_maui_students`.`hawkid`
    SET `field_first_name_value` = `_maui_students`.`FIRST_NAME`;
INSERT INTO _maui_students_dropped (uid) 
    SELECT users_roles.uid FROM users_roles
    JOIN users ON users_roles.uid = users.uid
    JOIN cas_user ON cas_user.uid = users.uid
    LEFT JOIN _maui_students ON cas_user.cas_name = _maui_students.hawkid
    LEFT JOIN _maui_ignore ON _maui_ignore.uid = users.uid
    WHERE _maui_ignore.uid is null AND _maui_students.hawkid is null AND users_roles.uid = 5;
UPDATE users_roles
    JOIN users ON users_roles.uid = users.uid
    JOIN cas_user ON cas_user.uid = users.uid
    LEFT JOIN _maui_students ON cas_user.cas_name = _maui_students.hawkid
    LEFT JOIN _maui_ignore ON _maui_ignore.uid = users.uid
    SET users_roles.rid = 1
    WHERE _maui_ignore.uid is null AND _maui_students.hawkid is null;
END;    
|
delimiter ;






INSERT IGNORE INTO users_roles (uid)
    VALUES (SELECT users.uid FROM users
    JOIN users_roles ON users_roles.uid = users.uid
    JOIN cas_user ON cas_user.uid = users.uid
    JOIN _maui_students ON cas_user.cas_name = _maui_students.hawkid
    LEFT JOIN _maui_ignore ON _maui_ignore.uid = users.uid
    WHERE _maui_ignore.uid is null);


UPDATE users_roles
    JOIN users ON users_roles.uid = users.uid
    JOIN cas_user ON cas_user.uid = users.uid
    JOIN _maui_students ON cas_user.cas_name = _maui_students.hawkid
    LEFT JOIN _maui_ignore ON _maui_ignore.uid = users.uid
    SET users_roles.rid = 5
    WHERE _maui_ignore.uid is null