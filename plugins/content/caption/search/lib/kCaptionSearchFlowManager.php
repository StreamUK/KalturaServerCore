<?php
/**
 * @package plugins.captionSearch
 * @subpackage lib
 */
class kCaptionSearchFlowManager implements kObjectDataChangedEventConsumer, kObjectDeletedEventConsumer, kObjectCreatedEventConsumer
{
	/* (non-PHPdoc)
	 * @see kObjectCreatedEventConsumer::shouldConsumeCreatedEvent
	 */
	public function shouldConsumeCreatedEvent(BaseObject $object)
	{
		if(class_exists('CaptionAsset') && $object instanceof CaptionAsset 
				&& CaptionSearchPlugin::isAllowedPartner($object->getPartnerId()
					&& $object->getStatus() == CaptionAsset::ASSET_STATUS_READY)){
						return true;
					}
					
		return false;
	}

	/* (non-PHPdoc)
	 * @see kObjectCreatedEventConsumer::objectCreated
	 */
	public function objectCreated(BaseObject $object)
	{
		return self::objectDataChanged($object);
	}

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
	
	/**
	 * @param CaptionAsset $captionAsset
	 * @param BatchJob $parentJob
	 * @throws kCoreException FILE_NOT_FOUND
	 * @return BatchJob
	 */
	public function addParseCaptionAssetJob(CaptionAsset $captionAsset, BatchJob $parentJob = null)
	{
		$syncKey = $captionAsset->getSyncKey(asset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);
		$fileSync = kFileSyncUtils::getReadyInternalFileSyncForKey($syncKey);
		if(!$fileSync)
		{
			if(!PermissionPeer::isValidForPartner(CaptionPermissionName::IMPORT_REMOTE_CAPTION_FOR_INDEXING, $captionAsset->getPartnerId()))
				throw new kCoreException("File sync not found: $syncKey", kCoreException::FILE_NOT_FOUND);
			
			$fileSync = kFileSyncUtils::getReadyExternalFileSyncForKey($syncKey);
			if(!$fileSync)
				throw new kCoreException("File sync not found: $syncKey", kCoreException::FILE_NOT_FOUND);
			
	    	$fullPath = myContentStorage::getFSUploadsPath() . '/' . $captionAsset->getId() . '.tmp';
			if(!kFile::downloadUrlToFile($fileSync->getExternalUrl($captionAsset->getEntryId()), $fullPath))
				throw new kCoreException("File sync not found: $syncKey", kCoreException::FILE_NOT_FOUND);
			
			kFileSyncUtils::moveFromFile($fullPath, $syncKey, true, false, true);
		}
		
		$jobData = new kParseCaptionAssetJobData();
		$jobData->setCaptionAssetId($captionAsset->getId());
			
 		$batchJobType = CaptionSearchPlugin::getBatchJobTypeCoreValue(CaptionSearchBatchJobType::PARSE_CAPTION_ASSET);
		$batchJob = null;
		if($parentJob)
		{
			$batchJob = $parentJob->createChild($batchJobType);
		}
		else
		{
			$batchJob = new BatchJob();
			$batchJob->setEntryId($captionAsset->getEntryId());
			$batchJob->setPartnerId($captionAsset->getPartnerId());
		}
			
		$batchJob->setObjectId($captionAsset->getId());
		$batchJob->setObjectType(BatchJobObjectType::ASSET);
		return kJobsManager::addJob($batchJob, $jobData, $batchJobType);
	}

	/* (non-PHPdoc)
	 * @see kObjectDeletedEventConsumer::objectDeleted()
	 */
	public function objectDeleted(BaseObject $object, BatchJob $raisedJob = null)
	{
		/* @var $object CaptionAsset */
		
		// delete them one by one to raise the erased event
		$captionAssetItems = CaptionAssetItemPeer::retrieveByAssetId($object->getId());
		foreach($captionAssetItems as $captionAssetItem)
		{
			/* @var $captionAssetItem CaptionAssetItem */
			$captionAssetItem->delete();
		}
		
		// updates entry on order to trigger reindexing
		$entry = $object->getentry();
		$entry->setUpdatedAt(time());
		$entry->save();
	}

	/* (non-PHPdoc)
	 * @see kObjectDeletedEventConsumer::shouldConsumeDeletedEvent()
	 */
	public function shouldConsumeDeletedEvent(BaseObject $object)
	{
		if($object instanceof CaptionAsset)
			return true;
			
		return false;
	}
}