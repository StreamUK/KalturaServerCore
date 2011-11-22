<?php
/**
 * @package api
 * @subpackage objects
 */
class KalturaBulkUploadResult extends KalturaObject
{
    /**
     * The id of the result
     * 
     * @var int
	 * @readonly
     */
    public $id;

	
	/**
	 * The id of the parent job
	 * 
     * @var int
     */
    public $bulkUploadJobId;


	
	/**
	 * The index of the line in the CSV
	 * 
     * @var int
     */
    public $lineIndex;


	
	/**
     * @var int
     */
    public $partnerId;


	
	/**
     * @var KalturaBulkUploadAction
     */
    public $action;


	
	/**
     * @var string
     */
    public $entryId;


	
	/**
     * @var string
     */
    public $objectId;


	
	/**
     * @var KalturaBulkUploadResultObjectType
     */
    public $bulkUploadResultObjectType;

	
	/**
     * @var int
     */
    public $entryStatus;


	
	/**
	 * The data as recieved in the csv
	 * 
     * @var string
     */
    public $rowData;


	
	/**
     * @var string
     */
    public $title;


	
	/**
     * @var string
     */
    public $description;


	
	/**
     * @var string
     */
    public $tags;


	
	/**
     * @var string
     */
    public $url;


	
	/**
     * @var string
     */
    public $contentType;


	
	/**
     * @var int
     */
    public $conversionProfileId;


	
	/**
     * @var int
     */
    public $accessControlProfileId;


	
	/**
     * @var string
     */
    public $category;


	
	/**
     * @var int
     */
    public $scheduleStartDate;


	
	/**
     * @var int
     */
    public $scheduleEndDate;


	
	/**
     * @var string
     */
    public $thumbnailUrl;


	
	/**
     * @var bool
     */
    public $thumbnailSaved;


	
	/**
     * @var string
     */
    public $partnerData;


	
	/**
     * @var string
     */
    public $errorDescription;


	
	/**
     * @var KalturaBulkUploadPluginDataArray
     */
    public $pluginsData;
    
    
    /**
     * @var string
     */
    public $sshPrivateKey;
    
    /**
     * @var string
     */
    public $sshPublicKey;
    
    /**
     * @var string
     */
    public $sshKeyPassphrase;
    
    
       
	private static $mapBetweenObjects = array
	(
		"id",
	    "bulkUploadJobId",
	    "lineIndex",
	    "partnerId",
	    "entryId",
		"action",
		"objectId",
		"bulkUploadResultObjectType" => "objectType",
		"entryStatus",
	    "rowData",
	    "title",
	    "description",
	    "tags",
	    "url",
	    "contentType",
	    "conversionProfileId",
	    "accessControlProfileId",
	    "category",
	    "scheduleStartDate",
	    "scheduleEndDate",
	    "thumbnailUrl",
		"thumbnailSaved",
	    "partnerData",
	    "errorDescription",
	    "sshPrivateKey",
	    "sshPublicKey",
	    "sshKeyPassphrase",
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
	
	public function toInsertableObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		$dbObject = parent::toInsertableObject(new BulkUploadResult(), $props_to_skip);
		$pluginsData = array();
		if($this->pluginsData && $this->pluginsData instanceof KalturaBulkUploadPluginDataArray)
		{
			foreach($this->pluginsData as $data)
			{
//				KalturaLog::debug("Plugins data item is " . get_class($data));
				if($data instanceof KalturaBulkUploadPluginData)
					$pluginsData[$data->field] = $data->value;
			}
		}
		KalturaLog::debug("Plugins data array:\n" . print_r($pluginsData, true));
		$dbObject->setPluginsData($pluginsData);
		
		return $dbObject;
	}
}