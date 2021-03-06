<?php

class KAsyncDropFolderContentProcessor extends KJobHandlerWorker
{
	/**
	 * @var KalturaDropFolderClientPlugin
	 */
	protected $dropFolderPlugin = null;
	
	/* (non-PHPdoc)
	 * @see KBatchBase::getType()
	 */
	public static function getType()
	{
		return KalturaBatchJobType::DROP_FOLDER_CONTENT_PROCESSOR;
	}
	
	/* (non-PHPdoc)
	 * @see KBatchBase::getJobType()
	 */
	public function getJobType()
	{
		return self::getType();
	}
	
	/* (non-PHPdoc)
	 * @see KJobHandlerWorker::exec()
	 */
	protected function exec(KalturaBatchJob $job)
	{
		try 
		{
			return $this->process($job, $job->data);
		}
		catch(kTemporaryException $e)
		{
			if($e->getResetJobExecutionAttempts())
				throw $e;
			return $this->closeJob($job, KalturaBatchJobErrorTypes::RUNTIME, $e->getCode(), "Error: " . $e->getMessage(), KalturaBatchJobStatus::FAILED);
		}
		catch(KalturaClientException $e)
		{
			return $this->closeJob($job, KalturaBatchJobErrorTypes::KALTURA_CLIENT, $e->getCode(), "Error: " . $e->getMessage(), KalturaBatchJobStatus::FAILED);
		}
	}

	protected function process(KalturaBatchJob $job, KalturaDropFolderContentProcessorJobData $data)
	{
		$job = $this->updateJob($job, "Start processing drop folder files [$data->dropFolderFileIds]", KalturaBatchJobStatus::QUEUED);
		$this->dropFolderPlugin = KalturaDropFolderClientPlugin::get($this->kClient);
		
		$this->impersonate($job->partnerId);
		
		switch ($data->contentMatchPolicy)
		{
			case KalturaDropFolderContentFileHandlerMatchPolicy::ADD_AS_NEW:
				$this->addAsNewContent($job, $data);
				break;
			
			case KalturaDropFolderContentFileHandlerMatchPolicy::MATCH_EXISTING_OR_KEEP_IN_FOLDER:
				$this->addAsExistingContent($job, $data);
				break;
				
			case KalturaDropFolderContentFileHandlerMatchPolicy::MATCH_EXISTING_OR_ADD_AS_NEW:
				$matchedEntry = $this->isEntryMatch($data);
				if($matchedEntry)
					$this->addAsExistingContent($job, $data, $matchedEntry);
				else
					 $this->addAsNewContent($job, $data);	
				break;			
			default:
				KalturaLog::err('No content match policy is defined for drop folder');
				throw new kApplicativeException(KalturaDropFolderErrorCode::CONTENT_MATCH_POLICY_UNDEFINED, 'No content match policy is defined for drop folder'); 
				break;
		}
		
		$this->unimpersonate();		
				
		return $this->closeJob($job, null, null, null, KalturaBatchJobStatus::FINISHED);
	}
		
	private function addAsNewContent(KalturaBatchJob $job, KalturaDropFolderContentProcessorJobData $data)
	{ 		
		$resource = $this->getIngestionResource($job, $data);
		$newEntry = new KalturaBaseEntry();
		$newEntry->conversionProfileId = $data->conversionProfileId;
		$newEntry->name = $data->parsedSlug;
		$newEntry->referenceId = $data->parsedSlug;
			
		$addedEntry = $this->kClient->baseEntry->add($newEntry, null);
		$addedEntry = $this->kClient->baseEntry->addContent($addedEntry->id, $resource);	
	}

