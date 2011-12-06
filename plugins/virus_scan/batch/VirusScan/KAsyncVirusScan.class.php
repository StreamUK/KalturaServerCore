<?php
require_once("bootstrap.php");
/**
 * Will scan for viruses on specified file  
 *
 * @package plugins.virusScan
 * @subpackage Scheduler
 */
class KAsyncVirusScan extends KBatchBase
{
	/* (non-PHPdoc)
	 * @see KBatchBase::getType()
	 */
	public static function getType()
	{
		return KalturaBatchJobType::VIRUS_SCAN;
	}
	
	/* (non-PHPdoc)
	 * @see KBatchBase::getJobType()
	 */
	public function getJobType()
	{
		return self::getType();
	}
	
	/* (non-PHPdoc)
	 * @see KBatchBase::exec()
	 */
	protected function exec(KalturaBatchJob $job)
	{
		return $this->scan($job, $job->data);
	}
	
	// TODO remove run, updateExclusiveJob and freeExclusiveJob
	
	public function run($jobs = null)
	{
		KalturaLog::info("Virus scan batch is running");
		
		if($this->taskConfig->isInitOnly())
			return $this->init();
		
		if(is_null($jobs))
		{
			$maxJobsEachRun = $this->taskConfig->maxJobsEachRun;
			if(!$maxJobsEachRun)
				$maxJobsEachRun = 1;
				
			$jobs = $this->kClient->virusScanBatch->getExclusiveVirusScanJobs($this->getExclusiveLockKey(), $this->taskConfig->maximumExecutionTime, $maxJobsEachRun, $this->getFilter());
		}
		
		KalturaLog::info(count($jobs) . " virus scan jobs to perform");
		
		if(! count($jobs) > 0)
		{
			KalturaLog::info("Queue size: 0 sent to scheduler");
			$this->saveSchedulerQueue(self::getType());
			return null;
		}
		
		foreach($jobs as &$job)
		{
			$job = $this->scan($job, $job->data);
		}	
		return $jobs;
	}
	
	protected function scan(KalturaBatchJob $job, KalturaVirusScanJobData $data)
	{
		KalturaLog::debug("scan($job->id)");
		
		try
		{
			$engine = VirusScanEngine::getEngine($job->jobSubType);
			if (!$engine)
			{
				KalturaLog::err('Cannot create VirusScanEngine of type ['.$job->jobSubType.']');
				$this->closeJob($job, KalturaBatchJobErrorTypes::APP, null, 'Error: Cannot create VirusScanEngine of type ['.$job->jobSubType.']', KalturaBatchJobStatus::FAILED);
				return $job;
			}
						
			// configure engine
			if (!$engine->config($this->taskConfig->params))
			{
				KalturaLog::err('Cannot configure VirusScanEngine of type ['.$job->jobSubType.']');
				$this->closeJob($job, KalturaBatchJobErrorTypes::APP, null, 'Error: Cannot configure VirusScanEngine of type ['.$job->jobSubType.']', KalturaBatchJobStatus::FAILED);
				return $job;
			}
			
			$cleanIfInfected = $data->virusFoundAction == KalturaVirusFoundAction::CLEAN_NONE || $data->virusFoundAction == KalturaVirusFoundAction::CLEAN_DELETE;
			$errorDescription = null;
			$output = null;
			
			// execute scan
			$data->scanResult = $engine->execute($data->srcFilePath, $cleanIfInfected, $output, $errorDescription);
			
			if (!$output) {
				KalturaLog::notice('Virus scan engine ['.get_class($engine).'] did not return any log for file ['.$data->srcFilePath.']');
				$output = 'Virus scan engine ['.get_class($engine).'] did not return any log';
			}
			$this->kClient->batch->logConversion($data->flavorAssetId, $output);

			// check scan results
			switch ($data->scanResult)
			{
				case KalturaVirusScanJobResult::SCAN_ERROR:
					$this->closeJob($job, KalturaBatchJobErrorTypes::APP, null, "Error: " . $errorDescription, KalturaBatchJobStatus::RETRY, $data);
					break;
				
				case KalturaVirusScanJobResult::FILE_IS_CLEAN:
					$this->closeJob($job, null, null, "Scan finished - file was found to be clean", KalturaBatchJobStatus::FINISHED, $data);
					break;
				
				case KalturaVirusScanJobResult::FILE_WAS_CLEANED:
					$this->closeJob($job, null, null, "Scan finished - file was infected but scan has managed to clean it", KalturaBatchJobStatus::FINISHED, $data);
					break;
					
				case KalturaVirusScanJobResult::FILE_INFECTED:
				
					$this->closeJob($job, null, null, "File was found INFECTED and wasn't cleaned!", KalturaBatchJobStatus::FINISHED, $data);
					break;
					
				default:
					$data->scanResult = KalturaVirusScanJobResult::SCAN_ERROR;
					$this->closeJob($job, KalturaBatchJobErrorTypes::APP, null, "Error: Emtpy scan result returned", KalturaBatchJobStatus::RETRY, $data);
					break;
			}
			
		}
		catch(Exception $ex)
		{
			$data->scanResult = KalturaVirusScanJobResult::SCAN_ERROR;
			$this->closeJob($job, KalturaBatchJobErrorTypes::RUNTIME, $ex->getCode(), "Error: " . $ex->getMessage(), KalturaBatchJobStatus::FAILED, $data);
		}
		return $job;
	}
	
	
	protected function updateExclusiveJob($jobId, KalturaBatchJob $job, $entryStatus = null)
	{
		return $this->kClient->virusScanBatch->updateExclusiveVirusScanJob($jobId, $this->getExclusiveLockKey(), $job, $entryStatus);
	}
	
	protected function freeExclusiveJob(KalturaBatchJob $job)
	{
		$response = $this->kClient->virusScanBatch->freeExclusiveVirusScanJob($job->id, $this->getExclusiveLockKey(), false);
		
		KalturaLog::info("Queue size: $response->queueSize sent to scheduler");
		$this->saveSchedulerQueue(self::getType(), $response->queueSize);
		
		return $response->job;
	}
}
?>