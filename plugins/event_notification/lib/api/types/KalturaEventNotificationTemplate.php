<?php
/**
 * @package plugins.eventNotification
 * @subpackage api.objects
 */
class KalturaEventNotificationTemplate extends KalturaObject implements IFilterable
{	
	/**
	 * @var int
	 * @readonly
	 * @filter eq,in,order
	 */
	public $id;
	
	/**
	 * @var int
	 * @readonly
	 * @filter eq,in
	 */
	public $partnerId;
	
	/**
	 * @var string
	 */
	public $name;
	
	/**
	 * @var string
	 */
	public $systemName;
	
	/**
	 * @var string
	 */
	public $description;
	
	/**
	 * @var KalturaEventNotificationTemplateType
	 * @insertonly
	 * @filter eq,in
	 */
	public $type;
	
	/**
	 * @var KalturaEventNotificationTemplateStatus
	 * @readonly
	 * @filter eq,in
	 */
	public $status;
	
	/**
	 * @var int
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $createdAt;

	/**
	 * @var int
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $updatedAt;

	/**
	 * Define that the template could be dispatched manually from the API
	 * 
	 * @var bool
	 * @requiresPermission insert,update
	 */
	public $manualDispatchEnabled;

	/**
	 * Define that the template could be dispatched automatically by the system
	 * 
	 * @var bool
	 * @requiresPermission insert,update
	 */
	public $automaticDispatchEnabled;

	/**
	 * Define the event that should trigger this notification
	 * 
	 * @var KalturaEventNotificationEventType
	 */
	public $eventType;

	/**
	 * Define the object that raied the event that should trigger this notification
	 * 
	 * @var KalturaEventNotificationEventObjectType
	 */
	public $eventObjectType;

	/**
	 * Define the conditions that cause this notification to be triggered
	 * @var KalturaEventConditionArray
	 */
	public $eventConditions;
	
	/**
	 * mapping between the field on this object (on the left) and the setter/getter on the entry object (on the right)  
	 */
	private static $map_between_objects = array(
		'id',
		'partnerId',
		'name',
		'systemName',
		'description',
		'status',
		'createdAt',
		'updatedAt',
		'manualDispatchEnabled',
		'automaticDispatchEnabled',
		'eventType',
		'eventObjectType' => 'objectType',
		'eventConditions',
	);
		 
	/* (non-PHPdoc)
	 * @see KalturaObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
	 * @see KalturaObject::validateForInsert()
	 */
	public function validateForInsert($propertiesToSkip = array())
	{
		$this->validatePropertyMinLength('name', 3, false);
		$this->validatePropertyMinLength('systemName', 3, true);
		
		return parent::validateForInsert($propertiesToSkip);
	}
	
	/* (non-PHPdoc)
	 * @see KalturaObject::validateForUpdate()
	 */
	public function validateForUpdate($sourceObject, $propertiesToSkip = array())
	{
		$this->validatePropertyMinLength('name', 3, false);
		$this->validatePropertyMinLength('systemName', 3, true);
		
		return parent::validateForUpdate($sourceObject, $propertiesToSkip);
	}
	
	/* (non-PHPdoc)
	 * @see KalturaObject::toObject()
	 */
	public function toObject($dbObject = null, $propertiesToSkip = array())
	{
		if(is_null($dbObject))
			throw new kCoreException("Event notification template type [" . $this->type . "] not found", kCoreException::OBJECT_TYPE_NOT_FOUND, $this->type);
			
		return parent::toObject($dbObject, $propertiesToSkip);
	}
	
	/* (non-PHPdoc)
	 * @see IFilterable::getExtraFilters()
	 */
	public function getExtraFilters()
	{
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see IFilterable::getFilterDocs()
	 */
	public function getFilterDocs()
	{
		return array();
	}
	
	/**
	 * @param int $type core enum value of EventNotificationTemplateType
	 * @return KalturaEventNotificationTemplate
	 */
	public static function getInstanceByType($type)
	{
		return KalturaPluginManager::loadObject('KalturaEventNotificationTemplate', $type);
	}
}