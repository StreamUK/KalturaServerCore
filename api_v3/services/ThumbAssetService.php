<?php

/**
 * Retrieve information and invoke actions on Thumb Asset
 *
 * @service thumbAsset
 * @package api
 * @subpackage services
 */
class ThumbAssetService extends KalturaAssetService
{
	protected function kalturaNetworkAllowed($actionName)
	{
		if(
			$actionName == 'get' ||
			$actionName == 'list' ||
			$actionName == 'getByEntryId' ||
			$actionName == 'getUrl' ||
			$actionName == 'getWebPlayableByEntryId' ||
			$actionName == 'generateByEntryId' ||
			$actionName == 'regenerate'
			)
		{
			$this->partnerGroup .= ',0';
			return true;
		}
			
		return parent::kalturaNetworkAllowed($actionName);
	}
	
    /**
     * Add thumbnail asset
     *
     * @action add
     * @param string $entryId
     * @param KalturaThumbAsset $thumbAsset
     * @return KalturaThumbAsset
     * @throws KalturaErrors::ENTRY_ID_NOT_FOUND
     * @throws KalturaErrors::THUMB_ASSET_ALREADY_EXISTS
	 * @throws KalturaErrors::UPLOAD_TOKEN_INVALID_STATUS_FOR_ADD_ENTRY
	 * @throws KalturaErrors::UPLOADED_FILE_NOT_FOUND_BY_TOKEN
	 * @throws KalturaErrors::RECORDED_WEBCAM_FILE_NOT_FOUND
	 * @throws KalturaErrors::THUMB_ASSET_ID_NOT_FOUND
	 * @throws KalturaErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 * @throws KalturaErrors::RESOURCE_TYPE_NOT_SUPPORTED
     */
    function addAction($entryId, KalturaThumbAsset $thumbAsset)
    {
    	$dbEntry = entryPeer::retrieveByPK($entryId);
    	if(!$dbEntry || $dbEntry->getType() != KalturaEntryType::MEDIA_CLIP || !in_array($dbEntry->getMediaType(), array(KalturaMediaType::VIDEO, KalturaMediaType::AUDIO)))
    		throw new KalturaAPIException(KalturaErrors::ENTRY_ID_NOT_FOUND, $entryId);
    	
		$this->checkIfUserAllowedToUpdateEntry($dbEntry);
		
    	if($thumbAsset->thumbParamsId)
    	{
    		$dbThumbAsset = assetPeer::retrieveByEntryIdAndParams($entryId, $thumbAsset->thumbParamsId);
    		if($dbThumbAsset)
    			throw new KalturaAPIException(KalturaErrors::THUMB_ASSET_ALREADY_EXISTS, $dbThumbAsset->getId(), $thumbAsset->thumbParamsId);
    	}
    	
    	$dbThumbAsset = new thumbAsset();
    	$dbThumbAsset = $thumbAsset->toInsertableObject($dbThumbAsset);
    	
		$dbThumbAsset->setEntryId($entryId);
		$dbThumbAsset->setPartnerId($dbEntry->getPartnerId());
		$dbThumbAsset->setStatus(thumbAsset::FLAVOR_ASSET_STATUS_QUEUED);
		$dbThumbAsset->save();

		$thumbAsset = new KalturaThumbAsset();
		$thumbAsset->fromObject($dbThumbAsset);
		return $thumbAsset;
    }
    
