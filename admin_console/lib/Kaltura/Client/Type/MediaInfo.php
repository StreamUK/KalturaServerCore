<?php
/**
 * @package Admin
 * @subpackage Client
 */
class Kaltura_Client_Type_MediaInfo extends Kaltura_Client_ObjectBase
{
	public function getKalturaObjectType()
	{
		return 'KalturaMediaInfo';
	}
	
	public function __construct(SimpleXMLElement $xml = null)
	{
		parent::__construct($xml);
		
		if(is_null($xml))
			return;
		
		if(count($xml->id))
			$this->id = (int)$xml->id;
		$this->flavorAssetId = (string)$xml->flavorAssetId;
		if(count($xml->fileSize))
			$this->fileSize = (int)$xml->fileSize;
		$this->containerFormat = (string)$xml->containerFormat;
		$this->containerId = (string)$xml->containerId;
		$this->containerProfile = (string)$xml->containerProfile;
		if(count($xml->containerDuration))
			$this->containerDuration = (int)$xml->containerDuration;
		if(count($xml->containerBitRate))
			$this->containerBitRate = (int)$xml->containerBitRate;
		$this->videoFormat = (string)$xml->videoFormat;
		$this->videoCodecId = (string)$xml->videoCodecId;
		if(count($xml->videoDuration))
			$this->videoDuration = (int)$xml->videoDuration;
		if(count($xml->videoBitRate))
			$this->videoBitRate = (int)$xml->videoBitRate;
		if(count($xml->videoBitRateMode))
			$this->videoBitRateMode = (int)$xml->videoBitRateMode;
		if(count($xml->videoWidth))
			$this->videoWidth = (int)$xml->videoWidth;
		if(count($xml->videoHeight))
			$this->videoHeight = (int)$xml->videoHeight;
		if(count($xml->videoFrameRate))
			$this->videoFrameRate = (float)$xml->videoFrameRate;
		if(count($xml->videoDar))
			$this->videoDar = (float)$xml->videoDar;
		if(count($xml->videoRotation))
			$this->videoRotation = (int)$xml->videoRotation;
		$this->audioFormat = (string)$xml->audioFormat;
		$this->audioCodecId = (string)$xml->audioCodecId;
		if(count($xml->audioDuration))
			$this->audioDuration = (int)$xml->audioDuration;
		if(count($xml->audioBitRate))
			$this->audioBitRate = (int)$xml->audioBitRate;
		if(count($xml->audioBitRateMode))
			$this->audioBitRateMode = (int)$xml->audioBitRateMode;
		if(count($xml->audioChannels))
			$this->audioChannels = (int)$xml->audioChannels;
		if(count($xml->audioSamplingRate))
			$this->audioSamplingRate = (int)$xml->audioSamplingRate;
		if(count($xml->audioResolution))
			$this->audioResolution = (int)$xml->audioResolution;
		$this->writingLib = (string)$xml->writingLib;
		$this->rawData = (string)$xml->rawData;
		$this->multiStreamInfo = (string)$xml->multiStreamInfo;
		if(count($xml->scanType))
			$this->scanType = (int)$xml->scanType;
		$this->multiStream = (string)$xml->multiStream;
	}
	/**
	 * The id of the media info
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $id = null;

	/**
	 * The id of the related flavor asset
	 * 
	 *
	 * @var string
	 */
	public $flavorAssetId = null;

	/**
	 * The file size
	 * 
	 *
	 * @var int
	 */
	public $fileSize = null;

	/**
	 * The container format
	 * 
	 *
	 * @var string
	 */
	public $containerFormat = null;

	/**
	 * The container id
	 * 
	 *
	 * @var string
	 */
	public $containerId = null;

	/**
	 * The container profile
	 * 
	 *
	 * @var string
	 */
	public $containerProfile = null;

	/**
	 * The container duration
	 * 
	 *
	 * @var int
	 */
	public $containerDuration = null;

	/**
	 * The container bit rate
	 * 
	 *
	 * @var int
	 */
	public $containerBitRate = null;

	/**
	 * The video format
	 * 
	 *
	 * @var string
	 */
	public $videoFormat = null;

	/**
	 * The video codec id
	 * 
	 *
	 * @var string
	 */
	public $videoCodecId = null;

	/**
	 * The video duration
	 * 
	 *
	 * @var int
	 */
	public $videoDuration = null;

	/**
	 * The video bit rate
	 * 
	 *
	 * @var int
	 */
	public $videoBitRate = null;

	/**
	 * The video bit rate mode
	 * 
	 *
	 * @var Kaltura_Client_Enum_BitRateMode
	 */
	public $videoBitRateMode = null;

	/**
	 * The video width
	 * 
	 *
	 * @var int
	 */
	public $videoWidth = null;

	/**
	 * The video height
	 * 
	 *
	 * @var int
	 */
	public $videoHeight = null;

	/**
	 * The video frame rate
	 * 
	 *
	 * @var float
	 */
	public $videoFrameRate = null;

	/**
	 * The video display aspect ratio (dar)
	 * 
	 *
	 * @var float
	 */
	public $videoDar = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $videoRotation = null;

	/**
	 * The audio format
	 * 
	 *
	 * @var string
	 */
	public $audioFormat = null;

	/**
	 * The audio codec id
	 * 
	 *
	 * @var string
	 */
	public $audioCodecId = null;

	/**
	 * The audio duration
	 * 
	 *
	 * @var int
	 */
	public $audioDuration = null;

	/**
	 * The audio bit rate
	 * 
	 *
	 * @var int
	 */
	public $audioBitRate = null;

	/**
	 * The audio bit rate mode
	 * 
	 *
	 * @var Kaltura_Client_Enum_BitRateMode
	 */
	public $audioBitRateMode = null;

	/**
	 * The number of audio channels
	 * 
	 *
	 * @var int
	 */
	public $audioChannels = null;

	/**
	 * The audio sampling rate
	 * 
	 *
	 * @var int
	 */
	public $audioSamplingRate = null;

	/**
	 * The audio resolution
	 * 
	 *
	 * @var int
	 */
	public $audioResolution = null;

	/**
	 * The writing library
	 * 
	 *
	 * @var string
	 */
	public $writingLib = null;

	/**
	 * The data as returned by the mediainfo command line
	 * 
	 *
	 * @var string
	 */
	public $rawData = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $multiStreamInfo = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $scanType = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $multiStream = null;


}

