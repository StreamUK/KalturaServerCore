<?php
/**
 * @package Admin
 * @subpackage Client
 */
class Kaltura_Client_Document_Plugin extends Kaltura_Client_Plugin
{
	/**
	 * @var Kaltura_Client_Document_Plugin
	 */
	protected static $instance;

	/**
	 * @var Kaltura_Client_Document_DocumentsService
	 */
	public $documents = null;

	protected function __construct(Kaltura_Client_Client $client)
	{
		parent::__construct($client);
		$this->documents = new Kaltura_Client_Document_DocumentsService($client);
	}

	/**
	 * @return Kaltura_Client_Document_Plugin
	 */
	public static function get(Kaltura_Client_Client $client)
	{
		if(!self::$instance)
			self::$instance = new Kaltura_Client_Document_Plugin($client);
		return self::$instance;
	}

	/**
	 * @return array<Kaltura_Client_ServiceBase>
	 */
	public function getServices()
	{
		$services = array(
			'documents' => $this->documents,
		);
		return $services;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'document';
	}
}

