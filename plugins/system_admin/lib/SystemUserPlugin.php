<?php
class SystemUserPlugin extends KalturaPlugin implements IKalturaPermissions, IKalturaServices
{
	const PLUGIN_NAME = 'systemUser';
	
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	public static function getServicesMap()
	{
		$map = array(
			'systemUser' => 'SystemUserService'
		);
		return $map;
	}
	
	public static function getServiceConfig()
	{
		return realpath(dirname(__FILE__).'/../config/system_user.ct');
	}

	public static function isAllowedPartner($partnerId)
	{
		if($partnerId == Partner::ADMIN_CONSOLE_PARTNER_ID)
			return true;
		
		return false;
	}
}
