<?php
class kConf
{
	private static $map = null;
	
	private static function init()
	{
		if ( self::$map ) return;
		/** KALTURA-INSTALL **/
		// will be used for the instalation script to modify 
		$bin_path = "";
		
		self::$map = 
			array (
                                // take over the symfony config (sfConfig)
                                "sf_debug" => false,
                                "sf_logging_enabled" => true,
                                "sf_root_dir" => SF_ROOT_DIR,
				"cdn_host" => "cdnbakmi.kaltura.com",
				"iis_host" => "smoothakmi.kaltura.com",
				"www_host" => "www.kaltura.com",
				"rtmp_url" => "rtmp://rtmpakmi.kaltura.com:1935/ondemand",
				
				"kaltura_installation_type" => "",
				"enable_cache" => true,
				
				"corp_action_redirect" => "corp.kaltura.com",
			
				"terms_of_use_uri" => "index.php/terms",

				"memcache_host" => "localhost" ,
				"memcache_port" => "11211" ,
			
				"server_api_v2_path" => "/api/" ,
			
				"apphome_url" => "http://www.kaltura.com",
				"apphome_url_no_protocol" => "www.kaltura.com",
				"default_email" => "customer_service@kaltura.com",
				"default_email_name" => "Kaltura Automated Response",
				"partner_registration_confirmation_email" => "registration_confirmation@kaltura.com",
				"partner_registration_confirmation_name" => "Kaltura",
				"partner_notification_email" => "customer_service@kaltura.com",
				"partner_notification_name" => "Kaltura Automated Response",
				"partner_change_email_email" => "customer_service@kaltura.com",
				"partner_change_email_name" => "Kaltura Automated Response",
				"purchase_package_email" => "customer_service@kaltura.com",
				"purchase_package_name" => "Kaltura Automated Response",
				"batch_download_video_sender_email" => "download_video@kaltura.com",
				"batch_download_video_sender_name" => "Kaltura",
				"batch_flatten_video_sender_email" => "download_video@kaltura.com",
				"batch_flatten_video_sender_name" => "Kaltura",
				"batch_notification_sender_email" => "notifications@kaltura.com" , 
				"batch_notification_sender_name" => "Kaltura" ,

				"batch_alert_email" => "alert@kaltura.com" , 
				"batch_alert_name" => "Kaltura" ,

				"default_flow_manager_class" => "kFlowManager",
				
				"default_duplication_time_frame" => 60 ,
				"job_duplication_time_frame" => array(
				) ,
			
				"default_job_execution_attempt" => 3 ,
				"job_execution_attempt" => array(
					16 => 5, //BatchJob::BATCHJOB_TYPE_NOTIFICATION
					4 => 1, //BatchJob::BATCHJOB_TYPE_BULK_UPLOAD
					23 => 2, //BatchJob::BATCHJOB_TYPE_STORAGE_EXPORT
				) ,
			
				"default_job_retry_interval" => 20 ,
				"job_retry_intervals" => array(
					16 => 600, // BatchJob::BATCHJOB_TYPE_NOTIFICATION
					15 => 150, // BatchJob::BATCHJOB_TYPE_MAIL
					1 => 300, // BatchJob::BATCHJOB_TYPE_IMPORT
					23 => 300, // BatchJob::BATCHJOB_TYPE_STORAGE_EXPORT
				) ,
				
				"ignore_cdl_failure" => false,
			
				"batch_ignore_duplication" => true ,
				"priority_percent" => array(1 => 33, 2 => 27, 3 => 20, 4 => 13, 5 => 7),
				"priority_time_range" => 60,
		
				"kmc_admin_login_generic_regexp" => "/__([0-9]+)__@kaltura.com/" ,			// used as generic backdoor to kmc
				"kmc_admin_login_sha1_password" => "b65a488543b08affd62c945e41fb83b268799cd2" ,
				
				"system_pages_login_username" => "kaltura",
				"system_pages_login_password" => "bb01739677f512607521a4b09f677ed03198ef70",
				"system_allow_edit_kConf" => false,
				"testmeconsole_state" => true,
				
				"flash_root_url" => "",
				"uiconf_root_url" => "",
				"content_root_url" => "",
			
				"bin_path_ffmpeg" => $bin_path . "ffmpeg" ,
				"bin_path_mencoder" => $bin_path . "mencoder",
				"bin_path_flix" => $bin_path . "cli_encode",
				"bin_path_encoding_com" => $bin_path . "encoding_com.php",
				"bin_path_imagemagick" => $bin_path . "convert",
				"bin_path_curl" => $bin_path . "curl",
				"bin_path_mediainfo" => $bin_path . "mediainfo",
			
				"image_proxy_url" => "174.129.230.196" ,
				"image_proxy_port" => "8999" ,	
				"image_proxy_secret" => "fwejk324kljd#eqwkl" ,
			
				"kmc_display_customize_tab" => true,
				"kmc_display_account_tab" => true, 
				"kmc_content_enable_commercial_transcoding" => true, 
				"kmc_content_enable_live_streaming" => true,
				"kmc_login_show_signup_link" => true,
				"kmc_display_developer_tab" => false,
				"kmc_display_server_tab" => false,
				"kmc_account_show_usage" => true,

			/* kmc applications versions */
				"kmc_content_version" => 'v3.0.2.1',
				"kmc_account_version" => 'v2.1.4',
				"kmc_appstudio_version" => 'v2.0.6',
				"kmc_rna_version" => 'v1.1.5',
				"kmc_dashboard_version" => 'v1.0.13',
				"kmc_login_version" => 'v1.0.12',


				/* kmc tabs rules */
				"kmc_rna_allowed_partners" => array( 0 , 55 , 82 , 300 , 309 , 2217 , 20623 , 22598 , 23625 , 19289 , 27630 , 19650 , 21658 , 34430 , 36599 , 33471 , 26959 , 42902 ,
					 11918 , 3213 ,
					 19401 , 33138 , 28093 , 36022 , 2631 , 32316 , 27099 , 26029 , 29959 , 27008 , 24931 , 27681 , 30747 , 34612 , 30595 , 33610 , 37305 , 33105 , 36094 , 34416 ,
					 31079 , 33383 , 34724 , 32774 , 34654 , 27121 , 36405 , 23707 , 36498 , 29516 , 33317, 35426, 37399 , 33318 , 35782 , 38050 ,33655 , 37164 , 48112 , 7463 ),

				"paypal_data" => array (
					"api_username" => 'gonen._1236086971_biz_api1.kaltura.com' ,
					"api_password" => '1236086990' ,
					"api_signature" => 'ATDF-wXMCOm13YkZAGOZJ5Z9c14fAhz92s8uuI4R7EKz93xZvtUJBbfN ' , 
					"environment" => 'sandbox'),
				
				"ga_account" => 'UA-7714780-1', // UA-7714780-1 = prod | UA-12055206-1 = all QA | UA-15857288-1 = all dev (including locals)

				"marketo_access_key" => "kaltura1_892512424C1FC1B8BA53F2",
				"marketo_secret_key" => "061698860392609144CC11FFCC11BB9ABBAA7BCCA530",
				
				"limelight_madiavault_password" => "NSjMpEHcL0EBg",
				"level3_authentication_key" => "a9e49681957ddb56b07ff9cc7dd170cc",
				"akamai_auth_smooth_param" => "aeauth",
				"akamai_auth_smooth_salt" => "aSd98uAsF3inf9aRAsniV",
				
				"kdpwrapper_track_url" => '',
				"kaltura_partner_id" => "",
				"track_kdpwrapper" => false,	

				"reports_db_config" => array (
					"host" => "192.168.252.131" , "test.kaltura.com" , "localhost",
					"user" => "root" ,
					"port" => 3306,
					"password" => "root" ,
					"db_name" => "kalturadw" , 
				) , 
				
				"template_partner_id" => 99,
				"event_log_file_path" => "/var/log/events_log",
                                "dc_config" => array (
                                          "current" => "0",
                                          "list" => array (
                                                        "0" => array ( "name" => "PA" , "url" => "http://pa-www.kaltura.com" , "external_url" => "http://208.122.63.164" , "secret" => "abc" , "root" => "/web/" ),
                                                        "1" => array ( "name" => "NY" , "url" => "http://ny-www.kaltura.com" , "external_url" => "http://72.251.194.4" ,  "secret" => "def" , "root" => "/web/" ),
                                          )
                                ),
				
				"url_managers" => array(
                                	'cdn.kaltura.com' => 'kLimeLightUrlManager',
                                	'cdnakmi.kaltura.com' => 'kAkamaiUrlManager',
                                	'cdnbakmi.kaltura.com' => 'kAkamaiUrlManager',
                                	'cdncakmi.kaltura.com' => 'kAkamaiUrlManager',
                                	'smoothakmi.kaltura.com' => 'kAkamaiUrlManager',
                                	'cdnlthree.kaltura.com' => 'kLevel3UrlManager',
                                
                                	'svenskaspel.kaltura.com' => 'svenskaLimieLightUrlManager',                                     
                                ),
                                
				"date_default_timezone" => "America/New_York",
				
				"kaltura_email_hash" => "kaktus1514",
             			"default_plugins" => array(
//                                        "SystemUserPlugin",
//                                        "SystemPartnerPlugin",
                                        "FileSyncPlugin",
//                                        "AdminConsolePlugin",
                                        "StorageProfilePlugin",
                                ),
								
				"cache_root_path" => "/opt/kaltura/cache",
			);
	}
	
	public static function get ( $param_name )
	{
		self::init();
		$res = self::$map [ $param_name ];
		// for now - throw an exception if now param in config - it will help prevent typos 
		// TODO - remove exception
		if ( $res === null ) throw new Exception ( "Cannot find [$param_name] in config" ) ;
		// kLog::log( "kConf [$param_name]=[$res]" );
		return $res; 
	}
	
	public static function hasParam($param_name)
	{
		return array_key_exists($param_name, self::$map);
	}

}
?>
