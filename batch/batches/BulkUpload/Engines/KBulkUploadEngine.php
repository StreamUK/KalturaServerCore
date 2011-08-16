<?php
/**
 * base class for the real KBulkUploadEngine in the system 
 * 
 * @package Scheduler
 * @subpackage BulkUpload
 * @abstract
 */
abstract class KBulkUploadEngine
{
	const BULK_UPLOAD_DATE_FORMAT = '%Y-%m-%dT%H:%i:%s';

	/**
	 * 
	 * The batch current partner id
	 * @var int
	 */
	protected $currentPartnerId;
	
	/**
	 * @var KalturaConfiguration
	 */
	protected $kClientConfig = null;
		
	/**
	 * @var int
	 */
	protected $multiRequestSize = 5;
	
	/**
	 * @var int
	 */
	protected $maxRecords = 1000;
	
	/**
	 * @var int
	 */
	protected $maxRecordsEachRun = 100;
	
	/**
	 * @var int
	 */
	protected $handledRecordsThisRun = 0;
	
	/**
	 * @var bool
	 */
	protected $exceededMaxRecordsEachRun = false;

	/**
	 * 
	 * The Engine client
	 * @var KalturaClient
	 */
	protected $kClient; 
	
	/**
	 * 
	 * @var KalturaBatchJob
	 */
	protected $job = null;
	
	/**
	 * 
	 * @var KalturaBulkUploadJobData
	 */
	protected $data = null;
	
	/**
	 * @param string $str
	 * @return int
	 */
	public static function parseFormatedDate($str)
	{
		if(function_exists('strptime'))
		{
			$ret = strptime($str, self::BULK_UPLOAD_DATE_FORMAT);
			if($ret)
			{
				KalturaLog::debug("Formated Date [$ret] " . date('Y-m-d\TH:i:s', $ret));
				return $ret;
			}
		}
			
		$fields = null;
		$regex = self::getDateFormatRegex($fields);
		
		$values = null;
		if(!preg_match($regex, $str, $values))
			return null;
			
		$hour = 0;
		$minute = 0;
		$second = 0;
		$month = 0;
		$day = 0;
		$year = 0;
		$is_dst = 0;
		
		foreach($fields as $index => $field)
		{
			$value = $values[$index + 1];
			
			switch($field)
			{
				case 'Y':
					$year = intval($value);
					break;
					
				case 'm':
					$month = intval($value);
					break;
					
				case 'd':
					$day = intval($value);
					break;
					
				case 'H':
					$hour = intval($value);
					break;
					
				case 'i':
					$minute = intval($value);
					break;
					
				case 's':
					$second = intval($value);
					break;
					
//				case 'T':
//					$date = date_parse($value);
//					$hour -= ($date['zone'] / 60);
//					break;
					
			}
		}
		
		KalturaLog::debug("gmmktime($hour, $minute, $second, $month, $day, $year)");
		$ret = gmmktime($hour, $minute, $second, $month, $day, $year);
		if($ret)
		{
			KalturaLog::debug("Formated Date [$ret] " . date('Y-m-d\TH:i:s', $ret));
			return $ret;
		}
		return null;
	}
		
	/**
	 * @param string $str
	 * @return boolean
	 */
	protected function isUrl($str)
	{
		$str = KCurlWrapper::encodeUrl($str);
		
		$strRegex = "^((https?)|(ftp)):\\/\\/" . "?(([0-9a-zA-Z_!~*'().&=+$%-]+:)?[0-9a-zA-Z_!~*'().&=+$%-]+@)?" . //user@
					"(([0-9]{1,3}\\.){3}[0-9]{1,3}" . // IP- 199.194.52.184
					"|" . // allows either IP or domain
					"([0-9a-zA-Z_!~*'()-]+\\.)*" . // tertiary domain(s)- www.
					"([0-9a-zA-Z][0-9a-zA-Z-]{0,61})?[0-9a-zA-Z]\\." . // second level domain
					"[a-zA-Z]{2,6})" . // first level domain- .com or .museum
					"(.[a-zA-Z]{2,6})*" . // additional domain level .il
					"(:[0-9]{1,4})?" . // port number- :80
					"((\\/?)|" . // a slash isn't required if there is no file name
					"(\\/[0-9a-zA-Z_!~*'().;?:@&=+$,%#-]+)+)$";
		
		return preg_match("/$strRegex/i", $str);
	}
		
	/**
	 * @param array $fields
	 * @return string
	 */
	private static function getDateFormatRegex(&$fields = null)
	{
		$replace = array(
			'%Y' => '([1-2][0-9]{3})',
			'%m' => '([0-1][0-9])',
			'%d' => '([0-3][0-9])',
			'%H' => '([0-2][0-9])',
			'%i' => '([0-5][0-9])',
			'%s' => '([0-5][0-9])',
//			'%T' => '([A-Z]{3})',
		);
	
		$fields = array();
		$arr = null;
		if(!preg_match_all('/%([YmdTHis])/', self::BULK_UPLOAD_DATE_FORMAT, $arr))
			return false;
	
		$fields = $arr[1];
		
		return '/' . str_replace(array_keys($replace), $replace, self::BULK_UPLOAD_DATE_FORMAT) . '/';
	}
	
	/**
	 * @param string $str
	 * @return boolean
	 */
	public static function isFormatedDate($str)
	{
		$regex = self::getDateFormatRegex();
		return preg_match($regex, $str);
	}
	
