<?php
/**
 * @package api
 * @subpackage objects
 */
class KalturaConvartableJobData extends KalturaJobData
{
	/**
	 * @var string
	 */
	public $srcFileSyncLocalPath;
	
	/**
	 * The translated path as used by the scheduler
	 * @var string
	 */
	public $actualSrcFileSyncLocalPath;
	
	/**
	 * @var string
	 */
	public $srcFileSyncRemoteUrl;
	
	/**
	 * @var int
	 */
	public $engineVersion;
	
	/**
	 * @var int
	 */
	public $flavorParamsOutputId;
	
	/**
	 * @var KalturaFlavorParamsOutput
	 */
	public $flavorParamsOutput;
	
	/**
	 * @var int
	 */
	public $mediaInfoId;
	
	/**
	 * @var int
	 */
	public $currentOperationSet;
	
	/**
	 * @var int
	 */
	public $currentOperationIndex;
	
	private static $map_between_objects = array
	(
		"srcFileSyncLocalPath" ,
		"actualSrcFileSyncLocalPath" ,
		"srcFileSyncRemoteUrl" ,
		"engineVersion" ,
		"mediaInfoId" ,
		"flavorParamsOutputId" ,
		"currentOperationSet" ,
		"currentOperationIndex" ,
	);


	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	    
	public function toObject(  $dbConvartableJobData = null, $props_to_skip = array()) 
	{
		if(is_null($dbConvartableJobData))
			$dbConvartableJobData = new kConvartableJobData();
			
		return parent::toObject($dbConvartableJobData, $props_to_skip);
	}
}
