<?php
/**
 * @package plugins.yahooDistribution
 * @subpackage api.filters.base
 * @abstract
 */
abstract class KalturaYahooDistributionProviderBaseFilter extends KalturaDistributionProviderFilter
{
	static private $map_between_objects = array
	(
	);

	static private $order_by_map = array
	(
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), KalturaYahooDistributionProviderBaseFilter::$map_between_objects);
	}

	public function getOrderByMap()
	{
		return array_merge(parent::getOrderByMap(), KalturaYahooDistributionProviderBaseFilter::$order_by_map);
	}
}
