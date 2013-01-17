<?php

/**
 * Live Stream service lets you manage live stream channels
 *
 * @service liveStream
 * @package api
 * @subpackage services
 */
class LiveStreamService extends KalturaEntryService
{
	const DEFAULT_BITRATE = 300;
	const DEFAULT_WIDTH = 320;
	const DEFAULT_HEIGHT = 240;
	const ISLIVE_ACTION_CACHE_EXPIRY = 30;
	const HLS_LIVE_STREAM_CONTENT_TYPE = 'application/vnd.apple.mpegurl';
	
	/**
	 * Adds new live stream entry.
	 * The entry will be queued for provision.
	 * 
	 * @action add
	 * @param KalturaLiveStreamAdminEntry $liveStreamEntry Live stream entry metadata  
	 * @param KalturaSourceType $sourceType  Live stream source type
	 * @return KalturaLiveStreamAdminEntry The new live stream entry
	 * 
	 * @throws KalturaErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL
	 */
	function addAction(KalturaLiveStreamAdminEntry $liveStreamEntry, $sourceType = null)
	{
		//TODO: allow sourceType that belongs to LIVE entries only - same for mediaType
		if ($sourceType) {
			$liveStreamEntry->sourceType = $sourceType;
		}
		else {
			// default sourceType is AKAMAI_LIVE
			$liveStreamEntry->sourceType = kPluginableEnumsManager::coreToApi('EntrySourceType', $this->getPartner()->getDefaultLiveStreamEntrySourceType());
		}
		
		// if the given password is empty, generate a random 8-character string as the new password
		if ( ($liveStreamEntry->streamPassword == null) || (strlen(trim($liveStreamEntry->streamPassword)) <= 0) )
		{
			$tempPassword = sha1(md5(uniqid(rand(), true)));
			$liveStreamEntry->streamPassword = substr($tempPassword, rand(0,strlen($tempPassword)-8), 8);		
		}
		
		// if no bitrate given, add default
		if(is_null($liveStreamEntry->bitrates) || !$liveStreamEntry->bitrates->count)
		{
			$liveStreamBitrate = new KalturaLiveStreamBitrate();
			$liveStreamBitrate->bitrate = self::DEFAULT_BITRATE;
			$liveStreamBitrate->width = self::DEFAULT_WIDTH;
			$liveStreamBitrate->height = self::DEFAULT_HEIGHT;
			
			$liveStreamEntry->bitrates = new KalturaLiveStreamBitrateArray();
			$liveStreamEntry->bitrates[] = $liveStreamBitrate;
		}
		else 
		{
			$bitrates = new KalturaLiveStreamBitrateArray();
			foreach($liveStreamEntry->bitrates as $bitrate)
			{		
				if(is_null($bitrate->bitrate))	$bitrate->bitrate = self::DEFAULT_BITRATE;
				if(is_null($bitrate->width))	$bitrate->bitrate = self::DEFAULT_WIDTH;
				if(is_null($bitrate->height))	$bitrate->bitrate = self::DEFAULT_HEIGHT;
				$bitrates[] = $bitrate;
			}
			$liveStreamEntry->bitrates = $bitrates;
		}
		
		$dbEntry = $this->insertLiveStreamEntry($liveStreamEntry);
		
		myNotificationMgr::createNotification( kNotificationJobData::NOTIFICATION_TYPE_ENTRY_ADD, $dbEntry, $this->getPartnerId(), null, null, null, $dbEntry->getId());

		$liveStreamEntry->fromObject($dbEntry);
		return $liveStreamEntry;
	}

	
	/**
	 * Get live stream entry by ID.
	 * 
	 * @action get
	 * @param string $entryId Live stream entry id
	 * @param int $version Desired version of the data
	 * @return KalturaLiveStreamEntry The requested live stream entry
	 * 
	 * @throws KalturaErrors::ENTRY_ID_NOT_FOUND
	 */
	function getAction($entryId, $version = -1)
	{
		return $this->getEntry($entryId, $version, KalturaEntryType::LIVE_STREAM);
	}

	
	/**
	 * Update live stream entry. Only the properties that were set will be updated.
	 * 
	 * @action update
	 * @param string $entryId Live stream entry id to update
	 * @param KalturaLiveStreamAdminEntry $liveStreamEntry Live stream entry metadata to update
	 * @return KalturaLiveStreamAdminEntry The updated live stream entry
	 * 
	 * @throws KalturaErrors::ENTRY_ID_NOT_FOUND
	 * @validateUser entry entryId edit
	 */
	function updateAction($entryId, KalturaLiveStreamAdminEntry $liveStreamEntry)
	{
		return $this->updateEntry($entryId, $liveStreamEntry, KalturaEntryType::LIVE_STREAM);
	}

