DELETE FROM conversion_profile_2;
ALTER TABLE conversion_profile_2 AUTO_INCREMENT = 1;
DELETE FROM flavor_params;
ALTER TABLE flavor_params AUTO_INCREMENT = 1;
DELETE FROM flavor_params_conversion_profile;
ALTER TABLE flavor_params_conversion_profile AUTO_INCREMENT = 1;

insert into `flavor_params` (`id`, `version`, `partner_id`, `name`, `tags`, `description`, `ready_behavior`, `created_at`, `updated_at`, `deleted_at`, `is_default`, `format`, `video_codec`, `video_bitrate`, `audio_codec`, `audio_bitrate`, `audio_channels`, `audio_sample_rate`, `audio_resolution`, `width`, `height`, `frame_rate`, `gop_size`, `two_pass`, `conversion_engines`, `conversion_engines_extra_params`, `custom_data`, `view_order`, `creation_mode`) 
values('0','0','0','Source','source','Maintains the original format and settings of the file - duplicate of the source file','0',NOW(),NOW(),NULL,'1','','','0','','0','0','0','0','0','0','0','0','0','','',NULL,'0','1');

# make sure source is ID 0
update flavor_params set id = 0 where id = 1;
ALTER TABLE flavor_params AUTO_INCREMENT = 1;

insert into `flavor_params` (`id`, `version`, `partner_id`, `name`, `tags`, `description`, `ready_behavior`, `created_at`, `updated_at`, `deleted_at`, `is_default`, `format`, `video_codec`, `video_bitrate`, `audio_codec`, `audio_bitrate`, `audio_channels`, `audio_sample_rate`, `audio_resolution`, `width`, `height`, `frame_rate`, `gop_size`, `two_pass`, `conversion_engines`, `conversion_engines_extra_params`, `custom_data`, `view_order`, `creation_mode`) 
values('1','0','0','HD','web,mbr','High Definition','2',NOW(),NOW(),NULL,'1','flv','vp6','4000','mp3','192','0','0','0','0','1080','0','0','0','1,4,2,99,3','',NULL,'0','1');

insert into `flavor_params` (`id`, `version`, `partner_id`, `name`, `tags`, `description`, `ready_behavior`, `created_at`, `updated_at`, `deleted_at`, `is_default`, `format`, `video_codec`, `video_bitrate`, `audio_codec`, `audio_bitrate`, `audio_channels`, `audio_sample_rate`, `audio_resolution`, `width`, `height`, `frame_rate`, `gop_size`, `two_pass`, `conversion_engines`, `conversion_engines_extra_params`, `custom_data`, `view_order`, `creation_mode`) 
values('2','0','0','High - Large','web,mbr','High web quality, large frame','2',NOW(),NOW(),NULL,'1','flv','vp6','2500','mp3','128','0','0','0','0','720','0','0','0','1,4,2,99,3','',NULL,'0','1');

insert into `flavor_params` (`id`, `version`, `partner_id`, `name`, `tags`, `description`, `ready_behavior`, `created_at`, `updated_at`, `deleted_at`, `is_default`, `format`, `video_codec`, `video_bitrate`, `audio_codec`, `audio_bitrate`, `audio_channels`, `audio_sample_rate`, `audio_resolution`, `width`, `height`, `frame_rate`, `gop_size`, `two_pass`, `conversion_engines`, `conversion_engines_extra_params`, `custom_data`, `view_order`, `creation_mode`) 
values('3','0','0','Standard - Large','web,mbr','Standard web quality, large frame','2',NOW(),NOW(),NULL,'1','flv','vp6','1350','mp3','96','0','0','0','0','720','0','0','0','1,4,2,99,3','',NULL,'0','1');

insert into `flavor_params` (`id`, `version`, `partner_id`, `name`, `tags`, `description`, `ready_behavior`, `created_at`, `updated_at`, `deleted_at`, `is_default`, `format`, `video_codec`, `video_bitrate`, `audio_codec`, `audio_bitrate`, `audio_channels`, `audio_sample_rate`, `audio_resolution`, `width`, `height`, `frame_rate`, `gop_size`, `two_pass`, `conversion_engines`, `conversion_engines_extra_params`, `custom_data`, `view_order`, `creation_mode`) 
values('4','0','0','Standard - Small','web,mbr','Standard web quality, small frame','2',NOW(),NOW(),NULL,'1','flv','vp6','750','mp3','96','0','0','0','0','288','0','0','0','1,4,2,99,3','',NULL,'0','1');

insert into `flavor_params` (`id`, `version`, `partner_id`, `name`, `tags`, `description`, `ready_behavior`, `created_at`, `updated_at`, `deleted_at`, `is_default`, `format`, `video_codec`, `video_bitrate`, `audio_codec`, `audio_bitrate`, `audio_channels`, `audio_sample_rate`, `audio_resolution`, `width`, `height`, `frame_rate`, `gop_size`, `two_pass`, `conversion_engines`, `conversion_engines_extra_params`, `custom_data`, `view_order`, `creation_mode`) 
values('5','0','0','Basic - Small','web,mbr','Basic web quality, small frame. To be used for low resource environments.','2',NOW(),NOW(),NULL,'1','flv','vp6','400','mp3','96','0','0','0','0','288','0','0','0','1,4,2,99,3','',NULL,'0','1');

