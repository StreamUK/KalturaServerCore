<?php
/**
 * @package plugins.youTubeDistribution
 * @subpackage lib
 */
class YouTubeDistributionLegacyStatusParser
{
	/**
	 * @var DOMDocument
	 */
	protected $doc;
	
	/**
	 * @var DOMXpath
	 */
	protected $xpath;
	
	/**
	 * @param string $xml
	 */
	public function __construct($xml)
	{
		$this->doc = new DOMDocument();
		$this->doc->loadXML($xml);
		$this->xpath = new DOMXPath($this->doc);
	}
	
	/**
	 * @param string $command
	 * @return string
	 */
	public function getStatusForAction($command)
	{
		$actionNode = $this->xpath->query("//*/action[@name='".$command."']")->item(0);
		if (is_null($actionNode))
			return null;
			
		$statusNode = $this->xpath->query("status", $actionNode)->item(0);
		if (is_null($statusNode))
			return null;
			
		return $statusNode->nodeValue;
	}
	
	public function getReferenceId()
	{
		$reference = $this->xpath->query("//*/id[@type='Reference ID']")->item(0);
		if ($reference)
			return $reference->nodeValue;
		return null;
	}

	public function getAssetId()
	{
		$reference = $this->xpath->query("//*/id[@type='Asset ID']")->item(0);
		if ($reference)
			return $reference->nodeValue;
		return null;
	}

	public function getVideoId()
	{
		$reference = $this->xpath->query("//*/id[@type='Video ID']")->item(0);
		if ($reference)
			return $reference->nodeValue;
		return null;
	}
}