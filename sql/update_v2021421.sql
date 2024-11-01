CREATE TABLE {WPPREFIX}yawave_liveblogs (
id int NOT NULL AUTO_INCREMENT,
application_uuid varchar(200) NOT NULL,
uuid varchar(200) NOT NULL,
createtime datetime NOT NULL,
sportradar_id varchar(100) NOT NULL,
title varchar(200) NOT NULL,
description text NOT NULL,
saison_slug varchar(50) NOT NULL,
spieltag_slug varchar(50) NOT NULL,
wp_post_id int NOT NULL,
cover_image text NOT NULL,
yawave_type varchar(200) DEFAULT NULL,
yawave_status varchar(50) DEFAULT NULL,
location varchar(200) DEFAULT NULL,
start_date datetime NOT NULL,
home_competitor text,
away_competitor text,
yawave_sources text,
yawave_json text,
updatetime datetime NOT NULL,
PRIMARY KEY (id)
) COLLATE={WPCOLLATE};
COMMIT;

CREATE TABLE {WPPREFIX}yawave_liveblogs_posts (
id int NOT NULL AUTO_INCREMENT,
uuid varchar(200) NOT NULL,
source varchar(50) NOT NULL,
period varchar(25) NOT NULL,
minute int NOT NULL,
title varchar(200) NOT NULL,
post_content text NOT NULL,
url text NOT NULL,
publication_id varchar(200) NOT NULL,
pinned char(1) NOT NULL,
creation_date datetime NOT NULL,
liveblog_id int NOT NULL,
embed_code text NOT NULL,
all_parms text NOT NULL,
source_specs_values text NOT NULL,
timeline_timestamp datetime NOT NULL,
yawave_timestamp varchar(50) NOT NULL,
action_id varchar(100) NOT NULL,
person_id varchar(100) NOT NULL,
person_infos text NOT NULL,
action_infos text NOT NULL,
wp_visible_status char(1) NOT NULL,
update_date datetime NOT NULL,
PRIMARY KEY (id)
) COLLATE={WPCOLLATE};
COMMIT;

CREATE TABLE {WPPREFIX}yawave_log (
id int NOT NULL AUTO_INCREMENT,
logtime datetime NOT NULL,
logdata text NOT NULL,
slug varchar(100) NOT NULL,
PRIMARY KEY (id)
) COLLATE={WPCOLLATE};
COMMIT;

