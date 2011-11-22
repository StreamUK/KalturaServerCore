<?php
/**
 * @package api
 * @subpackage objects
 */
class KalturaBulkUpload extends KalturaObject
{
	/**
	 * @var int
	 */
	public $id;
	
	/**
	 * @var string
	 */
	public $uploadedBy;
	
	/**
	 * @var string
	 */
	public $uploadedByUserId;
	
	/**
	 * @var int
	 */
	public $uploadedOn;
	
	/**
	 * @var int
	 */
	public $numOfEntries;
	
	/**
	 * @var KalturaBatchJobStatus
	 */
	public $status;
	
	/**
	 * @var string
	 */
	public $logFileUrl;
	
	/**
	 * @var string;
	 * @deprecated
	 */
	public $csvFileUrl;
	
	/**
	 * @var string;
	 */
	public $bulkFileUrl;
	
	/**
	 * @var KalturaBulkUploadType;
	 */
	public $bulkUploadType;
	
	
	
	/**
	 * @var KalturaBulkUploadResultArray;
	 */
	public $results;
	
	/**
	 * @var string
	 */
	public $error;
	
	/**
	 * @var KalturaBatchJobErrorTypes
	 */
	public $errorType;
	
	/**
	 * @var int
	 */
	public $errorNumber;
	
	/**
	 * @var string
	 */
	public $fileName;
	
	/**
	 * @var string
	 */
	public $description;
	
	public function fromObject($batchJob)
	{
		/* @var $batchJob BatchJob */
		
		if($batchJob->getJobType() != BatchJobType::BULKUPLOAD)
			throw new Exception("Bulk upload object can be initialized from bulk upload job only");
		
		$this->id = $batchJob->getId();
		$this->uploadedOn = $batchJob->getCreatedAt(null);
		$this->status = $batchJob->getStatus();
		$this->error = $batchJob->getMessage();
		$this->description = $batchJob->getDescription();
		$this->bulkUploadType = kPluginableEnumsManager::coreToApi('BulkUploadType', $batchJob->getJobSubType());
		
		$this->logFileUrl = requestUtils::getHost() . "/api_v3/service/bulkUpload/action/serveLog/id/{$batchJob->getId()}/ks/" . kCurrentContext::$ks;
//		$this->logFileUrl = requestUtils::getHost() . "/index.php/extwidget/bulkuploadfile/id/{$batchJob->getId()}/pid/{$batchJob->getPartnerId()}/type/log";
		$this->bulkFileUrl = requestUtils::getHost() . "/api_v3/service/bulkUpload/action/serve/id/{$batchJob->getId()}/ks/" . kCurrentContext::$ks;
//		$this->bulkFileUrl = requestUtils::getCdnHost() . "/index.php/extwidget/bulkuploadfile/id/{$batchJob->getId()}/pid/{$batchJob->getPartnerId()}/type/$type";
		$this->csvFileUrl = $this->bulkFileUrl;
					
		$jobData = $batchJob->getData();
		if($jobData instanceof kBulkUploadJobData)
		{
			$this->uploadedBy = $jobData->getUploadedBy();
			$this->uploadedByUserId = $jobData->getUserId();
			$this->numOfEntries = $jobData->getNumOfEntries();
			$this->fileName = $jobData->getFileName();
		}
		
//		$results = BulkUploadResultPeer::retrieveByBulkUploadId($this->id);
//		$this->results = KalturaBulkUploadResultArray::fromBulkUploadResultArray($results);
	}
}