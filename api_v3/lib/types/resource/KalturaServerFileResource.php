<?php
/**
 * Used to ingest media file that is already accessible on the shared disc.
 * 
 * @package api
 * @subpackage objects
 */
class KalturaServerFileResource extends KalturaDataCenterContentResource 
{
	/**
	 * Full path to the local file 
	 * @var string
	 * @requiresPermission all
	 */
	public $localFilePath;

	private static $map_between_objects = array
	(
		'localFilePath',
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function validateEntry(entry $dbEntry)
	{
		parent::validateEntry($dbEntry);
    	$this->validatePropertyNotNull('localFilePath');
    	
    	if(!file_exists($this->localFilePath))
    		throw new KalturaAPIException(KalturaErrors::LOCAL_FILE_NOT_FOUND, $this->localFilePath);
	}

	public function toObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		if(!$object_to_fill)
			$object_to_fill = new kLocalFileResource();
			
		$object_to_fill->setKeepOriginalFile(true);
		return parent::toObject($object_to_fill, $props_to_skip);
	}
}