	/**
	 * Delete a live stream entry.
	 *
	 * @action delete
	 * @param string $entryId Live stream entry id to delete
	 * 
 	 * @throws KalturaErrors::ENTRY_ID_NOT_FOUND
 	 * @validateUser entry entryId edit
	 */
	function deleteAction($entryId)
	{
		$this->deleteEntry($entryId, KalturaEntryType::LIVE_STREAM);
	}
	
	/**
	 * List live stream entries by filter with paging support.
	 * 
	 * @action list
     * @param KalturaLiveStreamEntryFilter $filter live stream entry filter
	 * @param KalturaFilterPager $pager Pager
	 * @return KalturaLiveStreamListResponse Wrapper for array of live stream entries and total count
	 */
	function listAction(KalturaLiveStreamEntryFilter $filter = null, KalturaFilterPager $pager = null)
	{
	    if (!$filter)
			$filter = new KalturaLiveStreamEntryFilter();
			
	    $filter->typeEqual = KalturaEntryType::LIVE_STREAM;
	    list($list, $totalCount) = parent::listEntriesByFilter($filter, $pager);
	    
	    $newList = KalturaLiveStreamEntryArray::fromEntryArray($list);
		$response = new KalturaLiveStreamListResponse();
		$response->objects = $newList;
		$response->totalCount = $totalCount;
		return $response;
	}
	


	/**
	 * Update live stream entry thumbnail using a raw jpeg file
	 * 
	 * @action updateOfflineThumbnailJpeg
	 * @param string $entryId live stream entry id
	 * @param file $fileData Jpeg file data
	 * @return KalturaLiveStreamEntry The live stream entry
	 * 
	 * @throws KalturaErrors::ENTRY_ID_NOT_FOUND
	 * @throws KalturaErrors::PERMISSION_DENIED_TO_UPDATE_ENTRY
	 */
	function updateOfflineThumbnailJpegAction($entryId, $fileData)
	{
		return parent::updateThumbnailJpegForEntry($entryId, $fileData, KalturaEntryType::LIVE_STREAM, entry::FILE_SYNC_ENTRY_SUB_TYPE_OFFLINE_THUMB);
	}
	
	/**
	 * Update entry thumbnail using url
	 * 
	 * @action updateOfflineThumbnailFromUrl
	 * @param string $entryId live stream entry id
	 * @param string $url file url
	 * @return KalturaLiveStreamEntry The live stream entry
	 * 
	 * @throws KalturaErrors::ENTRY_ID_NOT_FOUND
	 * @throws KalturaErrors::PERMISSION_DENIED_TO_UPDATE_ENTRY
	 */
	function updateOfflineThumbnailFromUrlAction($entryId, $url)
	{
		return parent::updateThumbnailForEntryFromUrl($entryId, $url, KalturaEntryType::LIVE_STREAM, entry::FILE_SYNC_ENTRY_SUB_TYPE_OFFLINE_THUMB);
	}
	
	private function insertLiveStreamEntry(KalturaLiveStreamAdminEntry $liveStreamEntry)
	{
		// first validate the input object
		$liveStreamEntry->validatePropertyNotNull("mediaType");
		$liveStreamEntry->validatePropertyNotNull("sourceType");
		$liveStreamEntry->validatePropertyNotNull("encodingIP1");
		$liveStreamEntry->validatePropertyNotNull("encodingIP2");
		$liveStreamEntry->validatePropertyNotNull("streamPassword");
		
		// create a default name if none was given
		if (!$liveStreamEntry->name)
			$liveStreamEntry->name = $this->getPartnerId().'_'.time();
		
		// first copy all the properties to the db entry, then we'll check for security stuff
		$dbEntry = $liveStreamEntry->toObject(new entry());

		$this->checkAndSetValidUserInsert($liveStreamEntry, $dbEntry);
		$this->checkAdminOnlyInsertProperties($liveStreamEntry);
		$this->validateAccessControlId($liveStreamEntry);
		$this->validateEntryScheduleDates($liveStreamEntry, $dbEntry);
		
		$dbEntry->setPartnerId($this->getPartnerId());
		$dbEntry->setSubpId($this->getPartnerId() * 100);
		$dbEntry->setKuserId($this->getKuser()->getId());
		$dbEntry->setCreatorKuserId($this->getKuser()->getId());
		$dbEntry->setStatus(entryStatus::IMPORT);
		
		$te = new TrackEntry();
		$te->setEntryId( $dbEntry->getId() );
		$te->setTrackEventTypeId( TrackEntry::TRACK_ENTRY_EVENT_TYPE_ADD_ENTRY );
		$te->setDescription(  __METHOD__ . ":" . __LINE__ . "::ENTRY_MEDIA_SOURCE_AKAMAI_LIVE" );
		TrackEntry::addTrackEntry( $te );
		
		//if type is manual don't create batch job, just change entry status to ready
		if ($liveStreamEntry->sourceType == KalturaSourceType::MANUAL_LIVE_STREAM)
		{
			$dbEntry->setStatus(entryStatus::READY);
			$dbEntry->save();
		}
		else
		{
			$dbEntry->save();
			kJobsManager::addProvisionProvideJob(null, $dbEntry);
		}
 			
		return $dbEntry;
	}	
	
