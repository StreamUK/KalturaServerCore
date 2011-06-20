<?php
/**
 * @package plugins.msnDistribution
 * @subpackage lib
 */
class MsnDistributionProvider implements IDistributionProvider
{
	/**
	 * @var MsnDistributionProvider
	 */
	protected static $instance;
	
	protected function __construct()
	{
		
	}
	
	/**
	 * @return MsnDistributionProvider
	 */
	public static function get()
	{
		if(!self::$instance)
			self::$instance = new MsnDistributionProvider();
			
		return self::$instance;
	}
	
	/* (non-PHPdoc)
	 * @see IDistributionProvider::getType()
	 */
	public function getType()
	{
		return MsnDistributionPlugin::getDistributionProviderTypeCoreValue(MsnDistributionProviderType::MSN);
	}
	
	/**
	 * @return string
	 */
	public function getName()
	{
		return 'MSN';
	}

	/* (non-PHPdoc)
	 * @see IDistributionProvider::isDeleteEnabled()
	 */
	public function isDeleteEnabled()
	{
		return true;
	}

	/* (non-PHPdoc)
	 * @see IDistributionProvider::isUpdateEnabled()
	 */
	public function isUpdateEnabled()
	{
		return true;
	}

	/* (non-PHPdoc)
	 * @see IDistributionProvider::isMediaUpdateEnabled()
	 */
	public function isMediaUpdateEnabled()
	{
		return true;
	}

	/* (non-PHPdoc)
	 * @see IDistributionProvider::isReportsEnabled()
	 */
	public function isReportsEnabled()
	{
		return true;
	}

	/* (non-PHPdoc)
	 * @see IDistributionProvider::isScheduleUpdateEnabled()
	 */
	public function isScheduleUpdateEnabled()
	{
		return true;
	}

	/* (non-PHPdoc)
	 * @see IDistributionProvider::useDeleteInsteadOfUpdate()
	 */
	public function useDeleteInsteadOfUpdate()
	{
		return false;
	}

	/* (non-PHPdoc)
	 * @see IDistributionProvider::getJobIntervalBeforeSunrise()
	 */
	public function getJobIntervalBeforeSunrise()
	{
		return 0;
	}

	/* (non-PHPdoc)
	 * @see IDistributionProvider::getJobIntervalBeforeSunset()
	 */
	public function getJobIntervalBeforeSunset()
	{
		return 0;
	}

	/* (non-PHPdoc)
	 * @see IDistributionProvider::getUpdateRequiredEntryFields()
	 */
	public function getUpdateRequiredEntryFields($distributionProfileId = null)
	{
		if(kConf::hasParam('msn_update_required_entry_fields'))
			return kConf::get('msn_update_required_entry_fields');
			
		return array();
	}

	/* (non-PHPdoc)
	 * @see IDistributionProvider::getUpdateRequiredMetadataXPaths()
	 */
	public function getUpdateRequiredMetadataXPaths($distributionProfileId = null)
	{
		if(kConf::hasParam('msn_update_required_metadata_xpaths'))
			return kConf::get('msn_update_required_metadata_xpaths');
			
		return array();
	}
	
	/**
	 * @param string $entryId
	 * @param KalturaMsnDistributionJobProviderData $providerData
	 * @return string
	 */
	public static function generateDeleteXML($entryId, KalturaMsnDistributionJobProviderData $providerData)
	{
		$xml = self::generateXML($entryId, $providerData);
		if(!$xml)
		{
			KalturaLog::err("No XML returned for entry [$entryId]");
			return null;
		}
	
		// change end time to 5 days from now (it's an MSN hack)
		$fiveDaysFromNow = date('Y-m-d\TH:i:s\Z', time() + (5 * 24 * 60 * 60));
		
		$nodes = $xml->getElementsByTagName('activeEndDate');
		foreach($nodes as $node)
			$node->replaceChild($xml->createTextNode($fiveDaysFromNow), $node->firstChild);
		
		$nodes = $xml->getElementsByTagName('searchableEndDate');
		foreach($nodes as $node)
			$node->replaceChild($xml->createTextNode($fiveDaysFromNow), $node->firstChild);
			
		$nodes = $xml->getElementsByTagName('archiveEndDate');
		foreach($nodes as $node)
			$node->replaceChild($xml->createTextNode($fiveDaysFromNow), $node->firstChild);
			
		return $xml->saveXML();
	}
	
	/**
	 * @param string $entryId
	 * @param KalturaMsnDistributionJobProviderData $providerData
	 * @return string
	 */
	public static function generateUpdateXML($entryId, KalturaMsnDistributionJobProviderData $providerData)
	{
		$xml = self::generateXML($entryId, $providerData);
		if(!$xml)
		{
			KalturaLog::err("No XML returned for entry [$entryId]");
			return null;
		}
		
		return $xml->saveXML();
	}
	
	/**
	 * @param string $entryId
	 * @param KalturaMsnDistributionJobProviderData $providerData
	 * @return string
	 */
	public static function generateSubmitXML($entryId, KalturaMsnDistributionJobProviderData $providerData)
	{
		$xml = self::generateXML($entryId, $providerData);
		if(!$xml)
		{
			KalturaLog::err("No XML returned for entry [$entryId]");
			return null;
		}
		
		return $xml->saveXML();
	}
	
	/**
	 * @param string $entryId
	 * @param KalturaMsnDistributionJobProviderData $providerData
	 * @return DOMDocument
	 */
	protected static function generateXML($entryId, KalturaMsnDistributionJobProviderData $providerData)
	{
		$entry = entryPeer::retrieveByPKNoFilter($entryId);
		if(!$entry)
			return null;
			
		$mrss = kMrssManager::getEntryMrss($entry);
		if(!$mrss)
		{
			KalturaLog::err("No MRSS returned for entry [$entryId]");
			return null;
		}
			
		$xml = new DOMDocument();
		if(!$xml->loadXML($mrss))
		{
			KalturaLog::err("Could not load MRSS as XML for entry [$entryId]");
			return null;
		}
		
		$xslPath = realpath(dirname(__FILE__) . '/../') . '/xml/submit.xsl';
		if(!file_exists($xslPath))
		{
			KalturaLog::err("XSL file not found [$xslPath]");
			return null;
		}
		$xsl = new DOMDocument();
		$xsl->load($xslPath);
			
		// set variables in the xsl
		$varNodes = $xsl->getElementsByTagName('variable');
		foreach($varNodes as $varNode)
		{
			$nameAttr = $varNode->attributes->getNamedItem('name');
			if(!$nameAttr)
				continue;
				
			$name = $nameAttr->value;
			if($name && $providerData->$name)
			{
				$varNode->textContent = $providerData->$name;
				$varNode->appendChild($xsl->createTextNode($providerData->$name));
			}
		}

		$proc = new XSLTProcessor;
		$proc->registerPHPFunctions();
		$proc->importStyleSheet($xsl);
		
		$xml = $proc->transformToDoc($xml);
		$xml->encoding = 'UTF-8';
		$xml->documentElement->removeAttributeNS('http://php.net/xsl', 'php');
		
		if(!$xml)
		{
			KalturaLog::err("XML Transformation failed");
			return null;
		}
			
		// TODO create validation XSD
		$xsdPath = realpath(dirname(__FILE__) . '/../') . '/xml/submit.xsd';
		if(file_exists($xsdPath) && !$xml->schemaValidate($xsdPath))
		{
			KalturaLog::err("Schema validation failed");		
			return null;
		}
		
		return $xml;
	}
}