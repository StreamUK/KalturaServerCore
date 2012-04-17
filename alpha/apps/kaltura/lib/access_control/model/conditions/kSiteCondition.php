<?php
/**
 * @package Core
 * @subpackage model.data
 */
class kSiteCondition extends kMatchCondition
{
	/**
	 * Indicates that global whitelist domains already appended 
	 * @var bool
	 */
	private $globalWhitelistDomainsAppended = false;
	
	/* (non-PHPdoc)
	 * @see kCondition::__construct()
	 */
	public function __construct($not = false)
	{
		$this->setType(ConditionType::SITE);
		parent::__construct($not);
	}
	
	/* (non-PHPdoc)
	 * @see kCondition::getFieldValue()
	 */
	public function getFieldValue(accessControl $accessControl)
	{
		$scope = $accessControl->getScope();
		$referrer = $scope->getReferrer();
		
		$referrerDetails = parse_url($referrer);
		if(isset($referrerDetails['host']))
		{
			$suspectedDomain = $referrerDetails['host'];
		}
		elseif(isset($referrerDetails['path']))
		{
			// parse_url could not extract domain, but returned path
			// we validate that this path could be considered a domain
			$suspectedDomain = rtrim($referrerDetails['path'], '/'); // trim trailing slashes. example: www.kaltura.com/test.php
			
			// stop string at first slash. example: httpssss/google.com - malformed url...
			if (strpos($suspectedDomain, "/") !== false)
			{
				$suspectedDomain = substr($suspectedDomain, 0, strpos($suspectedDomain, "/"));
			}
		}
		else // empty path and host, cannot parse the URL
		{
			return null;
		}
		
		// some urls might return host or path which is not yet clean for comparison with user's input
		if (strpos($suspectedDomain, "?") !== false)
		{
			$suspectedDomain = substr($suspectedDomain, 0, strpos($suspectedDomain, "?"));
		}
		if (strpos($suspectedDomain, "#") !== false)
		{
			$suspectedDomain = substr($suspectedDomain, 0, strpos($suspectedDomain, "#"));
		}
		if (strpos($suspectedDomain, "&") !== false)
		{
			$suspectedDomain = substr($suspectedDomain, 0, strpos($suspectedDomain, "&"));
		}
		return $suspectedDomain;
	}
	
	/* (non-PHPdoc)
	 * @see kCondition::internalFulfilled()
	 */
	public function internalFulfilled(accessControl $accessControl)
	{
		$scope = $accessControl->getScope();
		$referrer = $scope->getReferrer();

		if (!$this->globalWhitelistDomainsAppended && strpos($referrer, "kwidget") === false && kConf::hasParam("global_whitelisted_domains"))
		{
			$this->globalWhitelistDomainsAppended = true;
			
			$globalWhitelistedDomains = kConf::get("global_whitelisted_domains");
			if(!is_array($globalWhitelistedDomains))
				$globalWhitelistedDomains = explode(',', $globalWhitelistedDomains);
				
			foreach($globalWhitelistedDomains as $globalWhitelistedDomain)
				$this->values[] = $globalWhitelistedDomain;
		}
		
		return parent::internalFulfilled($accessControl);
	}
	
	/* (non-PHPdoc)
	 * @see kMatchCondition::matches()
	 */
	protected function matches($field, $value)
	{
		return ($field === $value) || (strpos($field, ".".$value) !== false);
	}

	/* (non-PHPdoc)
	 * @see kCondition::shouldDisableCache()
	 */
	public function shouldDisableCache($scope)
	{
		// no need to disable cache if the referrer is part of the cache key
		$ks = $scope->getKs();
		if ($ks && in_array($ks->partner_id, kConf::get('v3cache_include_referrer_in_key')))
			return false;
		return true;
	}
}
