<?php
/**
 * @package plugins.captionSearch
 * @subpackage lib
 */
class kCaptionSearchFlowManager implements kObjectDataChangedEventConsumer
{
	/* (non-PHPdoc)
	 * @see kObjectDataChangedEventConsumer::shouldConsumeDataChangedEvent()
	 */
	public function shouldConsumeDataChangedEvent(BaseObject $object, $previousVersion = null)
	{
		if(class_exists('CaptionAsset') && $object instanceof CaptionAsset)
			return CaptionSearchPlugin::isAllowedPartner($object->getPartnerId());
			
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see kObjectDataChangedEventConsumer::objectDataChanged()
	 */
	public function objectDataChanged(BaseObject $object, $previousVersion = null, BatchJob $raisedJob = null)
	{
		/* @var $object CaptionAsset */
		
		try
		{
			self::addParseCaptionAssetJob($object, $raisedJob);
		}
		catch (kCoreException $kce)
		{
			KalturaLog::err("Cannot create parse caption job, error [" . $kce->getMessage() . "]");
		}
		
		// updated in the entry in the indexing server
		$entry = $object->getentry();
		if($entry)
		{
			$entry->setUpdatedAt(time());
			$entry->save();
		}
		
		return true;
	}
	
	public function addParseCaptionAssetJob(CaptionAsset $captionAsset, BatchJob $parentJob = null)
	{
		$syncKey = $captionAsset->getSyncKey(asset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);
		$fileSync = kFileSyncUtils::getReadyInternalFileSyncForKey($syncKey);
		if(!$fileSync)
		{
			throw new kCoreException("File sync not found: $syncKey", kCoreException::FILE_NOT_FOUND);
		}
		
		$jobData = new kParseCaptionAssetJobData();
		$jobData->setCaptionAssetId($captionAsset->getId());
			
 		
		$batchJob = null;
		if($parentJob)
		{
			$batchJob = $parentJob->createChild();
		}
		else
		{
			$batchJob = new BatchJob();
			$batchJob->setEntryId($captionAsset->getEntryId());
			$batchJob->setPartnerId($captionAsset->getPartnerId());
		}
			
		return kJobsManager::addJob($batchJob, $jobData, CaptionSearchPlugin::getBatchJobTypeCoreValue(CaptionSearchBatchJobType::PARSE_CAPTION_ASSET));
	}
}