	private function isEntryMatch(KalturaDropFolderContentProcessorJobData $data)
	{
		try 
		{
			$entryFilter = new KalturaBaseEntryFilter();
			$entryFilter->referenceIdEqual = $data->parsedSlug;
			$entryFilter->statusIn = KalturaEntryStatus::IMPORT.','.KalturaEntryStatus::PRECONVERT.','.KalturaEntryStatus::READY.','.KalturaEntryStatus::PENDING.','.KalturaEntryStatus::NO_CONTENT;		
			
			$entryPager = new KalturaFilterPager();
			$entryPager->pageSize = 1;
			$entryPager->pageIndex = 1;
			$entryList = $this->kClient->baseEntry->listAction($entryFilter, $entryPager);
			
			if (is_array($entryList->objects) && isset($entryList->objects[0]) ) 
			{
				$result = $entryList->objects[0];
				if ($result->referenceId === $data->parsedSlug) 
					return $result;
			}
			
			return false;			
		}
		catch (Exception $e)
		{
			KalturaLog::err('Failed to get entry by reference id: [$data->parsedSlug] - '. $e->getMessage() );
			return false;
		}
	}
	
	/**
	 * Match the current file to an existing entry and flavor according to the slug regex.
	 * Update the matched entry with the new file and all other relevant files from the drop folder, according to the ingestion profile.
	 *
	 */
	private function addAsExistingContent(KalturaBatchJob $job, KalturaDropFolderContentProcessorJobData $data, $matchedEntry = null)
	{	    
		// check for matching entry and flavor
		if(!$matchedEntry)
		{
			$matchedEntry = $this->isEntryMatch($data);
			if(!$matchedEntry)
			{
				$e = new kTemporaryException('No matching entry found', KalturaDropFolderFileErrorCode::FILE_NO_MATCH);
				if(($job->queueTime + $this->taskConfig->params->maxTimeBeforeFail) >= time())	
				{
					$e->setResetJobExecutionAttempts(true);
				}	
				throw $e;		
			}
		}	
		$resource = $this->getIngestionResource($job, $data);
		$this->kClient->media->cancelReplace($matchedEntry->id);
		$updatedEntry = $this->kClient->baseEntry->updateContent($matchedEntry->id, $resource, $data->conversionProfileId);
	}
	
	/**
	 * Retrieve all the relevant drop folder files according to the list of id's passed on the job data.
	 * Create resource object based on the conversion profile as an input to the ingestion API
	 * @param KalturaBatchJob $job
	 * @param KalturaDropFolderContentProcessorJobData $data
	 */
	private function getIngestionResource(KalturaBatchJob $job, KalturaDropFolderContentProcessorJobData $data)
	{
		$filter = new KalturaDropFolderFileFilter();
		$filter->idIn = $data->dropFolderFileIds;
		$dropFolderFiles = $this->dropFolderPlugin->dropFolderFile->listAction($filter); 
		
		$resource = null;
		if($dropFolderFiles->totalCount == 1 && is_null($dropFolderFiles->objects[0]->parsedFlavor)) //only source is ingested
		{
			$resource = new KalturaDropFolderFileResource();
			$resource->dropFolderFileId = $dropFolderFiles->objects[0]->id;			
		}
		else //ingest all the required flavors
		{			
			$fileToFlavorMap = array();
			foreach ($dropFolderFiles->objects as $dropFolderFile) 
			{
				$fileToFlavorMap[$dropFolderFile->parsedFlavor] = $dropFolderFile->id;			
			}
			
			$assetContainerArray = array();
		
			$assetParamsFilter = new KalturaConversionProfileAssetParamsFilter();
			$assetParamsFilter->conversionProfileIdEqual = $data->conversionProfileId;
			$assetParamsList = $this->kClient->conversionProfileAssetParams->listAction($assetParamsFilter);
			foreach ($assetParamsList->objects as $assetParams)
			{
				if(array_key_exists($assetParams->systemName, $fileToFlavorMap))
				{
					$assetContainer = new KalturaAssetParamsResourceContainer();
					$assetContainer->assetParamsId = $assetParams->assetParamsId;
					$assetContainer->resource = new KalturaDropFolderFileResource();
					$assetContainer->resource->dropFolderFileId = $fileToFlavorMap[$assetParams->systemName];
					$assetContainerArray[] = $assetContainer;				
				}			
			}		
			$resource = new KalturaAssetsParamsResourceContainers();
			$resource->resources = $assetContainerArray;
		}
		return $resource;		
	}
}