	/**
	 * New action delivering the status of a live stream (on-air/offline) if it is possible
	 * @action isLive
	 * @param string $id ID of the live stream
	 * @param KalturaPlaybackProtocol $protocol protocol of the stream to test.
	 * @throws KalturaErrors::LIVE_STREAM_STATUS_CANNOT_BE_DETERMINED
	 * @throws KalturaErrors::INVALID_ENTRY_ID
	 * @return bool
	 */
	public function isLiveAction ($id, $protocol)
	{
		KalturaResponseCacher::setExpiry(self::ISLIVE_ACTION_CACHE_EXPIRY);
		kApiCache::disableConditionalCache();
		$liveStreamEntry = entryPeer::retrieveByPK($id);
		if (!$liveStreamEntry)
			throw new KalturaAPIException(KalturaErrors::INVALID_ENTRY_ID, $id);
		
		if ($liveStreamEntry)
		{
			switch ($protocol)
			{
				case KalturaPlaybackProtocol::HLS:
					KalturaLog::info('Determining status of live stream URL [' .$liveStreamEntry->getHlsStreamUrl(). ']');
					return $this->hlsUrlExistsRecursive($liveStreamEntry->getHlsStreamUrl());
					break;
				case KalturaPlaybackProtocol::AKAMAI_HDS:
					$config = kLiveStreamConfiguration::getSingleItemByPropertyValue($liveStreamEntry, "protocol", $protocol);
					if ($config)
					{
						KalturaLog::info('Determining status of live stream URL [' .$config->getUrl() . ']');
						return $this->hdsUrlExists($config->getUrl(). '?hdcore=' . kConf::get('hd_core_version'));
					}
					break;
			}
		}
		
		throw new KalturaAPIException(KalturaErrors::LIVE_STREAM_STATUS_CANNOT_BE_DETERMINED, $protocol);
	}
	
	
	
	/**
	 * Method checks whether the URL passed to it as a parameter returns a response.
	 * @param string $url
	 * @return string
	 */
	private function urlExists ($url, $contentTypeToReturn)
	{
		if (is_null($url)) 
			return false;  
		if (!function_exists('curl_init'))
		{
			KalturaLog::err('Unable to use util when php curl is not enabled');
			return false;  
		}
	    $ch = curl_init($url);  
	    curl_setopt($ch, CURLOPT_TIMEOUT, 5);  
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);  
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
	    $data = curl_exec($ch);  
	    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);  
	    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
	    curl_close($ch);  
	    if($data && $httpcode>=200 && $httpcode<300)
	    {
	        return $contentType == $contentTypeToReturn ? $data : true;
	    }  
	    else 
	        return false;  
	}	
	
	/**
	 * Recursive function which returns true/false depending whether the given URL returns a valid video eventually
	 * @param string $url
	 * @return bool
	 */
	private function hlsUrlExistsRecursive ($url)
	{
		$data = $this->urlExists($url, self::HLS_LIVE_STREAM_CONTENT_TYPE);
		if(is_bool($data))
			return $data;
		
		$lines = explode("#EXT-X-STREAM-INF:", trim($data));
		var_dump($lines);
		
		$result = false;
		foreach ($lines as $line)
		{
			if(!preg_match("/http.*/", array_shift($lines), $matches))
				continue;
			$streamUrl = $matches[0];
			
			$data = $this->urlExists($streamUrl, self::HLS_LIVE_STREAM_CONTENT_TYPE);
			if (!$data)
				continue;
				
			$segs = explode("#EXTINF:", $data);
			if(!preg_match("/http.*/", array_pop($segs), $matches))
				continue;
			
			$tsUrl = $matches[0];
			if ($this->urlExists($tsUrl ,self::HLS_LIVE_STREAM_CONTENT_TYPE))
				return true;
		}
			
		return false;
	}
	
	/**
	 * Function which returns true/false depending whether the given URL returns a live video
	 * @param string $url
	 * @return true
	 */
	private function hdsUrlExists ($url) 
	{
		$data = $this->urlExists($url, 'video/f4m');
		if (is_bool($data))
			return $data;
		
		$element = new KDOMDocument();
		$element->loadXML($data);
		$streamType = $element->getElementsByTagName('streamType')->item(0);
		if ($streamType->nodeValue == 'live')
			return true;
		
		return false;
	}
}