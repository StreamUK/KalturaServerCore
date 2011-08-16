<?php
/**
 * @package Admin
 * @subpackage Client
 */
class Kaltura_Client_Type_Playlist extends Kaltura_Client_Type_BaseEntry
{
	public function getKalturaObjectType()
	{
		return 'KalturaPlaylist';
	}
	
	public function __construct(SimpleXMLElement $xml = null)
	{
		parent::__construct($xml);
		
		if(is_null($xml))
			return;
		
		$this->playlistContent = (string)$xml->playlistContent;
		if(empty($xml->filters))
			$this->filters = array();
		else
			$this->filters = Kaltura_Client_Client::unmarshalItem($xml->filters);
		if(count($xml->totalResults))
			$this->totalResults = (int)$xml->totalResults;
		if(count($xml->playlistType))
			$this->playlistType = (int)$xml->playlistType;
		if(count($xml->plays))
			$this->plays = (int)$xml->plays;
		if(count($xml->views))
			$this->views = (int)$xml->views;
		if(count($xml->duration))
			$this->duration = (int)$xml->duration;
	}
	/**
	 * Content of the playlist - 
	 * XML if the playlistType is dynamic 
	 * text if the playlistType is static 
	 * url if the playlistType is mRss 
	 *
	 * @var string
	 */
	public $playlistContent = null;

	/**
	 * 
	 *
	 * @var array of KalturaMediaEntryFilterForPlaylist
	 */
	public $filters;

	/**
	 * 
	 *
	 * @var int
	 */
	public $totalResults = null;

	/**
	 * Type of playlist  
	 *
	 * @var Kaltura_Client_Enum_PlaylistType
	 */
	public $playlistType = null;

	/**
	 * Number of plays
	 *
	 * @var int
	 * @readonly
	 */
	public $plays = null;

	/**
	 * Number of views
	 *
	 * @var int
	 * @readonly
	 */
	public $views = null;

	/**
	 * The duration in seconds
	 *
	 * @var int
	 * @readonly
	 */
	public $duration = null;


}

