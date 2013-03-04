<?php
/**
 * @package plugins.podcastDistribution
 * @subpackage api.filters.base
 * @abstract
 */
abstract class KalturaPodcastDistributionProviderBaseFilter extends KalturaDistributionProviderFilter
{
	static private $map_between_objects = array
	(
	);

	static private $order_by_map = array
	(
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), KalturaPodcastDistributionProviderBaseFilter::$map_between_objects);
	}

	public function getOrderByMap()
	{
		return array_merge(parent::getOrderByMap(), KalturaPodcastDistributionProviderBaseFilter::$order_by_map);
	}
}