    /**
     * Update content of thumbnail asset
     *
     * @action setContent
     * @param string $id
     * @param KalturaContentResource $contentResource
     * @return KalturaThumbAsset
     * @throws KalturaErrors::THUMB_ASSET_ID_NOT_FOUND
	 * @throws KalturaErrors::UPLOAD_TOKEN_INVALID_STATUS_FOR_ADD_ENTRY
	 * @throws KalturaErrors::UPLOADED_FILE_NOT_FOUND_BY_TOKEN
	 * @throws KalturaErrors::RECORDED_WEBCAM_FILE_NOT_FOUND
	 * @throws KalturaErrors::THUMB_ASSET_ID_NOT_FOUND
	 * @throws KalturaErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 * @throws KalturaErrors::RESOURCE_TYPE_NOT_SUPPORTED 
     */
    function setContentAction($id, KalturaContentResource $contentResource)
    {
   		$dbThumbAsset = assetPeer::retrieveById($id);
   		if (!$dbThumbAsset || !($dbThumbAsset instanceof thumbAsset))
   			throw new KalturaAPIException(KalturaErrors::THUMB_ASSET_ID_NOT_FOUND, $id);
    	
		$dbEntry = $dbThumbAsset->getentry();
		if (!$dbEntry)
			throw new KalturaAPIException(KalturaErrors::ENTRY_ID_NOT_FOUND, $dbThumbAsset->getEntryId());
			
		$this->checkIfUserAllowedToUpdateEntry($dbEntry);
		
   		$previousStatus = $dbThumbAsset->getStatus();
		$contentResource->validateEntry($dbThumbAsset->getentry());
		$kContentResource = $contentResource->toObject();
    	$this->attachContentResource($dbThumbAsset, $kContentResource);
		$contentResource->entryHandled($dbThumbAsset->getentry());
		kEventsManager::raiseEvent(new kObjectDataChangedEvent($dbThumbAsset));
		
    	$newStatuses = array(
    		thumbAsset::FLAVOR_ASSET_STATUS_READY,
    		thumbAsset::FLAVOR_ASSET_STATUS_VALIDATING,
    		thumbAsset::FLAVOR_ASSET_STATUS_TEMP,
    	);
    	
    	if($previousStatus == thumbAsset::FLAVOR_ASSET_STATUS_QUEUED && in_array($dbThumbAsset->getStatus(), $newStatuses))
   			kEventsManager::raiseEvent(new kObjectAddedEvent($dbThumbAsset));
   		
		$thumbAssetsCount = assetPeer::countThumbnailsByEntryId($dbThumbAsset->getEntryId());
		
		$defaultThumbKey = $dbEntry->getSyncKey(entry::FILE_SYNC_ENTRY_SUB_TYPE_THUMB);
    		
 		//If the thums has the default tag or the entry is in no content and this is the first thumb
		if(
			$dbThumbAsset->hasTag(thumbParams::TAG_DEFAULT_THUMB) 
			|| 
			(
				$dbEntry->getStatus() == KalturaEntryStatus::NO_CONTENT 
				&& $thumbAssetsCount == 1 
				&& !kFileSyncUtils::fileSync_exists($defaultThumbKey)
			)
		)
		{
			$this->setAsDefaultAction($dbThumbAsset->getId());
		}
		
		$thumbAsset = new KalturaThumbAsset();
		$thumbAsset->fromObject($dbThumbAsset);
		return $thumbAsset;
    }
	
    /**
     * Update thumbnail asset
     *
     * @action update
     * @param string $id
     * @param KalturaThumbAsset $thumbAsset
     * @return KalturaThumbAsset
     * @throws KalturaErrors::ENTRY_ID_NOT_FOUND
     */
    function updateAction($id, KalturaThumbAsset $thumbAsset)
    {
		$dbThumbAsset = assetPeer::retrieveById($id);
		if (!$dbThumbAsset || !($dbThumbAsset instanceof thumbAsset))
			throw new KalturaAPIException(KalturaErrors::THUMB_ASSET_ID_NOT_FOUND, $id);
    	
		$dbEntry = $dbThumbAsset->getentry();
		if (!$dbEntry)
			throw new KalturaAPIException(KalturaErrors::ENTRY_ID_NOT_FOUND, $dbThumbAsset->getEntryId());
			
		$this->checkIfUserAllowedToUpdateEntry($dbEntry);
		
    	$dbThumbAsset = $thumbAsset->toUpdatableObject($dbThumbAsset);
   		$dbThumbAsset->save();
		
		if($dbThumbAsset->hasTag(thumbParams::TAG_DEFAULT_THUMB))
			$this->setAsDefaultAction($dbThumbAsset->getId());
			
		$thumbAsset = new KalturaThumbAsset();
		$thumbAsset->fromObject($dbThumbAsset);
		return $thumbAsset;
    }
    
