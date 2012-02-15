<?php
/**
 * @subpackage lib
 */
class AttUverseDistributionFeedHelper
{
	/**
	 * @var DOMDocument
	 */
	protected $doc;
	
	/**
	 * @var DOMXPath
	 */
	protected $xpath;
	
	/**
	 * @var DOMElement
	 */
	protected $item;	
	
	/**
	 * @var DOMElement
	 */
	protected $tags;
	
	/**
	 * @var DOMElement
	 */
	protected $tag;
	
	/**
	 * @var DOMElement
	 */
	protected $category;
	
	/**
	 * @var DOMElement
	 */
	protected $content;
	
	/**
	 * @var DOMElement
	 */
	protected $thumbnail;
	
	/**
	 * @var AttUverseDistributionProfile
	 */
	protected $distributionProfile;
			
	
	/**
	 * @param $templateName
	 * @param $distributionProfile
	 */
	public function __construct($templateName, AttUverseDistributionProfile $distributionProfile)
	{
		$this->distributionProfile = $distributionProfile;
		$xmlTemplate = realpath(dirname(__FILE__) . '/../') . '/xml_templates/' . $templateName;
		$this->doc = new DOMDocument();
		$this->doc->formatOutput = true;
		$this->doc->preserveWhiteSpace = false;
		$this->doc->load($xmlTemplate);				
		
		$this->xpath = new DOMXPath($this->doc);
		$this->xpath->registerNamespace('media', 'http://search.yahoo.com/mrss/');
		$this->xpath->registerNamespace('dcterms', 'http://purl.org/dc/terms/');		
		
		// item node template
		$node = $this->xpath->query('/rss/channel/item')->item(0);
		$this->item = $node->cloneNode(true);
		$node->parentNode->removeChild($node);		
		
		// tag node template
		$node = $this->xpath->query('tags/tag', $this->item)->item(0);
		$this->tag = $node->cloneNode(true);
		$this->tags = $node->parentNode;
		$node->parentNode->removeChild($node);	
		
		// category node template
		$node = $this->xpath->query('category', $this->item)->item(0);
		$this->category= $node->cloneNode(true);
		//$node->parentNode->removeChild($node);
		
		// content node template
		$node = $this->xpath->query('content', $this->item)->item(0);
		$this->content = $node->cloneNode(true);
		//$node->parentNode->removeChild($node);
		
		// thumbnail node template
		$node = $this->xpath->query('thumbnail', $this->item)->item(0);
		$this->thumbnail = $node->cloneNode(true);
		//$node->parentNode->removeChild($node);
				
	}
		
	
	private function getValueForField($fieldName)
	{
	    if (isset($this->fieldValues[$fieldName])) {
	        return $this->fieldValues[$fieldName];
	    }
	    return null;
	}	
	
	/**
	 * @param string $xpath
	 * @param string $value
	 */
	public function setNodeValue($xpath, $value, DOMNode $contextnode = null)
	{
		if ($contextnode) {
			$node = $this->xpath->query($xpath, $contextnode)->item(0);
		}
		else { 
			$node = $this->xpath->query($xpath)->item(0);
		}
		if (!is_null($node))
		{
			// if CDATA inside, set the value of CDATA
			if ($node->childNodes->length > 0 && $node->childNodes->item(0)->nodeType == XML_CDATA_SECTION_NODE)
				$node->childNodes->item(0)->nodeValue = htmlentities($value);
			else
				$node->nodeValue = htmlentities($value);
		}
	}
	
	/**
	 * @param string $xpath
	 * @param string $value
	 */
	public function getNodeValue($xpath)
	{
		$node = $this->xpath->query($xpath)->item(0);
		if (!is_null($node))
			return $node->nodeValue;
		else
			return null;
	}
	
	/**	 
	 * @param array $values
	 * @param array $flavorAssets
	 * @param array $remoteAssetFileUrls
	 * @param array $thumbAssets
	 * @param array $remoteThumbailFileUrls
	 */
	public function addItem(array $values, array $flavorAssets = null, $remoteAssetFileUrls = null, array $thumbAssets = null, $remoteThumbailFileUrls = null)
	{		
		$item = $this->item->cloneNode(true);
		$channelNode = $this->xpath->query('/rss/channel', $item)->item(0);
		$channelNode->appendChild($item);
		
		//setting tags node to the current item tags node
		$this->tags = $this->xpath->query('tags', $item)->item(0);
		
		$this->setNodeValue('entryId', $values[AttUverseDistributionField::ITEM_ENTRY_ID], $item);
		
		//date fields
		$createdAt = date('c', $values[AttUverseDistributionField::ITEM_CREATED_AT]);
		$this->setNodeValue('createdAt', $createdAt, $item);
		
		$updatedAt = date('c', $values[AttUverseDistributionField::ITEM_UPDATED_AT]);
		$this->setNodeValue('updatedAt', $updatedAt, $item);
		
		$startDate = date('c', $values[AttUverseDistributionField::ITEM_START_DATE]);
		$this->setNodeValue('startDate', $startDate, $item);
		
		$endDate = date('c', $values[AttUverseDistributionField::ITEM_END_DATE]);
		$this->setNodeValue('endDate', $endDate, $item);
		
		$this->setNodeValue('title', $values[AttUverseDistributionField::ITEM_TITLE], $item);
		$this->setNodeValue('description', $values[AttUverseDistributionField::ITEM_DESCRIPTION], $item);
		
		//tags
		$this->addTags($this->tags, $values[AttUverseDistributionField::ITEM_TAGS]);
		//categories
		$this->addCategories($item, $values[AttUverseDistributionField::ITEM_CATEGORIES]);

		//content
		if (!is_null($flavorAssets) && is_array($flavorAssets) && count($flavorAssets)>0)
		{
			$this->setFlavorAsset($item, $flavorAssets, $remoteAssetFileUrls);
		}
		
		//thumbnail
		if (!is_null($thumbAssets) && is_array($thumbAssets) && count($thumbAssets)>0)
		{
			$this->setThumbAsset($item, $thumbAssets, $remoteThumbailFileUrls);			
		}	
		
		//metadata fields
		$this->setNodeValue('customData/metadata/ShortTitle', $values[AttUverseDistributionField::ITEM_METADATA_SHORT_TITLE], $item);
		$this->setNodeValue('customData/metadata/TuneIn', $values[AttUverseDistributionField::ITEM_METADATA_TUNEIN], $item);
		$this->setNodeValue('customData/metadata/ContentRating', $values[AttUverseDistributionField::ITEM_METADATA_CONTENT_RATING], $item);
		$this->setNodeValue('customData/metadata/LegalDisclaimer', $values[AttUverseDistributionField::ITEM_METADATA_LEGAL_DISCLAIMER], $item);
		$this->setNodeValue('customData/metadata/Genre', $values[AttUverseDistributionField::ITEM_METADATA_GENRE], $item);				
	}
	
