<?php
/**
 * @package api
 * @subpackage filters.base
 * @abstract
 */
abstract class KalturaThumbParamsBaseFilter extends KalturaAssetParamsFilter
{
	static private $map_between_objects = array
	(
		"formatEqual" => "_eq_format",
	);

	static private $order_by_map = array
	(
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), KalturaThumbParamsBaseFilter::$map_between_objects);
	}

	public function getOrderByMap()
	{
		return array_merge(parent::getOrderByMap(), KalturaThumbParamsBaseFilter::$order_by_map);
	}

	/**
	 * @var KalturaContainerFormat
	 */
	public $formatEqual;
}