insert into `flavor_params` (`id`, `version`, `partner_id`, `name`, `tags`, `description`, `ready_behavior`, `created_at`, `updated_at`, `deleted_at`, `is_default`, `format`, `video_codec`, `video_bitrate`, `audio_codec`, `audio_bitrate`, `audio_channels`, `audio_sample_rate`, `audio_resolution`, `width`, `height`, `frame_rate`, `gop_size`, `two_pass`, `conversion_engines`, `conversion_engines_extra_params`, `custom_data`, `view_order`, `creation_mode`) 
values('6','0','0','HQ MP4 for Export','mp4_export,web','High web quality in MP4 format, to be used for download or syndication','0',NOW(),NOW(),NULL,'1','mp4','h264','2500','aac','128','0','0','0','0','720','0','0','0','1,4,2','',NULL,'0','1');

insert into `flavor_params` (`id`, `version`, `partner_id`, `name`, `tags`, `description`, `ready_behavior`, `created_at`, `updated_at`, `deleted_at`, `is_default`, `format`, `video_codec`, `video_bitrate`, `audio_codec`, `audio_bitrate`, `audio_channels`, `audio_sample_rate`, `audio_resolution`, `width`, `height`, `frame_rate`, `gop_size`, `two_pass`, `conversion_engines`, `conversion_engines_extra_params`, `custom_data`, `view_order`, `creation_mode`) 
values('7','0','0','Editable','edit,web','Good web quality, to be used for editable content','0',NOW(),NOW(),NULL,'1','flv','vp6','700','mp3','64','0','0','0','0','480','0','5','0','1,4,2,99,3','',NULL,'0','1');


#### following are not in for day one ! ####
#insert into `flavor_params` (`id`, `version`, `partner_id`, `name`, `tags`, `description`, `ready_behavior`, `created_at`, `updated_at`, `deleted_at`, `is_default`, `format`, `video_codec`, `video_bitrate`, `audio_codec`, `audio_bitrate`, `audio_channels`, `audio_sample_rate`, `audio_resolution`, `width`, `height`, `frame_rate`, `gop_size`, `two_pass`, `conversion_engines`, `conversion_engines_extra_params`, `custom_data`, `view_order`, `creation_mode`) 
#values('8','0','0','Audio-only','audio_only_export,web','Audio-only, to be used for download or syndication','0',NOW(),NOW(),NULL,'1','flv','','0','mp3','96','2','44100','0','0','0','0','0','0','2,99','',NULL,'0','1');

#insert into `flavor_params` (`id`, `version`, `partner_id`, `name`, `tags`, `description`, `ready_behavior`, `created_at`, `updated_at`, `deleted_at`, `is_default`, `format`, `video_codec`, `video_bitrate`, `audio_codec`, `audio_bitrate`, `audio_channels`, `audio_sample_rate`, `audio_resolution`, `width`, `height`, `frame_rate`, `gop_size`, `two_pass`, `conversion_engines`, `conversion_engines_extra_params`, `custom_data`, `view_order`, `creation_mode`) 
#values('9','0','0','HQ AVI for Export','avi_export','High web quality in AVI format, to be used for download or syndication','0',NOW(),NOW(),NULL,'1','avi','h264','2500','mp3','128','0','0','0','0','720','0','0','0','2,3,4','',NULL,'0','1');

#insert into `flavor_params` (`id`, `version`, `partner_id`, `name`, `tags`, `description`, `ready_behavior`, `created_at`, `updated_at`, `deleted_at`, `is_default`, `format`, `video_codec`, `video_bitrate`, `audio_codec`, `audio_bitrate`, `audio_channels`, `audio_sample_rate`, `audio_resolution`, `width`, `height`, `frame_rate`, `gop_size`, `two_pass`, `conversion_engines`, `conversion_engines_extra_params`, `custom_data`, `view_order`, `creation_mode`) 
#values('10','0','0','HQ MOV for Export','mov_export','High web quality in MOV format, to be used for download or syndication','0',NOW(),NOW(),NULL,'1','mov','h264','2500','aac','128','0','0','0','0','720','0','0','0','2,3,4','',NULL,'0','1');

ALTER TABLE flavor_params AUTO_INCREMENT = 200;

insert into conversion_profile_2 (id,partner_id, name, created_at, updated_at, deleted_at, description, crop_left, crop_top, crop_width, crop_height, clip_start, clip_duration) VALUES
(1,'99', 'Default', now(), now(), NULL, 'The default set of flavors. If not specified otherwise all media uploaded will be converted based on the definition in this profile', '-1', '-1', '-1', '-1', '-1', '-1');
#updates_2009-12-27_conversion_profile_input_tags.sql
UPDATE conversion_profile_2 SET input_tags_map = 'web' WHERE input_tags_map IS NULL;

#flavor_params_conversion_profile
insert into flavor_params_conversion_profile( conversion_profile_id, flavor_params_id, ready_behavior, force_none_complied, created_at, updated_at)
values(1, 0, 0, null, now(), now());
insert into flavor_params_conversion_profile( conversion_profile_id, flavor_params_id, ready_behavior, force_none_complied, created_at, updated_at)
values(1, 1, 2, null, now(), now());
insert into flavor_params_conversion_profile( conversion_profile_id, flavor_params_id, ready_behavior, force_none_complied, created_at, updated_at)
values(1, 2, 2, null, now(), now());
insert into flavor_params_conversion_profile( conversion_profile_id, flavor_params_id, ready_behavior, force_none_complied, created_at, updated_at)
values(1, 3, 2, null, now(), now());
insert into flavor_params_conversion_profile( conversion_profile_id, flavor_params_id, ready_behavior, force_none_complied, created_at, updated_at)
values(1, 4, 2, null, now(), now());
insert into flavor_params_conversion_profile( conversion_profile_id, flavor_params_id, ready_behavior, force_none_complied, created_at, updated_at)
values(1, 5, 2, null, now(), now());

#run conversion profile migration

