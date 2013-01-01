<?php
/**
 * @package plugins.httpNotification
 * @subpackage api.filters.base
 * @abstract
 */
abstract class KalturaHttpNotificationTemplateBaseFilter extends KalturaEventNotificationTemplateFilter
{
	static private $map_between_objects = array
	(
	);

	static private $order_by_map = array
	(
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), KalturaHttpNotificationTemplateBaseFilter::$map_between_objects);
	}

	public function getOrderByMap()
	{
		return array_merge(parent::getOrderByMap(), KalturaHttpNotificationTemplateBaseFilter::$order_by_map);
	}
}