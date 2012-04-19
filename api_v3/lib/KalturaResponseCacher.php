<?php
require_once(dirname(__FILE__) . '/../../alpha/config/kConf.php');
require_once(dirname(__FILE__) . '/../../alpha/apps/kaltura/lib/requestUtils.class.php');

class KalturaResponseCacher
{
	// warm cache constatns
	// cache warming is used to maintain continous use of the request caching while preventing a load once the cache expires
	// during WARM_CACHE_INTERVAL before the cache expiry a single request will be allowed to get through and renew the cache
	// this request named warm cache request will block other such requests for WARM_CACHE_TTL seconds

	// header to mark the request is due to cache warming. the header holds the original request protocol http/https
	const WARM_CACHE_HEADER = "X-KALTURA-WARM-CACHE";

	// interval before cache expiry in which to try and warm the cache
	const WARM_CACHE_INTERVAL = 60;

	// time in which a warm cache request will block another request from warming the cache
	const WARM_CACHE_TTL = 10;
	
	// cache statuses
	const CACHE_STATUS_ACTIVE = 0;				// cache was not explicitly disabled
	const CACHE_STATUS_ANONYMOUS_ONLY = 1;		// conditional cache was explicitly disabled by calling DisableConditionalCache (e.g. a database query that is not handled by the query cache was issued)
	const CACHE_STATUS_DISABLED = 2;			// cache was explicitly disabled by calling DisableCache (e.g. getContentData for an entry with access control)
	
	// cache modes
	const CACHE_MODE_NONE = 0;					// no caching should be performed
	const CACHE_MODE_ANONYMOUS = 1;				// anonymous caching should be performed - the cached response will not be associated with any conditions
	const CACHE_MODE_CONDITIONAL = 2;			// cache the response along with its matching conditions
	
	const CONDITIONAL_CACHE_EXPIRY = 86400;		// 1 day, must not be greater than the expiry of the query cache keys

	protected $_params = array();
	protected $_cacheFilePrefix = "cache_v3-";
	protected $_cacheDirectory = "/tmp/";
	protected $_cacheKey = "";
	protected $_cacheDataFilePath = "";
	protected $_cacheHeadersFilePath = "";
	protected $_cacheLogFilePath = "";
	protected $_cacheExpiryFilePath = "";
	protected $_cacheConditionsFilePath = "";
	protected $_ks = "";
	protected $_ksHash = "";
	protected $_ksRealStr = "";
	protected $_ksValidUntil = "";
	protected $_ksPartnerId = null;
	protected $_defaultExpiry = 600;
	protected $_expiry = 600;
	protected $_cacheHeadersExpiry = 60; // cache headers for CDN & browser - used  for GET request with kalsig param
	
	protected $_instanceId = 0;
	
	protected $_cacheStatus = self::CACHE_STATUS_DISABLED;	// enabled after the KalturaResponseCacher initializes
	protected $_invalidationKeys = array();				// the list of query cache invalidation keys for the current request
	protected $_invalidationTime = 0;					// the last invalidation time of the invalidation keys
	
	protected $_wouldHaveUsedCondCache = false;			// XXXXXXX TODO: remove this

	protected static $_activeInstances = array();		// active class instances: instanceId => instanceObject
	protected static $_nextInstanceId = 0;
		
