<?php
require_once(dirname(__FILE__) . "/../KalturaClientBase.php");
require_once(dirname(__FILE__) . "/../KalturaEnums.php");
require_once(dirname(__FILE__) . "/../KalturaTypes.php");

class KalturaYouTubeDistributionProfile extends KalturaConfigurableDistributionProfile
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $username = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $notificationEmail = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $sftpHost = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $sftpLogin = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $sftpPublicKey = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $sftpPrivateKey = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $sftpBaseDir = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $ownerName = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $defaultCategory = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $allowComments = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $allowEmbedding = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $allowRatings = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $allowResponses = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $commercialPolicy = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $ugcPolicy = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $target = null;


}

class KalturaYouTubeDistributionProvider extends KalturaDistributionProvider
{

}

class KalturaYouTubeDistributionJobProviderData extends KalturaConfigurableDistributionJobProviderData
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $videoAssetFilePath = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $thumbAssetFilePath = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $sftpDirectory = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $sftpMetadataFilename = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $currentPlaylists = null;


}

class KalturaYouTubeDistributionClientPlugin extends KalturaClientPlugin
{
	/**
	 * @var KalturaYouTubeDistributionClientPlugin
	 */
	protected static $instance;

	protected function __construct(KalturaClient $client)
	{
		parent::__construct($client);
	}

	/**
	 * @return KalturaYouTubeDistributionClientPlugin
	 */
	public static function get(KalturaClient $client)
	{
		if(!self::$instance)
			self::$instance = new KalturaYouTubeDistributionClientPlugin($client);
		return self::$instance;
	}

	/**
	 * @return array<KalturaServiceBase>
	 */
	public function getServices()
	{
		$services = array(
		);
		return $services;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'youTubeDistribution';
	}
}