	/**
	 * @param thumbAsset $thumbAsset
	 * @param string $fullPath
	 * @param bool $copyOnly
	 */
	protected function attachFile(thumbAsset $thumbAsset, $fullPath, $copyOnly = false)
	{
		$ext = pathinfo($fullPath, PATHINFO_EXTENSION);
		
		$thumbAsset->incrementVersion();
		$thumbAsset->setFileExt($ext);
		$thumbAsset->setSize(filesize($fullPath));
		$thumbAsset->save();
		
		$syncKey = $thumbAsset->getSyncKey(thumbAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		
		try {
			kFileSyncUtils::moveFromFile($fullPath, $syncKey, true, $copyOnly);
		}
		catch (Exception $e) {
			
			if($thumbAsset->getStatus() == thumbAsset::FLAVOR_ASSET_STATUS_QUEUED || $thumbAsset->getStatus() == thumbAsset::FLAVOR_ASSET_STATUS_NOT_APPLICABLE)
			{
				$thumbAsset->setDescription($e->getMessage());
				$thumbAsset->setStatus(thumbAsset::FLAVOR_ASSET_STATUS_ERROR);
				$thumbAsset->save();
			}												
			throw $e;
		}

		$finalPath = kFileSyncUtils::getLocalFilePathForKey($syncKey);
		list($width, $height, $type, $attr) = getimagesize($finalPath);
		
		$thumbAsset->setWidth($width);
		$thumbAsset->setHeight($height);
		$thumbAsset->setSize(filesize($finalPath));
		
		$thumbAsset->setStatus(thumbAsset::FLAVOR_ASSET_STATUS_READY);
		$thumbAsset->save();
	}
    
	/**
	 * @param thumbAsset $thumbAsset
	 * @param string $url
	 */
	protected function attachUrl(thumbAsset $thumbAsset, $url)
	{
    	$fullPath = myContentStorage::getFSUploadsPath() . '/' . $thumbAsset->getId() . '.jpg';
		if (kFile::downloadUrlToFile($url, $fullPath))
			return $this->attachFile($thumbAsset, $fullPath);
			
		if($thumbAsset->getStatus() == thumbAsset::FLAVOR_ASSET_STATUS_QUEUED || $thumbAsset->getStatus() == thumbAsset::FLAVOR_ASSET_STATUS_NOT_APPLICABLE)
		{
			$thumbAsset->setDescription("Failed downloading file[$url]");
			$thumbAsset->setStatus(thumbAsset::FLAVOR_ASSET_STATUS_ERROR);
			$thumbAsset->save();
		}
		
		throw new KalturaAPIException(KalturaErrors::THUMB_ASSET_DOWNLOAD_FAILED, $url);
    }
    
	/**
	 * @param thumbAsset $thumbAsset
	 * @param kUrlResource $contentResource
	 */
	protected function attachUrlResource(thumbAsset $thumbAsset, kUrlResource $contentResource)
	{
    	$this->attachUrl($thumbAsset, $contentResource->getUrl());
    }
    
	/**
	 * @param thumbAsset $thumbAsset
	 * @param kLocalFileResource $contentResource
	 */
	protected function attachLocalFileResource(thumbAsset $thumbAsset, kLocalFileResource $contentResource)
	{
		if($contentResource->getIsReady())
			return $this->attachFile($thumbAsset, $contentResource->getLocalFilePath(), $contentResource->getKeepOriginalFile());
			
		$thumbAsset->setStatus(asset::FLAVOR_ASSET_STATUS_IMPORTING);
		$thumbAsset->save();
		
		$contentResource->attachCreatedObject($thumbAsset);
    }
    
	/**
	 * @param thumbAsset $thumbAsset
	 * @param FileSyncKey $srcSyncKey
	 */
	protected function attachFileSync(thumbAsset $thumbAsset, FileSyncKey $srcSyncKey)
	{
		$thumbAsset->incrementVersion();
		$thumbAsset->save();
		
        $newSyncKey = $thumbAsset->getSyncKey(thumbAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
        kFileSyncUtils::createSyncFileLinkForKey($newSyncKey, $srcSyncKey);
                
		$finalPath = kFileSyncUtils::getLocalFilePathForKey($newSyncKey);
		list($width, $height, $type, $attr) = getimagesize($finalPath);
		
		$thumbAsset->setWidth($width);
		$thumbAsset->setHeight($height);
		$thumbAsset->setSize(filesize($finalPath));
		
		$thumbAsset->setStatus(thumbAsset::FLAVOR_ASSET_STATUS_READY);
		$thumbAsset->save();
    }
    
	/**
	 * @param thumbAsset $thumbAsset
	 * @param kFileSyncResource $contentResource
	 */
	protected function attachFileSyncResource(thumbAsset $thumbAsset, kFileSyncResource $contentResource)
	{
    	$syncable = kFileSyncObjectManager::retrieveObject($contentResource->getFileSyncObjectType(), $contentResource->getObjectId());
    	$srcSyncKey = $syncable->getSyncKey($contentResource->getObjectSubType(), $contentResource->getVersion());
    	
        return $this->attachFileSync($thumbAsset, $srcSyncKey);
    }
    
	/**
	 * @param thumbAsset $thumbAsset
	 * @param IRemoteStorageResource $contentResource
	 * @throws KalturaErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 */
	protected function attachRemoteStorageResource(thumbAsset $thumbAsset, IRemoteStorageResource $contentResource)
	{
		$resources = $contentResource->getResources();
		
		$thumbAsset->setFileExt($contentResource->getFileExt());
        $thumbAsset->incrementVersion();
		$thumbAsset->setStatus(thumbAsset::FLAVOR_ASSET_STATUS_READY);
        $thumbAsset->save();
        	
        $syncKey = $thumbAsset->getSyncKey(thumbAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		foreach($resources as $currentResource)
		{
			$storageProfile = StorageProfilePeer::retrieveByPK($currentResource->getStorageProfileId());
			$fileSync = kFileSyncUtils::createReadyExternalSyncFileForKey($syncKey, $currentResource->getUrl(), $storageProfile);
		}
    }
    
    
	/**
	 * @param thumbAsset $thumbAsset
	 * @param kContentResource $contentResource
	 * @throws KalturaErrors::UPLOAD_TOKEN_INVALID_STATUS_FOR_ADD_ENTRY
	 * @throws KalturaErrors::UPLOADED_FILE_NOT_FOUND_BY_TOKEN
	 * @throws KalturaErrors::RECORDED_WEBCAM_FILE_NOT_FOUND
	 * @throws KalturaErrors::THUMB_ASSET_ID_NOT_FOUND
	 * @throws KalturaErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 * @throws KalturaErrors::RESOURCE_TYPE_NOT_SUPPORTED
	 */
	protected function attachContentResource(thumbAsset $thumbAsset, kContentResource $contentResource)
	{
    	switch($contentResource->getType())
    	{
			case 'kUrlResource':
				return $this->attachUrlResource($thumbAsset, $contentResource);
				
			case 'kLocalFileResource':
				return $this->attachLocalFileResource($thumbAsset, $contentResource);
				
			case 'kFileSyncResource':
				return $this->attachFileSyncResource($thumbAsset, $contentResource);
				
			case 'kRemoteStorageResource':
			case 'kRemoteStorageResources':
				return $this->attachRemoteStorageResource($thumbAsset, $contentResource);
				
			default:
				$msg = "Resource of type [" . get_class($contentResource) . "] is not supported";
				KalturaLog::err($msg);
				
				if($thumbAsset->getStatus() == thumbAsset::FLAVOR_ASSET_STATUS_QUEUED || $thumbAsset->getStatus() == thumbAsset::FLAVOR_ASSET_STATUS_NOT_APPLICABLE)
				{
					$thumbAsset->setDescription($msg);
					$thumbAsset->setStatus(asset::FLAVOR_ASSET_STATUS_ERROR);
					$thumbAsset->save();
				}
				
				throw new KalturaAPIException(KalturaErrors::RESOURCE_TYPE_NOT_SUPPORTED, get_class($contentResource));
    	}
    }
    
    
	/**
	 * Serves thumbnail by entry id and thumnail params id
	 *  
	 * @action serveByEntryId
	 * @param string $entryId
	 * @param int $thumbParamId if not set, default thumbnail will be used.
	 * @return file
	 * 
	 * @throws KalturaErrors::THUMB_ASSET_IS_NOT_READY
	 * @throws KalturaErrors::THUMB_ASSET_PARAMS_ID_NOT_FOUND
	 * @throws KalturaErrors::ENTRY_ID_NOT_FOUND
	 */
	public function serveByEntryIdAction($entryId, $thumbParamId = null)
	{
		$entry = entryPeer::retrieveByPK($entryId);
		if (!$entry)
			throw new KalturaAPIException(KalturaErrors::ENTRY_ID_NOT_FOUND, $entryId);

		$fileName = $entry->getId() . '.jpg';
		
		if(is_null($thumbParamId))
			return $this->serveAsset($entry, $fileName);
		
		$thumbAsset = assetPeer::retrieveByEntryIdAndParams($entryId, $thumbParamId);
		if(!$thumbAsset)
			throw new KalturaAPIException(KalturaErrors::THUMB_ASSET_PARAMS_ID_NOT_FOUND, $thumbParamId);
		
		return $this->serveAsset($thumbAsset, $fileName);
	}

	/**
	 * Serves thumbnail by its id
	 *  
	 * @action serve
	 * @param string $thumbAssetId
	 * @return file
	 *  
	 * @throws KalturaErrors::THUMB_ASSET_IS_NOT_READY
	 * @throws KalturaErrors::THUMB_ASSET_ID_NOT_FOUND
	 */
	public function serveAction($thumbAssetId)
	{
		$thumbAsset = assetPeer::retrieveById($thumbAssetId);
		if (!$thumbAsset || !($thumbAsset instanceof thumbAsset))
			throw new KalturaAPIException(KalturaErrors::THUMB_ASSET_ID_NOT_FOUND, $thumbAssetId);

		$ext = $thumbAsset->getFileExt();
		if(is_null($ext))
			$ext = 'jpg';
			
		$fileName = $thumbAsset->getEntryId()."_" . $thumbAsset->getId() . ".$ext";
		
		return $this->serveAsset($thumbAsset, $fileName);
	}
	
	/**
	 * Tags the thumbnail as DEFAULT_THUMB and removes that tag from all other thumbnail assets of the entry.
	 * Create a new file sync link on the entry thumbnail that points to the thumbnail asset file sync.
	 *  
	 * @action setAsDefault
	 * @param string $thumbAssetId
	 * @throws KalturaErrors::THUMB_ASSET_ID_NOT_FOUND
	 */
	public function setAsDefaultAction($thumbAssetId)
	{
		$thumbAsset = assetPeer::retrieveById($thumbAssetId);
		if (!$thumbAsset || !($thumbAsset instanceof thumbAsset))
			throw new KalturaAPIException(KalturaErrors::THUMB_ASSET_ID_NOT_FOUND, $thumbAssetId);
		
		$entry = $thumbAsset->getentry();
		if (!$entry)
			throw new KalturaAPIException(KalturaErrors::ENTRY_ID_NOT_FOUND, $thumbAsset->getEntryId());
									
		$this->checkIfUserAllowedToUpdateEntry($entry);
		
		$entryKuserId = $entry->getKuserId();
		$thisKuserId = $this->getKuser()->getId();
		$isNotAdmin = !kCurrentContext::$ks_object->isAdmin();
		
		KalturaLog::debug("entryKuserId [$entryKuserId], thisKuserId [$thisKuserId], isNotAdmin [$isNotAdmin ]");

		if(!$entry || ($isNotAdmin && !is_null($entryKuserId) && $entryKuserId != $thisKuserId))  
			throw new KalturaAPIException(KalturaErrors::ENTRY_ID_NOT_FOUND, $thumbAsset->getEntryId());
			
		$entryThumbAssets = assetPeer::retrieveThumbnailsByEntryId($thumbAsset->getEntryId());
		foreach($entryThumbAssets as $entryThumbAsset)
		{
			if($entryThumbAsset->getId() == $thumbAsset->getId())
				continue;
				
			if(!$entryThumbAsset->hasTag(thumbParams::TAG_DEFAULT_THUMB))
				continue;
				
			$entryThumbAsset->removeTags(array(thumbParams::TAG_DEFAULT_THUMB));
			$entryThumbAsset->save();
		}
		
		if(!$thumbAsset->hasTag(thumbParams::TAG_DEFAULT_THUMB))
		{
			$thumbAsset->addTags(array(thumbParams::TAG_DEFAULT_THUMB));
			$thumbAsset->save();
		}
		
		$entry->setThumbnail(".jpg");
		$entry->setCreateThumb(false);
		$entry->save();
		
		$thumbSyncKey = $thumbAsset->getSyncKey(thumbAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		$entrySyncKey = $entry->getSyncKey(entry::FILE_SYNC_ENTRY_SUB_TYPE_THUMB);
		kFileSyncUtils::createSyncFileLinkForKey($entrySyncKey, $thumbSyncKey);
	}

	/**
	 * @action generateByEntryId
	 * @param string $entryId
	 * @param int $destThumbParamsId indicate the id of the ThumbParams to be generate this thumbnail by
	 * @return KalturaThumbAsset
	 * 
	 * @throws KalturaErrors::ENTRY_ID_NOT_FOUND
	 * @throws KalturaErrors::ENTRY_TYPE_NOT_SUPPORTED
	 * @throws KalturaErrors::ENTRY_MEDIA_TYPE_NOT_SUPPORTED
	 * @throws KalturaErrors::THUMB_ASSET_PARAMS_ID_NOT_FOUND
	 * @throws KalturaErrors::INVALID_ENTRY_STATUS
	 * @throws KalturaErrors::THUMB_ASSET_IS_NOT_READY
	 */
	public function generateByEntryIdAction($entryId, $destThumbParamsId)
	{
		$entry = entryPeer::retrieveByPK($entryId);
		if(!$entry)
			throw new KalturaAPIException(KalturaErrors::ENTRY_ID_NOT_FOUND, $entryId);
			
		if ($entry->getType() != entryType::MEDIA_CLIP)
			throw new KalturaAPIException(KalturaErrors::ENTRY_TYPE_NOT_SUPPORTED, $entry->getType());
		if ($entry->getMediaType() != entry::ENTRY_MEDIA_TYPE_VIDEO)
			throw new KalturaAPIException(KalturaErrors::ENTRY_MEDIA_TYPE_NOT_SUPPORTED, $entry->getMediaType());
						
		$this->checkIfUserAllowedToUpdateEntry($entry);
			
		$validStatuses = array(
			entryStatus::ERROR_CONVERTING,
			entryStatus::PRECONVERT,
			entryStatus::READY,
		);
		
		if (!in_array($entry->getStatus(), $validStatuses))
			throw new KalturaAPIException(KalturaErrors::INVALID_ENTRY_STATUS);
			
		$destThumbParams = assetParamsPeer::retrieveByPK($destThumbParamsId);
		if(!$destThumbParams)
			throw new KalturaAPIException(KalturaErrors::THUMB_ASSET_PARAMS_ID_NOT_FOUND, $destThumbParamsId);

		$dbThumbAsset = kBusinessPreConvertDL::decideThumbGenerate($entry, $destThumbParams);
		if(!$dbThumbAsset)
			return null;
			
		$thumbAsset = new KalturaThumbAsset();
		$thumbAsset->fromObject($dbThumbAsset);
		return $thumbAsset;
	}

	/**
	 * @action generate
	 * @param string $entryId
	 * @param KalturaThumbParams $thumbParams
	 * @param string $sourceAssetId id of the source asset (flavor or thumbnail) to be used as source for the thumbnail generation
	 * @return KalturaThumbAsset
	 * 
	 * @throws KalturaErrors::ENTRY_ID_NOT_FOUND
	 * @throws KalturaErrors::ENTRY_TYPE_NOT_SUPPORTED
	 * @throws KalturaErrors::ENTRY_MEDIA_TYPE_NOT_SUPPORTED
	 * @throws KalturaErrors::THUMB_ASSET_PARAMS_ID_NOT_FOUND
	 * @throws KalturaErrors::INVALID_ENTRY_STATUS
	 * @throws KalturaErrors::THUMB_ASSET_IS_NOT_READY
	 */
	public function generateAction($entryId, KalturaThumbParams $thumbParams, $sourceAssetId = null)
	{
		$entry = entryPeer::retrieveByPK($entryId);
		if(!$entry)
			throw new KalturaAPIException(KalturaErrors::ENTRY_ID_NOT_FOUND, $entryId);
			
		if ($entry->getType() != entryType::MEDIA_CLIP)
			throw new KalturaAPIException(KalturaErrors::ENTRY_TYPE_NOT_SUPPORTED, $entry->getType());
			
		if ($entry->getMediaType() != entry::ENTRY_MEDIA_TYPE_VIDEO)
			throw new KalturaAPIException(KalturaErrors::ENTRY_MEDIA_TYPE_NOT_SUPPORTED, $entry->getMediaType());
			
		$this->checkIfUserAllowedToUpdateEntry($entry);
		
		$validStatuses = array(
			entryStatus::ERROR_CONVERTING,
			entryStatus::PRECONVERT,
			entryStatus::READY,
		);
		
		if (!in_array($entry->getStatus(), $validStatuses))
			throw new KalturaAPIException(KalturaErrors::INVALID_ENTRY_STATUS);
			
		$destThumbParams = new thumbParams();
		$thumbParams->toUpdatableObject($destThumbParams);

		$dbThumbAsset = kBusinessPreConvertDL::decideThumbGenerate($entry, $destThumbParams, null, $sourceAssetId, true);
		if(!$dbThumbAsset)
			return null;
			
		$thumbAsset = new KalturaThumbAsset();
		$thumbAsset->fromObject($dbThumbAsset);
		return $thumbAsset;
	}

	/**
	 * @action regenerate
	 * @param string $thumbAssetId
	 * @return KalturaThumbAsset
	 * 
	 * @throws KalturaErrors::THUMB_ASSET_ID_NOT_FOUND
	 * @throws KalturaErrors::ENTRY_TYPE_NOT_SUPPORTED
	 * @throws KalturaErrors::ENTRY_MEDIA_TYPE_NOT_SUPPORTED
	 * @throws KalturaErrors::THUMB_ASSET_PARAMS_ID_NOT_FOUND
	 * @throws KalturaErrors::INVALID_ENTRY_STATUS
	 */
	public function regenerateAction($thumbAssetId)
	{
		$thumbAsset = assetPeer::retrieveById($thumbAssetId);
		if (!$thumbAsset || !($thumbAsset instanceof thumbAsset))
			throw new KalturaAPIException(KalturaErrors::THUMB_ASSET_ID_NOT_FOUND, $thumbAssetId);
			
		if(is_null($thumbAsset->getFlavorParamsId()))
			throw new KalturaAPIException(KalturaErrors::THUMB_ASSET_PARAMS_ID_NOT_FOUND, null);
			
		$destThumbParams = assetParamsPeer::retrieveByPK($thumbAsset->getFlavorParamsId());
		if(!$destThumbParams)
			throw new KalturaAPIException(KalturaErrors::THUMB_ASSET_PARAMS_ID_NOT_FOUND, $thumbAsset->getFlavorParamsId());
			
		$entry = $thumbAsset->getentry();
		if ($entry->getType() != entryType::MEDIA_CLIP)
			throw new KalturaAPIException(KalturaErrors::ENTRY_TYPE_NOT_SUPPORTED, $entry->getType());
		if ($entry->getMediaType() != entry::ENTRY_MEDIA_TYPE_VIDEO)
			throw new KalturaAPIException(KalturaErrors::ENTRY_MEDIA_TYPE_NOT_SUPPORTED, $entry->getMediaType());
						
		$this->checkIfUserAllowedToUpdateEntry($entry);
			
		$validStatuses = array(
			entryStatus::ERROR_CONVERTING,
			entryStatus::PRECONVERT,
			entryStatus::READY,
		);
		
		if (!in_array($entry->getStatus(), $validStatuses))
			throw new KalturaAPIException(KalturaErrors::INVALID_ENTRY_STATUS);

		$dbThumbAsset = kBusinessPreConvertDL::decideThumbGenerate($entry, $destThumbParams);
		if(!$dbThumbAsset)
			return null;
			
		$thumbAsset = new KalturaThumbAsset();
		$thumbAsset->fromObject($dbThumbAsset);
		return $thumbAsset;
	}
	
	/**
	 * @action get
	 * @param string $thumbAssetId
	 * @return KalturaThumbAsset
	 * 
	 * @throws KalturaErrors::THUMB_ASSET_ID_NOT_FOUND
	 */
	public function getAction($thumbAssetId)
	{
		$thumbAssetsDb = assetPeer::retrieveById($thumbAssetId);
		if (!$thumbAssetsDb || !($thumbAssetsDb instanceof thumbAsset))
			throw new KalturaAPIException(KalturaErrors::THUMB_ASSET_ID_NOT_FOUND, $thumbAssetId);
		
		$thumbAssets = new KalturaThumbAsset();
		$thumbAssets->fromObject($thumbAssetsDb);
		return $thumbAssets;
	}
	
	/**
	 * @action getByEntryId
	 * @param string $entryId
	 * @return KalturaThumbAssetArray
	 * 
	 * @throws KalturaErrors::ENTRY_ID_NOT_FOUND
	 * @deprecated Use thumbAsset.list instead
	 */
	public function getByEntryIdAction($entryId)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry)
			throw new KalturaAPIException(KalturaErrors::ENTRY_ID_NOT_FOUND, $entryId);
			
		// get the thumb assets for this entry
		$c = new Criteria();
		$c->add(assetPeer::ENTRY_ID, $entryId);
		
		$thumbTypes = KalturaPluginManager::getExtendedTypes(assetPeer::OM_CLASS, assetType::THUMBNAIL);
		$c->add(assetPeer::TYPE, $thumbTypes, Criteria::IN);
		
		$thumbAssetsDb = assetPeer::doSelect($c);
		$thumbAssets = KalturaThumbAssetArray::fromDbArray($thumbAssetsDb);
		return $thumbAssets;
	}
	
	/**
	 * List Thumbnail Assets by filter and pager
	 * 
	 * @action list
	 * @param KalturaAssetFilter $filter
	 * @param KalturaFilterPager $pager
	 * @return KalturaThumbAssetListResponse
	 */
	function listAction(KalturaAssetFilter $filter = null, KalturaFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new KalturaAssetFilter();

		if (!$pager)
			$pager = new KalturaFilterPager();
			
		$thumbAssetFilter = new AssetFilter();
		
		$filter->toObject($thumbAssetFilter);

		$c = new Criteria();
		$thumbAssetFilter->attachToCriteria($c);
		
		$thumbTypes = KalturaPluginManager::getExtendedTypes(assetPeer::OM_CLASS, assetType::THUMBNAIL);
		$c->add(assetPeer::TYPE, $thumbTypes, Criteria::IN);
		
		$totalCount = assetPeer::doCount($c);
		
		$pager->attachToCriteria($c);
		$dbList = assetPeer::doSelect($c);
		
		$list = KalturaThumbAssetArray::fromDbArray($dbList);
		$response = new KalturaThumbAssetListResponse();
		$response->objects = $list;
		$response->totalCount = $totalCount;
		return $response;    
	}
	
	/**
	 * @action addFromUrl
	 * @param string $entryId
	 * @param string $url
	 * @return KalturaThumbAsset
	 * 
	 * @deprecated use thumbAsset.add and thumbAsset.setContent instead
	 */
	public function addFromUrlAction($entryId, $url)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry)
			throw new KalturaAPIException(KalturaErrors::ENTRY_ID_NOT_FOUND, $entryId);
			
		$this->checkIfUserAllowedToUpdateEntry($dbEntry);
		
		$ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
		
		$dbThumbAsset = new thumbAsset();
		$dbThumbAsset->setPartnerId($dbEntry->getPartnerId());
		$dbThumbAsset->setEntryId($dbEntry->getId());
		$dbThumbAsset->setStatus(thumbAsset::FLAVOR_ASSET_STATUS_QUEUED);
		$dbThumbAsset->setFileExt($ext);
		$dbThumbAsset->incrementVersion();
		$dbThumbAsset->save();
		
		$syncKey = $dbThumbAsset->getSyncKey(thumbAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		kFileSyncUtils::file_put_contents($syncKey, file_get_contents($url));
		
		$finalPath = kFileSyncUtils::getLocalFilePathForKey($syncKey);
		list($width, $height, $type, $attr) = getimagesize($finalPath);
		
		$dbThumbAsset->setWidth($width);
		$dbThumbAsset->setHeight($height);
		$dbThumbAsset->setSize(filesize($finalPath));
		$dbThumbAsset->setStatus(thumbAsset::FLAVOR_ASSET_STATUS_READY);
		$dbThumbAsset->save();
		
		$thumbAssets = new KalturaThumbAsset();
		$thumbAssets->fromObject($dbThumbAsset);
		return $thumbAssets;
	}
	
	/**
	 * @action addFromImage
	 * @param string $entryId
	 * @param file $fileData
	 * @return KalturaThumbAsset
	 * 
	 * @throws KalturaErrors::ENTRY_ID_NOT_FOUND
	 */
	public function addFromImageAction($entryId, $fileData)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry)
			throw new KalturaAPIException(KalturaErrors::ENTRY_ID_NOT_FOUND, $entryId);
			
