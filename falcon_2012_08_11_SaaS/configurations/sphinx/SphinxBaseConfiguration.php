<?php
$baseDir = '/opt/kaltura';
$baseConfigurationIndexs = 	array( 
	'kaltura_entry' => array(	
	'path'					=> '/sphinx/kaltura_rt',
	
	'fields' => array( 
		'entry_id'					=> SphinxFieldType::RT_FIELD,
		'name'                     	=> SphinxFieldType::RT_FIELD,
		'tags'                     	=> SphinxFieldType::RT_FIELD,
		'categories'               	=> SphinxFieldType::RT_FIELD,
		'flavor_params'           	=> SphinxFieldType::RT_FIELD,
		'source_link'              	=> SphinxFieldType::RT_FIELD,
		'kshow_id'                 	=> SphinxFieldType::RT_FIELD,
		'group_id'                 	=> SphinxFieldType::RT_FIELD,
		'description'              	=> SphinxFieldType::RT_FIELD,
		'admin_tags'     			=> SphinxFieldType::RT_FIELD,
		'plugins_data'				=> SphinxFieldType::RT_FIELD,
		'duration_type'          	=> SphinxFieldType::RT_FIELD,
		'reference_id'             	=> SphinxFieldType::RT_FIELD,
		'replacing_entry_id'    	=> SphinxFieldType::RT_FIELD,
		'replaced_entry_id' 	    => SphinxFieldType::RT_FIELD,
		'roots'	                    => SphinxFieldType::RT_FIELD,
		
		'sort_name'					=> SphinxFieldType::RT_ATTR_BIGINT,
        'int_entry_id'				=> SphinxFieldType::RT_ATTR_BIGINT,
        'kuser_id'					=> SphinxFieldType::RT_ATTR_BIGINT,
        'entry_status'				=> SphinxFieldType::RT_ATTR_BIGINT,
		'type'						=> SphinxFieldType::RT_ATTR_BIGINT,
		'media_type'	 			=> SphinxFieldType::RT_ATTR_BIGINT,
		'views'			 			=> SphinxFieldType::RT_ATTR_BIGINT,
		'partner_id'	 			=> SphinxFieldType::RT_ATTR_BIGINT,
		'moderation_status'			=> SphinxFieldType::RT_ATTR_BIGINT,
		'display_in_search'			=> SphinxFieldType::RT_ATTR_BIGINT,
		'duration'		 			=> SphinxFieldType::RT_ATTR_BIGINT,
		'access_control_id'			=> SphinxFieldType::RT_ATTR_BIGINT,
		'moderation_count' 			=> SphinxFieldType::RT_ATTR_BIGINT,
		'rank'			 			=> SphinxFieldType::RT_ATTR_BIGINT,
		'plays'			 			=> SphinxFieldType::RT_ATTR_BIGINT,
		'partner_sort_value'		=> SphinxFieldType::RT_ATTR_BIGINT,
		'replacement_status'		=> SphinxFieldType::RT_ATTR_BIGINT,

		
		'created_at'				=> SphinxFieldType::RT_ATTR_TIMESTAMP,
		'updated_at'				=> SphinxFieldType::RT_ATTR_TIMESTAMP,
		'modified_at'				=> SphinxFieldType::RT_ATTR_TIMESTAMP,
		'media_date'				=> SphinxFieldType::RT_ATTR_TIMESTAMP,
		'start_date'				=> SphinxFieldType::RT_ATTR_TIMESTAMP,
		'end_date'					=> SphinxFieldType::RT_ATTR_TIMESTAMP,
		'available_from'			=> SphinxFieldType::RT_ATTR_TIMESTAMP,	
		
		'str_entry_id'				=> SphinxFieldType::RT_ATTR_STRING),
	)
);
		
$baseConfigurations = array (
	'searchd' => array(
		'log'				=> '/var/log/sphinx/kaltura_sphinx_searchd.log',
		'query_log'			=> '/var/log/sphinx/kaltura_sphinx_query.log',
		'query_log_format'	=> 'sphinxql',
		'read_timeout'		=> '5',
		'max_children'		=> '30',
		'pid_file'			=> '/opt/kaltura/sphinx/searchd.pid',
		'max_matches'		=> '10000',
		'preopen_indexes'	=> '0',
		'unlink_old'		=> '1',
		'workers'			=> 'threads',
		'binlog_path'		=> '/usr/local/var/data',
		'binlog_flush'		=> '1',
		'rt_flush_period'	=> '3600',
		'listen'			=> '0.0.0.0:9312:mysql41'
	)
);
