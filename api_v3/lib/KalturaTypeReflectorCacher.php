<?php
class KalturaTypeReflectorCacher
{
	protected static $_loadedTypeReflectors = array();
	protected static $_enabled = true;
	
	static function disable()
	{
		self::$_enabled = false;
	}
	
	static function enable()
	{
		self::$_enabled = true;
	}
	
	static function get($type)
	{
		if (!self::$_enabled)
			return new KalturaTypeReflector($type);
			
		if (!array_key_exists($type, self::$_loadedTypeReflectors))
		{
			$cachedDir = KAutoloader::buildPath(KALTURA_API_PATH, "cache", "typeReflector");
			if (!is_dir($cachedDir))
				mkdir($cachedDir);
				
			$cachedFilePath = $cachedDir.DIRECTORY_SEPARATOR.$type.".cache";
			if (file_exists($cachedFilePath))
			{
				$cachedData = file_get_contents($cachedFilePath);
				$typeReflector = unserialize($cachedData);
			}
			else
			{
				$typeReflector = new KalturaTypeReflector($type);
				$cachedData = serialize($typeReflector);
				file_put_contents($cachedFilePath, $cachedData);
			}
			
			self::$_loadedTypeReflectors[$type] = $typeReflector;
		}
		
		return self::$_loadedTypeReflectors[$type];
	}
}