	/**
	 * @param KSchedularTaskConfig $taskConfig
	 */
	public function __construct( KSchedularTaskConfig $taskConfig, KalturaClient $kClient, KalturaBatchJob $job)
	{
		if($taskConfig->params->multiRequestSize)
			$this->multiRequestSize = $taskConfig->params->multiRequestSize;
		if($taskConfig->params->maxRecords)
			$this->maxRecords = $taskConfig->params->maxRecords;
		if($taskConfig->params->maxRecordsEachRun)
			$this->maxRecordsEachRun = $taskConfig->params->maxRecordsEachRun;
		
		$this->kClient = $kClient;
		$this->kClientConfig = $kClient->getConfig();
		
		$this->job = $job;
		$this->data = $job->data;
		
		$this->currentPartnerId = $this->job->partnerId;
	}
	
	/**
	 * Will return the proper engine depending on the type (KalturaBulkUploadType)
	 *
	 * @param int $provider
	 * @param KSchedularTaskConfig $taskConfig - for the engine
	 * @param KalturaClient kClient - the client for the engine to use
	 * @return KBulkUploadEngine
	 */
	public static function getEngine($batchJobSubType, KSchedularTaskConfig $taskConfig, $kClient, KalturaBatchJob $job)
	{
		//Gets the engine from the plugin (as we moved all engines to the plugin)
		return KalturaPluginManager::loadObject('KBulkUploadEngine', $batchJobSubType, array($taskConfig, $kClient, $job));
	}
	
	/**
	 * @return string
	 */
	public function getName()
	{
		return get_class($this);
	}
	
	/**
	 * @return KalturaBatchJob
	 */
	public function getJob()
	{
		return $this->job;
	}

	/**
	 * @return KalturaBulkUploadJobData
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @return bool
	 */
	public function shouldRetry()
	{
		return $this->exceededMaxRecordsEachRun;
	}

	/**
	 * 
	 * Impersonates into the current partner (overrides the batch partner) 
	 */
	protected function impersonate()
	{
		$this->kClientConfig->partnerId = $this->currentPartnerId;
		$this->kClient->setConfig($this->kClientConfig);
	}
		
	/**
	 * 
	 * Handles the bulk upload
	 */
	abstract public function handleBulkUpload();
			
	/**
	 * 
	 * Adds a bulk upload result
	 * @param KalturaBulkUploadResult $bulkUploadResult
	 */
	protected function addBulkUploadResult(KalturaBulkUploadResult $bulkUploadResult)
	{
		$pluginsData = $bulkUploadResult->pluginsData;
		$bulkUploadResult->pluginsData = null;
		$this->kClient->batch->addBulkUploadResult($bulkUploadResult, $pluginsData);
	}

	/**
	 * 
	 * Gets the start line number for the given job id
	 * @return int - the start line for the job id
	 */
	protected function getStartIndex()
	{
		try{
			return (int)$this->kClient->batch->getBulkUploadLastResult($this->job->id)->lineIndex;
		}
		catch(Exception $e){
			KalturaLog::notice("getBulkUploadLastResult: " . $e->getMessage());
		}
		return 0;
	}
	
	/**
	 * 
	 * Start a multirequest, if specified start the multi request for the job partner
	 * @param bool $isSpecificForPartner
	 */
	protected function startMultiRequest($isSpecificForPartner = false)
	{
		if($isSpecificForPartner)
		{
			$this->kClientConfig->partnerId = $this->currentPartnerId;
			$this->kClient->setConfig($this->kClientConfig);
		}
		
		$this->kClient->startMultiRequest();
	}	
	
	/**
	 * save the results for returned created entries
	 * 
	 * @param array $requestResults
	 * @param array $bulkUploadResults
	 */
	protected function updateEntriesResults(array $requestResults, array $bulkUploadResults)
	{
		$this->kClient->startMultiRequest();
		KalturaLog::info("Updating " . count($requestResults) . " results");
		
		// checking the created entries
		foreach($requestResults as $index => $requestResult)
		{
			$bulkUploadResult = $bulkUploadResults[$index];
			
			if($requestResult instanceof Exception)
			{
				$bulkUploadResult->entryStatus = KalturaEntryStatus::ERROR_IMPORTING;
				$bulkUploadResult->errorDescription = $requestResult->getMessage();
				$this->addBulkUploadResult($bulkUploadResult);
				continue;
			}
			
			if(! ($requestResult instanceof KalturaBaseEntry))
			{
				$bulkUploadResult->entryStatus = KalturaEntryStatus::ERROR_IMPORTING;
				$bulkUploadResult->errorDescription = "Returned type is " . get_class($requestResult) . ', KalturaMediaEntry was expected';
				$this->addBulkUploadResult($bulkUploadResult);
				continue;
			}
			
			// update the results with the new entry id
			$bulkUploadResult->entryId = $requestResult->id;
			$this->addBulkUploadResult($bulkUploadResult);
		}
		$this->kClient->doMultiRequest();
	}
	
	/**
	 * 
	 * Checks if the job was aborted (throws exception if so)
	 * @throws KalturaBulkUploadAbortedException
	 */
	protected function checkAborted()
	{
		if($this->kClient->isMultiRequest())
			$this->kClient->doMultiRequest();
			
		$batchJobResponse = $this->kClient->jobs->getBulkUploadStatus($this->job->id);
		$updatedJob = $batchJobResponse->batchJob;
		if($updatedJob->abort)
		{
			KalturaLog::info("job[{$this->job->id}] aborted");
				
			//Throw exception and close the job from the outside 
			throw new KalturaBulkUploadAbortedException("Job was aborted", KalturaBulkUploadAbortedException::JOB_ABORTED);
		}
		return false;
	}
}