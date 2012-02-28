<?php

/**
 * Bulk upload service is used to upload & manage bulk uploads using CSV files
 *
 * @service bulkUpload
 * @package api
 * @subpackage services
 */
class BulkUploadService extends KalturaBaseService
{
	const PARTNER_DEFAULT_CONVERSION_PROFILE_ID = -1;
	
	/**
	 * Add new bulk upload batch job
	 * Conversion profile id can be specified in the API or in the CSV file, the one in the CSV file will be stronger.
	 * If no conversion profile was specified, partner's default will be used
	 * 
	 * @action add
	 * @param int $conversionProfileId Convertion profile id to use for converting the current bulk (-1 to use partner's default)
	 * @param file $csvFileData bulk upload file
	 * @param KalturaBulkUploadType $bulkUploadType
	 * @param string $uploadedBy
	 * @return KalturaBulkUpload
	 */
	function addAction($conversionProfileId, $csvFileData, $bulkUploadType = null, $uploadedBy = null)
	{
		if($conversionProfileId == self::PARTNER_DEFAULT_CONVERSION_PROFILE_ID)
			$conversionProfileId = $this->getPartner()->getDefaultConversionProfileId();
			
		$conversionProfile = conversionProfile2Peer::retrieveByPK($conversionProfileId);
		if(!$conversionProfile)
			throw new KalturaAPIException(KalturaErrors::CONVERSION_PROFILE_ID_NOT_FOUND, $conversionProfileId);
		
		$coreBulkUploadType = kPluginableEnumsManager::apiToCore('BulkUploadType', $bulkUploadType);
		
		if(is_null($uploadedBy))
			$uploadedBy = $this->getKuser()->getPuserId();
		
		$dbJob = kJobsManager::addBulkUploadJob($csvFileData["tmp_name"], $csvFileData["name"], $this->getPartner(), $this->getKuser()->getPuserId(), $uploadedBy, $conversionProfileId, $coreBulkUploadType);
		
		$bulkUpload = new KalturaBulkUpload();
		$bulkUpload->fromObject($dbJob);
		return $bulkUpload;
	}
	
	/**
	 * Get bulk upload batch job by id
	 *
	 * @action get
	 * @param int $id
	 * @return KalturaBulkUpload
	 */
	function getAction($id)
	{
	    $c = new Criteria();
	    $c->addAnd(BatchJobPeer::ID, $id);
		$c->addAnd(BatchJobPeer::PARTNER_ID, $this->getPartnerId());
		$c->addAnd(BatchJobPeer::JOB_TYPE, BatchJobType::BULKUPLOAD);
		$batchJob = BatchJobPeer::doSelectOne($c);
		
		if (!$batchJob)
		    throw new KalturaAPIException(KalturaErrors::BULK_UPLOAD_NOT_FOUND, $id);
		    
		$ret = new KalturaBulkUpload();
		$ret->fromObject($batchJob);
		return $ret;
	}
	