		$this->checkIfUserAllowedToUpdateEntry($dbEntry);
		
		$ext = pathinfo($fileData["name"], PATHINFO_EXTENSION);
		
		$dbThumbAsset = new thumbAsset();
		$dbThumbAsset->setPartnerId($dbEntry->getPartnerId());
		$dbThumbAsset->setEntryId($dbEntry->getId());
		$dbThumbAsset->setStatus(thumbAsset::FLAVOR_ASSET_STATUS_QUEUED);
		$dbThumbAsset->setFileExt($ext);
		$dbThumbAsset->incrementVersion();
		$dbThumbAsset->save();
		
		$syncKey = $dbThumbAsset->getSyncKey(thumbAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		kFileSyncUtils::moveFromFile($fileData["tmp_name"], $syncKey);
		
		$finalPath = kFileSyncUtils::getLocalFilePathForKey($syncKey);
		list($width, $height, $type, $attr) = getimagesize($finalPath);
		
		$dbThumbAsset->setWidth($width);
		$dbThumbAsset->setHeight($height);
		$dbThumbAsset->setSize(filesize($finalPath));
		$dbThumbAsset->setStatus(thumbAsset::FLAVOR_ASSET_STATUS_READY);
		$dbThumbAsset->save();
		
		$dbEntryThumbs = assetPeer::retrieveThumbnailsByEntryId($entryId);
    		
 		//If the thums has the default tag or the entry is in no content and this is the first thumb
		if( $dbThumbAsset->hasTag(thumbParams::TAG_DEFAULT_THUMB) || 
		  	($dbEntry->getStatus() == KalturaEntryStatus::NO_CONTENT && count($dbEntryThumbs) == 1)
		  )
				$this->setAsDefaultAction($dbThumbAsset->getId());
			
		$thumbAssets = new KalturaThumbAsset();
		$thumbAssets->fromObject($dbThumbAsset);
		return $thumbAssets;
	}
	
