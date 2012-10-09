<?php
/**
 * @package plugins.uverseClickToOrderDistribution
 * @subpackage model.enum
 */ 
interface UverseClickToOrderDistributionField extends BaseEnum
{
	
	const BACKGROUND_IMAGE_WIDE  = 'BACKGROUND_IMAGE_WIDE';
	const BACKGROUND_IMAGE_STANDART  = 'BACKGROUND_IMAGE_STANDART';
	const SORT_ITEMS_BY_FIELD = 'SORT_ITEMS_BY_FIELD';
	//category
	const CATEGORY_ENTRY_ID = 'CATEGORY_ENTRY_ID';
	const CATEGORY_IMAGE_WIDTH = 'CATEGORY_IMAGE_WIDTH';
	const CATEGORY_IMAGE_HEIGHT = 'CATEGORY_IMAGE_HEIGHT';
	//item
	const ITEM_TITLE = 'ITEM_TITLE';
	const ITEM_CONTENT_TYPE = 'ITEM_CONTENT_TYPE';	
	const ITEM_CCVIDFILE = 'ITEM_CCVIDFILE';
	const ITEM_DESTINATION = 'ITEM_DESTINATION';
	const ITEM_CONTENT = 'ITEM_CONTENT';
	const ITEM_DIRECTIONS = 'ITEM_DIRECTIONS';
		
}