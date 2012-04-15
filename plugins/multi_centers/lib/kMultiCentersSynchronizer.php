<?php
class kMultiCentersSynchronizer implements kObjectAddedEventConsumer
{
	public function getEntryId(FileSync $fileSync)
	{
		if($fileSync->getObjectType() == FileSyncObjectType::ENTRY)
			return $fileSync->getObjectId();
			
		if($fileSync->getObjectType() == FileSyncObjectType::BATCHJOB)
		{
			$job = BatchJobPeer::retrieveByPK($fileSync->getObjectId());
			if($job)
				return $job->getEntryId();
		}
			
		if($fileSync->getObjectType() == FileSyncObjectType::FLAVOR_ASSET)
		{
			$flavor = assetPeer::retrieveById($fileSync->getObjectId());
			if($flavor)
				return $flavor->getEntryId();
		}
			
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see kObjectAddedEventConsumer::shouldConsumeAddedEvent()
	 */
	public function shouldConsumeAddedEvent(BaseObject $object)
	{
		if(
			$object instanceof FileSync 
			&&
			$object->getStatus() == FileSync::FILE_SYNC_STATUS_PENDING 
			&&
			$object->getFileType() == FileSync::FILE_SYNC_FILE_TYPE_FILE 
			&&
			$object->getDc() != kDataCenterMgr::getCurrentDcId()
		)
			return true;
		
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see kObjectAddedEventConsumer::objectAdded()
	 */
	public function objectAdded(BaseObject $object, BatchJob $raisedJob = null)
	{
		$c = new Criteria();
		$c->addAnd(FileSyncPeer::OBJECT_ID, $object->getObjectId());
		$c->addAnd(FileSyncPeer::VERSION, $object->getVersion());
		$c->addAnd(FileSyncPeer::OBJECT_TYPE, $object->getObjectType());
		$c->addAnd(FileSyncPeer::OBJECT_SUB_TYPE, $object->getObjectSubType());
		$c->addAnd(FileSyncPeer::ORIGINAL, '1');
		$original_filesync = FileSyncPeer::doSelectOne($c);
		if (!$original_filesync) {
			KalturaLog::err('Original filesync not found for object_id['.$object->getObjectId().'] version['.$object->getVersion().'] type['.$object->getObjectType().'] subtype['.$object->getObjectSubType().']');
			return true;
		}
		
		$entryId = $this->getEntryId($object);
		
		$sourceFileUrl = $original_filesync->getExternalUrl($entryId);
		if (!$sourceFileUrl) {
			KalturaLog::err('External URL not found for filesync id [' . $object->getId() . ']');
			return true;
		}				
		
		$job = kMultiCentersManager::addFileSyncImportJob($entryId, $object, $sourceFileUrl, $raisedJob, $original_filesync->getFileSize());
		
		$job->save();
		
		return true;
	}
}
