<?php

require_once(dirname(__FILE__) . '/kBaseCacheWrapper.php');

/**
 * @package infra
 * @subpackage cache
 */
class kFileSystemCacheWrapper extends kBaseCacheWrapper
{
	const EXPIRY_SUFFIX = '.expiry';

	protected $baseFolder;
	protected $baseFilename;
	protected $keyFolderChars;
	protected $serializeData;

	/**
	 * @param string $baseFolder
	 * @param string $baseFilename
	 * @param int $keyFolderChars
	 * @param bool $serializeData
	 * @return bool false on error
	 */
	public function init($rootFolder, $baseFolder, $baseFilename, $keyFolderChars, $serializeData)
	{
		$this->baseFolder = rtrim($rootFolder, '/') . '/' . rtrim($baseFolder, '/') . '/';
		$this->baseFilename = $baseFilename;
		$this->keyFolderChars = $keyFolderChars;
		$this->serializeData = $serializeData;
		return true;
	}
	
	/**
	 * @param string $key
	 * @return string
	 */
	protected function getFilePath($key)
	{
		$filePath = $this->baseFolder;
		if ($this->keyFolderChars)
			$filePath .= substr($key, 0, $this->keyFolderChars) . '/';
		return $filePath . $this->baseFilename . $key;
	}

	/**
	 * @param string $filePath
	 */
	protected static function createDirForPath($filePath)
	{
		$dirname = dirname($filePath);
		if (!is_dir($dirname))
		{
			mkdir($dirname, 0777, true);
		}
	}
		
	/* (non-PHPdoc)
	 * @see kBaseCacheWrapper::get()
	 */
	public function get($key, $defaultExpiry = 0)
	{
		$filePath = $this->getFilePath($key);
		if (!file_exists($filePath))
			return false;
			
		$cacheExpiry = self::safeFileGetContents($filePath . self::EXPIRY_SUFFIX);
		if ($cacheExpiry === false)
		{
			$cacheExpiry = filemtime($filePath) + $defaultExpiry;		
		}
		
		if ($cacheExpiry && $cacheExpiry <= time())
		{
			self::safeUnlink($filePath);
			self::safeUnlink($filePath . self::EXPIRY_SUFFIX);
			return false;
		}
		
		$result = self::safeFileGetContents($filePath);
		if ($result === false)
			return false;
		if ($this->serializeData)
			$result = @unserialize($result);
		return $result;
	}
		
	/* (non-PHPdoc)
	 * @see kBaseCacheWrapper::set()
	 */
	public function set($key, $var, $expiry = 0, $defaultExpiry = 0)
	{
		$filePath = $this->getFilePath($key);
		if ($this->serializeData)
			$var = serialize($var);
		
		self::createDirForPath($filePath);
		
		// write the expiry if non default
		if ($defaultExpiry != $expiry)
		{
			self::safeFilePutContents($filePath . self::EXPIRY_SUFFIX, $expiry ? time() + $expiry : 0);
		}
		
		self::safeFilePutContents($filePath, $var);
	}
	
	protected static function safeFilePutContents($filePath, $var)
	{
		// write to a temp file and then rename, so that the write will be atomic
		$tempFilePath = tempnam(dirname($filePath), basename($filePath));
		file_put_contents($tempFilePath, $var);
		rename($tempFilePath, $filePath);
	}
		
	// This function avoids the 'file does not exist' warning
	protected static function safeFileGetContents($filePath)
	{
		if (!file_exists($filePath))
		{
			return false;
		}
		return @file_get_contents($filePath);
	}

	protected static function safeUnlink($filePath)
	{
		if (!file_exists($filePath))
		{
			return;
		}
		@unlink($filePath);
	}
}