	public function setChannelTitle($value)
	{
		$this->setNodeValue('/rss/channel/title', $value);
	}
	
	public function addTags($tagsNode, $value)
	{
		$tags = explode(',', $value);
		if ($tags)
		{									
			foreach ($tags as $tag)
			{
				if ($tag){
					$tagNode = $this->tag->cloneNode(true);				
					$tagsNode->appendChild($tagNode);
					$this->setNodeValue('.', $tag, $tagNode);
				}
			}
		}
		//remove <tag> tag from xml
		else {			
			$node = $this->tag->cloneNode(true);
			$parentNode = $node->parentNode;	
			$parentNode->removeChild($node);
		}
	}
	
	public function addCategories($item, $categoryValue)
	{	
		$categories = explode(',', $categoryValue);
		if ($categories)
		{	
			$node = $this->xpath->query('category', $item)->item(0);									
			foreach ($categories as $category)
			{											
				if ($category){	
					$categoryNode = $this->category->cloneNode(true);						
					$item->insertBefore($categoryNode, $node);		
					$this->setNodeValue('.', $category, $categoryNode);
					$this->setNodeValue('@name', $category, $categoryNode);													
				}
			}		
			$node->parentNode->removeChild($node);				
		}	
	}
	
	
	/**
	 * @param DOMElement $item
	 * @param array $flavorAssets
	 * @param array $remoteAssetFileUrls
	 */
	public function setFlavorAsset($item, array $flavorAssets, $remoteAssetFileUrls)
	{			
		if(count($flavorAssets))
		{
			$node = $this->xpath->query('content', $item)->item(0);
			foreach ($flavorAssets as $flavorAsset)
			{				
				/* @var $flavorAsset flavorAsset */
				$flavorAssetId = $flavorAsset->getId();
				$url = '';
				if (!is_null($remoteAssetFileUrls))
				{
					$url = $remoteAssetFileUrls[$flavorAssetId];
				}
				$contentNode = $this->content->cloneNode(true);											
				$item->insertBefore($contentNode, $node);		
				$this->setNodeValue('@containerFormat', $flavorAsset->getContainerFormat(), $contentNode);
				$this->setNodeValue('@url', $url, $contentNode);
				$this->setNodeValue('@height', $flavorAsset->getHeight(), $contentNode);
				$this->setNodeValue('@width',$flavorAsset->getWidth() ,$contentNode);
				$this->setNodeValue('@url', $url, $contentNode);
			}
			$node->parentNode->removeChild($node);
		}
	}
	
	/**
	 * @param DOMElement $item
	 * @param array $thumbAssets
	 * @param array $remoteThumbailFileUrls
	 */
	public function setThumbAsset($item, array $thumbAssets, $remoteThumbailFileUrls)
	{
		if(count($thumbAssets))
		{				
			$node = $this->xpath->query('thumbnail', $item)->item(0);						
			foreach ($thumbAssets as $thumbAsset)
			{
				/* @var $thumbAsset thumbAsset */ 			
				$thumbAssetId = $thumbAsset->getId();
				$url = ''; 
				if (!is_null($remoteThumbailFileUrls))
				{
					$url = $remoteThumbailFileUrls[$thumbAssetId];					
				}
				$thumbnailNode = $this->thumbnail->cloneNode(true);												
				$item->insertBefore($thumbnailNode, $node);		
				$this->setNodeValue('@url', $url, $thumbnailNode);
				$this->setNodeValue('@height', $thumbAsset->getHeight(), $thumbnailNode);
				$this->setNodeValue('@width',$thumbAsset->getWidth() ,$thumbnailNode);
			}
			$node->parentNode->removeChild($node);	
		}	
	}
	
	public function getXml()
	{
		return $this->doc->saveXML();
	}
	
}