	/**
	 * @action delete
	 * @param string $thumbAssetId
	 * 
	 * @throws KalturaErrors::THUMB_ASSET_ID_NOT_FOUND
	 */
	public function deleteAction($thumbAssetId)
	{
		$thumbAssetDb = assetPeer::retrieveById($thumbAssetId);
		if (!$thumbAssetDb || !($thumbAssetDb instanceof thumbAsset))
			throw new KalturaAPIException(KalturaErrors::THUMB_ASSET_ID_NOT_FOUND, $thumbAssetId);
	
		if($thumbAssetDb->hasTag(thumbParams::TAG_DEFAULT_THUMB))
			throw new KalturaAPIException(KalturaErrors::THUMB_ASSET_IS_DEFAULT, $thumbAssetId);
		
		$entry = $thumbAssetDb->getEntry();
		if (!$entry)
			throw new KalturaAPIException(KalturaErrors::ENTRY_ID_NOT_FOUND, $thumbAssetDb->getEntryId());
			
		$this->checkIfUserAllowedToUpdateEntry($entry);
		
		$thumbAssetDb->setStatus(thumbAsset::FLAVOR_ASSET_STATUS_DELETED);
		$thumbAssetDb->setDeletedAt(time());
		$thumbAssetDb->save();
	}
	
