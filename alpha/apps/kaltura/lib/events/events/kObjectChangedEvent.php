<?php
/**
 * Auto-raised event that raised by the propel generated objects
 * @package Core
 * @subpackage events
 */
class kObjectChangedEvent extends KalturaEvent implements IKalturaDatabaseEvent, IKalturaObjectRelatedEvent
{
	const EVENT_CONSUMER = 'kObjectChangedEventConsumer';
	
	/**
	 * @var BaseObject
	 */
	private $object;
	
	/**
	 * @var array
	 */
	private $modifiedColumns;
	
	/**
	 * @param BaseObject $object
	 */
	public function __construct(BaseObject $object, array $modifiedColumns)
	{
		$this->object = $object;
		$this->modifiedColumns = $modifiedColumns;
		
		$additionalLog = '';
		if(method_exists($object, 'getId'))
			$additionalLog .= 'id [' . $object->getId() . ']';
			
		KalturaLog::debug("Event [" . get_class($this) . "] object type [" . get_class($object) . "] $additionalLog modified columns [" . print_r($modifiedColumns, true) . "]");
	}
	
	public function getConsumerInterface()
	{
		return self::EVENT_CONSUMER;
	}
	
	/**
	 * @param kObjectChangedEventConsumer $consumer
	 * @return bool true if should continue to the next consumer
	 */
	protected function doConsume(KalturaEventConsumer $consumer)
	{
		if(!$consumer->shouldConsumeChangedEvent($this->object, $this->modifiedColumns))
			return true;
			
		$additionalLog = '';
		if(method_exists($this->object, 'getId'))
			$additionalLog .= 'id [' . $this->object->getId() . ']';
			
		KalturaLog::debug(get_class($this) . ' event consumed by ' . get_class($consumer) . ' object type [' . get_class($this->object) . '] ' . $additionalLog);
		return $consumer->objectChanged($this->object, $this->modifiedColumns);
	}
	
	public function getKey()
	{
		if(method_exists($this->object, 'getId'))
			return get_class($this->object).$this->object->getId();
		
		return null;
	}
	
	/**
	 * @return BaseObject $object
	 */
	public function getObject() 
	{
		return $this->object;
	}
	
	/**
	 * @return array $object
	 */
	public function getModifiedColumns() 
	{
		return $this->modifiedColumns;
	}
	
	/* (non-PHPdoc)
	 * @see KalturaEvent::getScope()
	 */
	public function getScope()
	{
		$scope = parent::getScope();
		if(method_exists($this->object, 'getPartnerId'))
			$scope->setPartnerId($this->object->getPartnerId());
			
		return $scope;
	}
}