	/**
	 * List bulk upload batch jobs
	 *
	 * @action list
	 * @param KalturaFilterPager $pager
	 * @return KalturaBulkUploadListResponse
	 */
	function listAction(KalturaFilterPager $pager = null)
	{
		myDbHelper::$use_alternative_con = myDbHelper::DB_HELPER_CONN_PROPEL2;

	    if (!$pager)
			$pager = new KalturaFilterPager();
			
	    $c = new Criteria();
		$c->addAnd(BatchJobPeer::PARTNER_ID, $this->getPartnerId());
		$c->addAnd(BatchJobPeer::JOB_TYPE, BatchJobType::BULKUPLOAD);
		
		$crit = $c->getNewCriterion(BatchJobPeer::ABORT, null);
		$critOr = $c->getNewCriterion(BatchJobPeer::ABORT, 0);
		$crit->addOr($critOr);
		$c->add($crit);
		
		$c->addDescendingOrderByColumn(BatchJobPeer::ID);
		
		$count = BatchJobPeer::doCount($c);
		$pager->attachToCriteria($c);
		$jobs = BatchJobPeer::doSelect($c);
		
		$response = new KalturaBulkUploadListResponse();
		$response->objects = KalturaBulkUploads::fromBatchJobArray($jobs);
		$response->totalCount = $count; 
		
		return $response;
	}
	
	
	
	
	/**
	 * serve action returan the original file.
	 * 
	 * @action serve
	 * @param int $id job id
	 * @return file
	 * 
	 */
	function serveAction($id)
	{
		$c = new Criteria();
		$c->addAnd(BatchJobPeer::ID, $id);
		$c->addAnd(BatchJobPeer::PARTNER_ID, $this->getPartnerId());
		$c->addAnd(BatchJobPeer::JOB_TYPE, BatchJobType::BULKUPLOAD);
		$batchJob = BatchJobPeer::doSelectOne($c);
		
		if (!$batchJob)
			throw new KalturaAPIException(KalturaErrors::BULK_UPLOAD_NOT_FOUND, $id);
			 
		KalturaLog::info("Batch job found for jobid [$id] bulk upload type [". $batchJob->getJobSubType() . "]");
		
		$syncKey = $batchJob->getSyncKey(BatchJob::FILE_SYNC_BATCHJOB_SUB_TYPE_BULKUPLOAD);
		list($fileSync, $local) = kFileSyncUtils::getReadyFileSyncForKey($syncKey, true, false);
		
		header("Content-Type: text/plain; charset=UTF-8");

		if($local)
		{
			$filePath = $fileSync->getFullPath();
			$mimeType = kFile::mimeType($filePath);
			kFile::dumpFile($filePath, $mimeType);
		}
		else
		{
			$remoteUrl = kDataCenterMgr::getRedirectExternalUrl($fileSync);
			KalturaLog::info("Redirecting to [$remoteUrl]");
			header("Location: $remoteUrl");
		}	
	}
	
	
	/**
	 * serveLog action returan the original file.
	 * 
	 * @action serveLog
	 * @param int $id job id
	 * @return file
	 * 
	 */
	function serveLogAction($id)
	{
		$c = new Criteria();
		$c->addAnd(BatchJobPeer::ID, $id);
		$c->addAnd(BatchJobPeer::PARTNER_ID, $this->getPartnerId());
		$c->addAnd(BatchJobPeer::JOB_TYPE, BatchJobType::BULKUPLOAD);
		$batchJob = BatchJobPeer::doSelectOne($c);
		
		if (!$batchJob)
			throw new KalturaAPIException(KalturaErrors::BULK_UPLOAD_NOT_FOUND, $id);
			 
		KalturaLog::info("Batch job found for jobid [$id] bulk upload type [". $batchJob->getJobSubType() . "]");
			
		$pluginInstances = KalturaPluginManager::getPluginInstances('IKalturaBulkUpload');
		foreach($pluginInstances as $pluginInstance)
		{
			/* @var $pluginInstance IKalturaBulkUpload */
			$pluginInstance->writeBulkUploadLogFile($batchJob);
		}	
	}
	
	/** 
	 * Aborts the bulk upload and all its child jobs
	 * 
	 * @action abort
	 * @param int $id job id
	 * @return KalturaBulkUpload
	 */
	function abortAction($id) 
	{
	    $c = new Criteria();
	    $c->addAnd(BatchJobPeer::ID, $id);
		$c->addAnd(BatchJobPeer::PARTNER_ID, $this->getPartnerId());
		$c->addAnd(BatchJobPeer::JOB_TYPE, BatchJobType::BULKUPLOAD);
		$batchJob = BatchJobPeer::doSelectOne($c);
		
		if (!$batchJob)
		    throw new KalturaAPIException(KalturaErrors::BULK_UPLOAD_NOT_FOUND, $id);
		
		if (!in_array($batchJob->getStatus(), BatchJobPeer::getClosedStatusList()))
		{
		    throw new KalturaAPIException(KalturaErrors::CANNOT_ABORT_BULKUPLOAD_UNFINISHED_JOB, $id);
		}
		    
		kJobsManager::abortJob($id, BatchJobType::BULKUPLOAD, true);
		
		$ret = new KalturaBulkUpload();
		$ret->fromObject($batchJob);
		return $ret;
	}
}