	/**
	 * Get download URL for the asset
	 * 
	 * @action getUrl
	 * @param string $id
	 * @param int $storageId
	 * @return string
	 * @throws KalturaErrors::THUMB_ASSET_ID_NOT_FOUND
	 * @throws KalturaErrors::THUMB_ASSET_IS_NOT_READY
	 */
	public function getUrlAction($id, $storageId = null)
	{
		$assetDb = assetPeer::retrieveById($id);
		if (!$assetDb || !($assetDb instanceof thumbAsset))
			throw new KalturaAPIException(KalturaErrors::THUMB_ASSET_ID_NOT_FOUND, $id);

		if ($assetDb->getStatus() != asset::FLAVOR_ASSET_STATUS_READY)
			throw new KalturaAPIException(KalturaErrors::THUMB_ASSET_IS_NOT_READY);

		if($storageId)
			return $assetDb->getExternalUrl($storageId);
			
		return $assetDb->getDownloadUrl(true);
	}
	
	/**
	 * Get remote storage existing paths for the asset
	 * 
	 * @action getRemotePaths
	 * @param string $id
	 * @return KalturaRemotePathListResponse
	 * @throws KalturaErrors::THUMB_ASSET_ID_NOT_FOUND
	 * @throws KalturaErrors::THUMB_ASSET_IS_NOT_READY
	 */
	public function getRemotePathsAction($id)
	{
		$assetDb = assetPeer::retrieveById($id);
		if (!$assetDb || !($assetDb instanceof thumbAsset))
			throw new KalturaAPIException(KalturaErrors::THUMB_ASSET_ID_NOT_FOUND, $id);

		if ($assetDb->getStatus() != asset::ASSET_STATUS_READY)
			throw new KalturaAPIException(KalturaErrors::THUMB_ASSET_IS_NOT_READY);

		$c = new Criteria();
		$c->add(FileSyncPeer::OBJECT_TYPE, FileSyncObjectType::ASSET);
		$c->add(FileSyncPeer::OBJECT_SUB_TYPE, asset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);
		$c->add(FileSyncPeer::OBJECT_ID, $id);
		$c->add(FileSyncPeer::VERSION, $assetDb->getVersion());
		$c->add(FileSyncPeer::PARTNER_ID, $assetDb->getPartnerId());
		$c->add(FileSyncPeer::STATUS, FileSync::FILE_SYNC_STATUS_READY);
		$c->add(FileSyncPeer::FILE_TYPE, FileSync::FILE_SYNC_FILE_TYPE_URL);
		$fileSyncs = FileSyncPeer::doSelect($c);
			
		$listResponse = new KalturaRemotePathListResponse();
		$listResponse->objects = KalturaRemotePathArray::fromFileSyncArray($fileSyncs);
		$listResponse->totalCount = count($listResponse->objects);
		return $listResponse;
	}
}