	public function __construct($params = null, $cacheDirectory = null, $expiry = 0)
	{
		$this->_instanceId = self::$_nextInstanceId;  
		self::$_nextInstanceId++;
		
		if ($expiry)
			$this->_defaultExpiry = $this->_expiry = $expiry;
			
		$this->_cacheDirectory = $cacheDirectory ? $cacheDirectory : 
			rtrim(kConf::get('response_cache_dir'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		
		$this->_cacheDirectory .= "cache_v3-".$this->_expiry . DIRECTORY_SEPARATOR;
		
		if (!kConf::get('enable_cache'))
			return;
			
		if (!$params) {
			$params = requestUtils::getRequestParams();
		}
		
		foreach(kConf::get('v3cache_ignore_params') as $name)
			unset($params[$name]);
		
		// check the clientTag parameter for a cache start time (cache_st:<time>) directive
		if (isset($params['clientTag']))
		{
			$clientTag = $params['clientTag'];
			$matches = null;
			if (preg_match("/cache_st:(\\d+)/", $clientTag, $matches))
			{
				if ($matches[1] > time())
				{
					self::disableCache();
					return;
				}
			}
		}
				
		if (isset($params['nocache']))
		{
			self::disableCache();
			return;
		}
		
		$ks = isset($params['ks']) ? $params['ks'] : '';
		foreach($params as $key => $value)
		{
			if(preg_match('/[\d]+:ks/', $key))
			{
				if (!$ks && strpos($value, ':result') === false)
					$ks = $value;
				unset($params[$key]);
			}
		}
			
		unset($params['ks']);
		unset($params['kalsig']);
		unset($params['clientTag']);
		unset($params['callback']);
		
		$this->_params = $params;
		$this->setKS($ks);

		self::$_activeInstances[$this->_instanceId] = $this;
		$this->_cacheStatus = self::CACHE_STATUS_ACTIVE;
	}
	
	public function setKS($ks)
	{
		$this->_ks = $ks;
		
		$ksData = $this->getKsData();
		$this->_params["___cache___partnerId"] = $this->_ksPartnerId;
		$this->_params["___cache___userId"] = $ksData["userId"];
		$this->_params["___cache___ksType"] = $ksData["type"];
		$this->_params["___cache___privileges"] = $ksData["privileges"];
		$this->_params['___cache___uri'] = $_SERVER['PHP_SELF'];
		$this->_params['___cache___protocol'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? "https" : "http";

		// take only the hostname part of the referrer parameter of baseEntry.getContextData
		foreach ($this->_params as $key => $value)
		{
			if (strpos($key, 'contextDataParams:referrer') === false)
				continue;

			if (in_array($this->_ksPartnerId, kConf::get('v3cache_include_referrer_in_key')))
				$this->_params[$key] = parse_url($value, PHP_URL_HOST);
			else
				unset($this->_params[$key]);
				
			break;
		}
		
		ksort($this->_params);

		$this->_cacheKey = md5( http_build_query($this->_params) );

		// split cache over 16 folders using the cachekey first character
		// this will reduce the amount of files per cache folder
		$pathWithFilePrefix = $this->_cacheDirectory . DIRECTORY_SEPARATOR . substr($this->_cacheKey, 0, 2) . DIRECTORY_SEPARATOR . $this->_cacheFilePrefix;
		$this->_cacheDataFilePath 		= $pathWithFilePrefix . $this->_cacheKey;
		$this->_cacheHeadersFilePath 	= $pathWithFilePrefix . $this->_cacheKey . ".headers";
		$this->_cacheLogFilePath 		= $pathWithFilePrefix . $this->_cacheKey . ".log";
		$this->_cacheExpiryFilePath 	= $pathWithFilePrefix . $this->_cacheKey . ".expiry";
		$this->_cacheConditionsFilePath = $pathWithFilePrefix . $this->_cacheKey . ".conditions";
	}
	
	public static function disableCache()
	{
		foreach (self::$_activeInstances as $curInstance)
		{
			$curInstance->_cacheStatus = self::CACHE_STATUS_DISABLED;
		}
		self::$_activeInstances = array();
	}

	public static function disableConditionalCache()
	{
		foreach (self::$_activeInstances as $curInstance)
		{
			// no need to check for CACHE_STATUS_DISABLED, since the instances are removed from the list when they get this status
			$curInstance->_cacheStatus = self::CACHE_STATUS_ANONYMOUS_ONLY;
		}
	}
	
	public static function addInvalidationKeys($invalidationKeys, $invalidationTime)
	{
		foreach (self::$_activeInstances as $curInstance)
		{
			$curInstance->_invalidationKeys = array_merge($curInstance->_invalidationKeys, $invalidationKeys);
			$curInstance->_invalidationTime = max($curInstance->_invalidationTime, $invalidationTime);
		}
	}
	
	/**
	 * This functions checks if a certain response resides in cache.
	 * In case it dose, the response is returned from cache and a response header is added.
	 * There are two possibilities on which this function is called:
	 * 1)	The request is a single 'stand alone' request (maybe this request is a multi request containing several sub-requests)
	 * 2)	The request is a single request that is part of a multi request (sub-request in a multi request)
	 * 
	 * in case this function is called when handling a sub-request (single request as part of a multirequest) it
	 * is preferable to change the default $cacheHeaderName
	 * 
	 * @param $cacheHeaderName - the header name to add
	 * @param $cacheHeader - the header value to add
	 */	 
	public function checkCache($cacheHeaderName = 'X-Kaltura', $cacheHeader = 'cached-dispatcher')
	{
		if ($this->_cacheStatus == self::CACHE_STATUS_DISABLED)
			return false;
		
		$startTime = microtime(true);
		if ($this->hasCache())
		{
			$response = @file_get_contents($this->_cacheDataFilePath);
			if ($response)
			{
				$processingTime = microtime(true) - $startTime;
				header("$cacheHeaderName:$cacheHeader,$this->_cacheKey,$processingTime", false);

				// in case of multirequest, we must not condtionally cache the multirequest when a sub request comes from cache
				// for single requests, the next line has no effect
				self::disableConditionalCache();
				return $response;
			}
		}
		
		return false;				
	}
	
		
	public function checkOrStart()
	{
		if ($this->_cacheStatus == self::CACHE_STATUS_DISABLED)
			return;
					
		$response = $this->checkCache();
		
		if ($response)
		{
			$contentTypeHdr = @file_get_contents($this->_cacheHeadersFilePath);
			if ($contentTypeHdr) {
				header($contentTypeHdr, true);
			}	

			// for GET requests with kalsig (signature of call params) return cdn/browser caching headers
			if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_REQUEST["kalsig"]))
			{
				$max_age = $this->_cacheHeadersExpiry;
				header("Cache-Control: private, max-age=$max_age max-stale=0");
				header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $max_age) . 'GMT'); 
				header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . 'GMT');
			}
			else
			{
				header("Expires: Sun, 19 Nov 2000 08:52:00 GMT", true);
				header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0", true);
				header("Pragma: no-cache", true);
			}
	
