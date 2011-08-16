<?php
/**
 * @package Scheduler
 * @subpackage Client
 */
require_once(dirname(__FILE__) . "/../KalturaClientBase.php");
require_once(dirname(__FILE__) . "/../KalturaEnums.php");
require_once(dirname(__FILE__) . "/../KalturaTypes.php");

/**
 * @package Scheduler
 * @subpackage Client
 */
class KalturaDocumentEntryOrderBy
{
	const NAME_ASC = "+name";
	const NAME_DESC = "-name";
	const MODERATION_COUNT_ASC = "+moderationCount";
	const MODERATION_COUNT_DESC = "-moderationCount";
	const CREATED_AT_ASC = "+createdAt";
	const CREATED_AT_DESC = "-createdAt";
	const UPDATED_AT_ASC = "+updatedAt";
	const UPDATED_AT_DESC = "-updatedAt";
	const RANK_ASC = "+rank";
	const RANK_DESC = "-rank";
	const PARTNER_SORT_VALUE_ASC = "+partnerSortValue";
	const PARTNER_SORT_VALUE_DESC = "-partnerSortValue";
}

/**
 * @package Scheduler
 * @subpackage Client
 */
class KalturaDocumentFlavorParamsOrderBy
{
}

/**
 * @package Scheduler
 * @subpackage Client
 */
class KalturaDocumentFlavorParamsOutputOrderBy
{
}

/**
 * @package Scheduler
 * @subpackage Client
 */
class KalturaDocumentType
{
	const DOCUMENT = 11;
	const SWF = 12;
	const PDF = 13;
}

/**
 * @package Scheduler
 * @subpackage Client
 */
class KalturaPdfFlavorParamsOrderBy
{
}

/**
 * @package Scheduler
 * @subpackage Client
 */
class KalturaPdfFlavorParamsOutputOrderBy
{
}

/**
 * @package Scheduler
 * @subpackage Client
 */
class KalturaSwfFlavorParamsOrderBy
{
}

/**
 * @package Scheduler
 * @subpackage Client
 */
class KalturaSwfFlavorParamsOutputOrderBy
{
}

/**
 * @package Scheduler
 * @subpackage Client
 */
abstract class KalturaDocumentEntryBaseFilter extends KalturaBaseEntryFilter
{
	/**
	 * 
	 *
	 * @var KalturaDocumentType
	 */
	public $documentTypeEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $documentTypeIn = null;


}

/**
 * @package Scheduler
 * @subpackage Client
 */
class KalturaDocumentEntryFilter extends KalturaDocumentEntryBaseFilter
{

}

/**
 * @package Scheduler
 * @subpackage Client
 */
abstract class KalturaDocumentFlavorParamsBaseFilter extends KalturaFlavorParamsFilter
{

}

/**
 * @package Scheduler
 * @subpackage Client
 */
class KalturaDocumentFlavorParamsFilter extends KalturaDocumentFlavorParamsBaseFilter
{

}

/**
 * @package Scheduler
 * @subpackage Client
 */
abstract class KalturaDocumentFlavorParamsOutputBaseFilter extends KalturaFlavorParamsOutputFilter
{

}

/**
 * @package Scheduler
 * @subpackage Client
 */
class KalturaDocumentFlavorParamsOutputFilter extends KalturaDocumentFlavorParamsOutputBaseFilter
{

}

/**
 * @package Scheduler
 * @subpackage Client
 */
abstract class KalturaPdfFlavorParamsBaseFilter extends KalturaFlavorParamsFilter
{

}

/**
 * @package Scheduler
 * @subpackage Client
 */
class KalturaPdfFlavorParamsFilter extends KalturaPdfFlavorParamsBaseFilter
{

}

/**
 * @package Scheduler
 * @subpackage Client
 */
abstract class KalturaPdfFlavorParamsOutputBaseFilter extends KalturaFlavorParamsOutputFilter
{

}

/**
 * @package Scheduler
 * @subpackage Client
 */
class KalturaPdfFlavorParamsOutputFilter extends KalturaPdfFlavorParamsOutputBaseFilter
{

}

/**
 * @package Scheduler
 * @subpackage Client
 */
abstract class KalturaSwfFlavorParamsBaseFilter extends KalturaFlavorParamsFilter
{

}

/**
 * @package Scheduler
 * @subpackage Client
 */
class KalturaSwfFlavorParamsFilter extends KalturaSwfFlavorParamsBaseFilter
{

}

/**
 * @package Scheduler
 * @subpackage Client
 */
abstract class KalturaSwfFlavorParamsOutputBaseFilter extends KalturaFlavorParamsOutputFilter
{

}

/**
 * @package Scheduler
 * @subpackage Client
 */
class KalturaSwfFlavorParamsOutputFilter extends KalturaSwfFlavorParamsOutputBaseFilter
{

}

/**
 * @package Scheduler
 * @subpackage Client
 */
class KalturaDocumentEntry extends KalturaBaseEntry
{
	/**
	 * The type of the document
	 *
	 * @var KalturaDocumentType
	 * @insertonly
	 */
	public $documentType = null;


}

/**
 * @package Scheduler
 * @subpackage Client
 */
class KalturaDocumentFlavorParams extends KalturaFlavorParams
{

}

/**
 * @package Scheduler
 * @subpackage Client
 */
class KalturaDocumentFlavorParamsOutput extends KalturaFlavorParamsOutput
{

}

/**
 * @package Scheduler
 * @subpackage Client
 */
class KalturaPdfFlavorParams extends KalturaFlavorParams
{
	/**
	 * 
	 *
	 * @var bool
	 */
	public $readonly = null;


}

/**
 * @package Scheduler
 * @subpackage Client
 */
class KalturaPdfFlavorParamsOutput extends KalturaFlavorParamsOutput
{
	/**
	 * 
	 *
	 * @var bool
	 */
	public $readonly = null;


}

/**
 * @package Scheduler
 * @subpackage Client
 */
class KalturaSwfFlavorParams extends KalturaFlavorParams
{

}

/**
 * @package Scheduler
 * @subpackage Client
 */
class KalturaSwfFlavorParamsOutput extends KalturaFlavorParamsOutput
{

}

/**
 * @package Scheduler
 * @subpackage Client
 */
class KalturaDocumentClientPlugin extends KalturaClientPlugin
{
	/**
	 * @var KalturaDocumentClientPlugin
	 */
	protected static $instance;

	protected function __construct(KalturaClient $client)
	{
		parent::__construct($client);
	}

	/**
	 * @return KalturaDocumentClientPlugin
	 */
	public static function get(KalturaClient $client)
	{
		if(!self::$instance)
			self::$instance = new KalturaDocumentClientPlugin($client);
		return self::$instance;
	}

	/**
	 * @return array<KalturaServiceBase>
	 */
	public function getServices()
	{
		$services = array(
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

