DELETE FROM flavor_params WHERE id = -1;

INSERT INTO flavor_params(id,VERSION,partner_id,NAME,tags,description,ready_behavior,created_at,updated_at,deleted_at,is_default,FORMAT,video_codec,video_bitrate,audio_codec,audio_bitrate,audio_channels,audio_sample_rate,audio_resolution,width,height,frame_rate,gop_size,two_pass,conversion_engines,conversion_engines_extra_params,custom_data,view_order,creation_mode,deinterlice,rotate,operators,engine_version,TYPE) 
VALUES (-1,0,0,'Generic (H264)','','High Definition',2,NOW(),NOW(),NULL,0,'mp4','h264m',4000,'aac',192,0,0,0,0,1080,0,0,0,'2,1,99,3','',NULL,0,1,0,0,'',0,1);
