<?php
/**
 * @package Admin
 * @subpackage Client
 */
class Kaltura_Client_ShortLink_Plugin extends Kaltura_Client_Plugin
{
	/**
	 * @var Kaltura_Client_ShortLink_Plugin
	 */
	protected static $instance;

	/**
	 * @var Kaltura_Client_ShortLink_ShortLinkService
	 */
	public $shortLink = null;

	protected function __construct(Kaltura_Client_Client $client)
	{
		parent::__construct($client);
		$this->shortLink = new Kaltura_Client_ShortLink_ShortLinkService($client);
	}

	/**
	 * @return Kaltura_Client_ShortLink_Plugin
	 */
	public static function get(Kaltura_Client_Client $client)
	{
		if(!self::$instance)
			self::$instance = new Kaltura_Client_ShortLink_Plugin($client);
		return self::$instance;
	}

	/**
	 * @return array<Kaltura_Client_ServiceBase>
	 */
	public function getServices()
	{
		$services = array(
			'shortLink' => $this->shortLink,
		);
		return $services;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'shortLink';
	}
}