			// for jsonp ignore the callback argument and replace it in result (e.g. callback_4([{...}]);
			if (@$_REQUEST["format"] == 9)
			{
				$callback = @$_REQUEST["callback"];
				$pos = strpos($response, "(");
				if ($pos)
				{
					$response = $callback.substr($response, $pos);
				}
			}

			echo $response;
			die;
		}
		else
		{
			ob_start();
		}
	}
	
		
	public function end()
	{
		if ($this->getCacheMode() == self::CACHE_MODE_NONE)
			return;
	
		$response = ob_get_contents();
		$headers = headers_list();
		$contentType = "";
		foreach($headers as $headerStr)
		{
			$header = explode(":", $headerStr);
			if (isset($header[0]) && strtolower($header[0]) == "content-type")
			{
				$contentType = $headerStr;
				break;	
			}
		}
		
		$this->storeCache($response, $contentType);
		
		ob_end_flush();
	}
	
	
	public function storeCache($response, $contentType = null)
	{
		// remove $this from the list of active instances - the request is complete
		unset(self::$_activeInstances[$this->_instanceId]);
	
		$cacheMode = $this->getCacheMode();
		if ($cacheMode == self::CACHE_MODE_NONE)
			return;

		// provide cache key in header unless the X-Kaltura header was already set with a value
		// such as an error code. the header is used for debugging but it also appears in the access_log
		// and there we rather show the error than the cach key

		$headers = headers_list();
		$foundHeader = false;
		foreach($headers as $header)
		{
			if (strpos($header, 'X-Kaltura') === 0)
			{
				$foundHeader = true;
				break;
			}
		}

		if (!$foundHeader)
			header("X-Kaltura:cache-key,".$this->_cacheKey);
	
		$this->createDirForPath($this->_cacheLogFilePath);
		$this->createDirForPath($this->_cacheDataFilePath);
			
		file_put_contents($this->_cacheLogFilePath, "cachekey: $this->_cacheKey\n".print_r($this->_params, true)."\n".$response);
		
		$cachedResponse = null;						// XXXXXXX TODO: remove this
		if ($this->_wouldHaveUsedCondCache)			// XXXXXXX TODO: remove this
		{
			$cachedResponse = @file_get_contents($this->_cacheDataFilePath);
		}

		if(!is_null($contentType)) {
			$this->createDirForPath($this->_cacheHeadersFilePath);
			file_put_contents($this->_cacheHeadersFilePath, $contentType);
		}

		if ($cacheMode == self::CACHE_MODE_CONDITIONAL)
		{
			// save the cache conditions
			$conditions = array(array_unique($this->_invalidationKeys), $this->_invalidationTime);
			file_put_contents($this->_cacheConditionsFilePath, serialize($conditions));
		}
		else
		{
			// store specific expiry shorter than the default one
			if ($this->_expiry == $this->_defaultExpiry)
			{
				if (kConf::hasParam("v3cache_expiry"))
				{
					$expiryArr = kConf::get("v3cache_expiry");
					if (array_key_exists($this->_ksPartnerId, $expiryArr))
						$this->_expiry = $expiryArr[$this->_ksPartnerId];
				}
			}

			if ($this->_expiry != $this->_defaultExpiry)
				file_put_contents($this->_cacheExpiryFilePath, time() + $this->_expiry);
		}
		
		// write the cached response to a temporary file and then rename, to prevent any
		// other running instance of apache from picking up a partially written response
		$tempDataFilePath = tempnam(dirname($this->_cacheDataFilePath), basename($this->_cacheDataFilePath));
		file_put_contents($tempDataFilePath, $response);
		rename($tempDataFilePath, $this->_cacheDataFilePath);
		
		// compare the calculated $response to the previously stored $cachedResponse
		if ($cachedResponse)			// XXXXXXX TODO: remove this
		{
			$pattern = '/\/ks\/[a-zA-Z0-9=]+/';
			$response = preg_replace($pattern, 'KS', $response);
			$cachedResponse = preg_replace($pattern, 'KS', $cachedResponse);

			$pattern = '/s:\d+:/';
			$response = preg_replace($pattern, 's::', $response);
			$cachedResponse = preg_replace($pattern, 's::', $cachedResponse);

			$pattern = '/kaltura_player_\d+/';
			$response = preg_replace($pattern, 'KP', $response);
			$cachedResponse = preg_replace($pattern, 'KP', $cachedResponse);
			
			$format = isset($_REQUEST["format"]) ? $_REQUEST["format"] : KalturaResponseType::RESPONSE_TYPE_XML;				
			switch($format)
			{
				case KalturaResponseType::RESPONSE_TYPE_XML:
					$pattern = '/<executionTime>[0-9\.]+<\/executionTime>/';
					$testResult = (preg_replace($pattern, 'ET', $cachedResponse) == preg_replace($pattern, 'ET', $response));
					break;
					
				case KalturaResponseType::RESPONSE_TYPE_JSONP:
					$pattern = '/^[^\(]+/';
					$testResult = (preg_replace($pattern, 'CB', $cachedResponse) == preg_replace($pattern, 'CB', $response));
					break;
				
				default:
					$testResult = ($cachedResponse == $response);
					break;
			}
			
			if ($testResult)
				KalturaLog::log('conditional cache check: OK');			// we would have used the cache, and the response buffer do match
			else
			{
				KalturaLog::log('conditional cache check: FAILED key: '.$this->_cacheKey);		// we would have used the cache, but the response buffers do not match
				
				$outputFileBase = '/tmp/condCache/' . $this->_cacheKey;
				@$this->createDirForPath($outputFileBase);
				$cachedFileName = $outputFileBase . '-cached';
				$nonCachedFileName = $outputFileBase . '-new';
				@file_put_contents($cachedFileName, $cachedResponse);
				@file_put_contents($nonCachedFileName, $response);
			}
		}
	}

	public function setExpiry($expiry)
	{
		$this->_expiry = $expiry;
	}
	
	private function createDirForPath($filePath)
	{
		$dirname = dirname($filePath);
		if (!is_dir($dirname))
		{
			mkdir($dirname, 0777, true);
		}
	}
	

	private function hasCache()
	{
		// if the request is for warming the cache, disregard the cache and run the request
		$warmCacheHeader = self::getRequestHeaderValue(self::WARM_CACHE_HEADER);

		if ($warmCacheHeader !== false)
		{
			// if the request triggering the cache warmup was an https request, fool the code to treat the current request as https as well 
			if ($warmCacheHeader == "https")
				$_SERVER["HTTPS"] = "on";
						
			// make a trace in the access log of this being a warmup call
			header("X-Kaltura:cached-warmup-$warmCacheHeader,".$this->_cacheKey);
			return false;
		}

		if (!file_exists($this->_cacheDataFilePath))
		{
			// don't have any cached response for this key
			return false;
		}
		
		// get caching conditions
		$cacheExpiry = self::safeFileGetContents($this->_cacheExpiryFilePath);
		$conditions = self::safeFileGetContents($this->_cacheConditionsFilePath);

		// check the expiry
		if (!$cacheExpiry)
		{
			if ($conditions)
				$cacheExpiry = filemtime($this->_cacheDataFilePath) + self::CONDITIONAL_CACHE_EXPIRY;
			else
				$cacheExpiry = filemtime($this->_cacheDataFilePath) + $this->_expiry;
		}
		$cacheTTL = $cacheExpiry - time(); 

		if($cacheTTL <= 0)
		{
			// cached response is expired
			@unlink($this->_cacheDataFilePath);
			@unlink($this->_cacheLogFilePath);
			self::safeUnlink($this->_cacheHeadersFilePath);
			self::safeUnlink($this->_cacheExpiryFilePath);
			self::safeUnlink($this->_cacheConditionsFilePath);
			return false;
		}
			
		if ($cacheTTL < self::WARM_CACHE_INTERVAL) // 1 minute left for cache, lets warm it
		{
			self::warmCache($this->_cacheDataFilePath);	
		}
		
		// check the invalidation conditions
		if ($conditions)
		{
			if (!$this->isKSValid())
				return false;					// ks not valid, should not return from cache since the response may contain sensitive data
		
			list($invalidationKeys, $cachedInvalidationTime) = unserialize($conditions);
			$invalidationTime = self::getMaxInvalidationTime($invalidationKeys);
			if ($invalidationTime === null)		
				return false;					// failed to get the invalidation time, can't use cache
				
			if ($cachedInvalidationTime < $invalidationTime)
				return false;					// something changed since the response was cached

			$this->_wouldHaveUsedCondCache = true;			// XXXXXXX TODO: remove this
			return false;									// XXXXXXX TODO: remove this
		}
		
		return true;
	}
	
	private function isKSValid()
	{
		if (!$this->_ksRealStr || !$this->_ksHash)
			return false;			// no KS
		
		if ($this->_ksValidUntil && $this->_ksValidUntil < time())
			return false;			// KS is expired
			
		if (!function_exists('apc_fetch'))
			return false;			// no APC - can't get the partner secret here (DB not initialized)
		
		$adminSecret = apc_fetch('partner_admin_secret_' . $this->_ksPartnerId);
		if (!$adminSecret)
			return false;			// admin secret not found in APC, can't validate the KS
			
		if (sha1($adminSecret . $this->_ksRealStr) != $this->_ksHash)
			return false;			// wrong KS signature
			
		return true;
	}
	
	private static function getMaxInvalidationTime($invalidationKeys)
	{
		if (!kConf::get("query_cache_enabled") || !class_exists('Memcache'))
			return null;
		
		$memcache = new Memcache;	

		$res = @$memcache->connect(kConf::get("global_keys_memcache_host"), kConf::get("global_keys_memcache_port"));
		if (!$res)
			return null;			// failed to connect to memcache

		$cacheResult = $memcache->get($invalidationKeys);
		if ($cacheResult === false)
			return null;			// failed to get the invalidation keys
			
		if (!$cacheResult)
			return 0;				// no invalidation keys - no changes occured
			
		return max($cacheResult);
	}

	private function getCacheMode()
	{
		if ($this->_cacheStatus == self::CACHE_STATUS_DISABLED)
			return self::CACHE_MODE_NONE;
			
		$ks = null;
		try
		{
			$ks = kSessionUtils::crackKs($this->_ks);
		}
		catch(Exception $e){
			KalturaLog::err($e->getMessage());
			self::disableCache();
			return self::CACHE_MODE_NONE;
		}
		
		if(!$ks)
			return self::CACHE_MODE_ANONYMOUS;
			
		// force caching of actions listed in kConf even if admin ks is used
		foreach(kConf::get('v3cache_ignore_admin_ks') as $ignoreParams)
		{
			$matches = 0;
			
			foreach($ignoreParams as $key => $value)
			{
				if ($key == 'partner_id')
				{
					if ($ks->partner_id != $value)
						break;
				}
				else if (!isset($this->_params[$key]) || $this->_params[$key] != $value)
					break;
					
				$matches++;
			}
			
			if ($matches == count($ignoreParams))
				return self::CACHE_MODE_ANONYMOUS;
		}
		
		if (($ks->valid_until && $ks->valid_until < time()) ||	// don't cache when the KS is expired
			$ks->isSetLimitAction()) 							// don't cache when the KS has a limit on the number of actions
		{
			self::disableCache();
			return self::CACHE_MODE_NONE;
		}
        
		if (!$ks->isAdmin() && ($ks->user === "0" || $ks->user === null)) 	// can use anonymous caching if it's a widget session
		{
			return self::CACHE_MODE_ANONYMOUS;
		}
		
		if ($this->_cacheStatus != self::CACHE_STATUS_ANONYMOUS_ONLY)		// use conditional caching if possible
		{
			return self::CACHE_MODE_CONDITIONAL;
		}
		
		self::disableCache();
		return self::CACHE_MODE_NONE;
	}
	
	private function getKsData()
	{
		$str = base64_decode($this->_ks, true);
		
		if (strpos($str, "|") === false)
		{
			$userId = null;
			$type = null;
			$privileges = null;
		}
		else
		{
			@list($this->_ksHash, $this->_ksRealStr) = @explode("|", $str, 2);
			@list($this->_ksPartnerId, $dummy, $this->_ksValidUntil, $type, $dummy, $userId, $privileges) = @explode (";", $this->_ksRealStr);
		}
		return array("userId" => $userId, "type" => $type, "privileges" => $privileges );
	}

	private static function getRequestHeaderValue($headerName)
	{
		$headerName = "HTTP_".str_replace("-", "_", strtoupper($headerName));

		if (!isset($_SERVER[$headerName]))
			return false;

		return $_SERVER[$headerName];
	}


	private static function getRequestHeaders()
	{
		if(function_exists('apache_request_headers'))
			return apache_request_headers();
		
		foreach($_SERVER as $key => $value)
		{
			if(substr($key, 0, 5) == "HTTP_")
			{
				$key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
				$out[$key] = $value;
			}
		}
		return $out;
	}

	// warm cache by sending the current request asynchronously via a socket to localhost
	// apc is used to flag that an existing warmup request is already running. The flag has a TTL of 10 seconds, 
	// so in the case the warmup request failed another one can be ran after 10 seconds.
	// finalize IP passing (use getRemoteAddr code)
	// can the warm cache header get received via a warm request passed from the other DC?
	private function warmCache($key)
	{
		// require apc for checking whether warmup is already in progress
		if (!function_exists('apc_fetch'))
		return;

		$key = "cache-warmup-$key";

		// abort warming if a previous warmup started less than 10 seconds ago
		if (apc_fetch($key) !== false)
			return;

		// flag we are running a warmup for the current request
		apc_store($key, true, self::WARM_CACHE_TTL);

		$uri = $_SERVER["REQUEST_URI"];

		$fp = fsockopen('127.0.0.1', 80, $errno, $errstr, 1);

		if ($fp === false)
		{
			error_log("warmCache - Couldn't open a socket [".$uri."]", 0);
			return;
		}

		$method = $_SERVER["REQUEST_METHOD"];

		$out = "$method $uri HTTP/1.1\r\n";

		$sentHeaders = self::getRequestHeaders();
		$sentHeaders["Connection"] = "Close";

		// mark request as a warm cache request in order to disable caching and pass the http/https protocol (the warmup always uses http)
		$sentHeaders[self::WARM_CACHE_HEADER] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? "https" : "http";

		// if the request wasn't proxied pass the ip on the X-FORWARDED-FOR header
		if (!isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$sentHeaders["X-FORWARDED-FOR"] = $_SERVER['REMOTE_ADDR'];
			$sentHeaders["X-FORWARDED-SERVER"] = kConf::get('remote_addr_header_server');
		}

		foreach($sentHeaders as $header => $value)
		{
			$out .= "$header:$value\r\n";
		}

		$out .= "\r\n";

		if ($method == "POST")
		{
			$postParams = array();
			foreach ($_POST as $key => &$val) {
				if (is_array($val)) $val = implode(',', $val);
				{
					$postParams[] = $key.'='.urlencode($val);
				}
			}

			$out .= implode('&', $postParams);
		}

		fwrite($fp, $out);
		fclose($fp);
	}

	// This function avoids the 'file does not exist' warning
	private static function safeFileGetContents($fileName)
	{
		if (!file_exists($fileName))
			return null;
		return @file_get_contents($fileName);
	}

	// This function avoids the 'file does not exist' warning
	private static function safeUnlink($fileName)
	{
		if (!file_exists($fileName))
			return;
		@unlink($fileName);
	}
	
}
