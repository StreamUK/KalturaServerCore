<?php
/**
 * @package plugins.synacorHboDistribution
 * @subpackage api.filters.base
 * @abstract
 */
abstract class KalturaSynacorHboDistributionProviderBaseFilter extends KalturaDistributionProviderFilter
{
	static private $map_between_objects = array
	(
	);

	static private $order_by_map = array
	(
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), KalturaSynacorHboDistributionProviderBaseFilter::$map_between_objects);
	}

	public function getOrderByMap()
	{
		return array_merge(parent::getOrderByMap(), KalturaSynacorHboDistributionProviderBaseFilter::$order_by_map);
	}
}
