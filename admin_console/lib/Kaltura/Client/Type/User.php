<?php
/**
 * @package Admin
 * @subpackage Client
 */
class Kaltura_Client_Type_User extends Kaltura_Client_ObjectBase
{
	public function getKalturaObjectType()
	{
		return 'KalturaUser';
	}
	
	public function __construct(SimpleXMLElement $xml = null)
	{
		parent::__construct($xml);
		
		if(is_null($xml))
			return;
		
		$this->id = (string)$xml->id;
		if(count($xml->partnerId))
			$this->partnerId = (int)$xml->partnerId;
		$this->screenName = (string)$xml->screenName;
		$this->fullName = (string)$xml->fullName;
		$this->email = (string)$xml->email;
		if(count($xml->dateOfBirth))
			$this->dateOfBirth = (int)$xml->dateOfBirth;
		$this->country = (string)$xml->country;
		$this->state = (string)$xml->state;
		$this->city = (string)$xml->city;
		$this->zip = (string)$xml->zip;
		$this->thumbnailUrl = (string)$xml->thumbnailUrl;
		$this->description = (string)$xml->description;
		$this->tags = (string)$xml->tags;
		$this->adminTags = (string)$xml->adminTags;
		if(count($xml->gender))
			$this->gender = (int)$xml->gender;
		if(count($xml->status))
			$this->status = (int)$xml->status;
		if(count($xml->createdAt))
			$this->createdAt = (int)$xml->createdAt;
		if(count($xml->updatedAt))
			$this->updatedAt = (int)$xml->updatedAt;
		$this->partnerData = (string)$xml->partnerData;
		if(count($xml->indexedPartnerDataInt))
			$this->indexedPartnerDataInt = (int)$xml->indexedPartnerDataInt;
		$this->indexedPartnerDataString = (string)$xml->indexedPartnerDataString;
		if(count($xml->storageSize))
			$this->storageSize = (int)$xml->storageSize;
		$this->password = (string)$xml->password;
		$this->firstName = (string)$xml->firstName;
		$this->lastName = (string)$xml->lastName;
		if(!empty($xml->isAdmin))
			$this->isAdmin = true;
		if(count($xml->lastLoginTime))
			$this->lastLoginTime = (int)$xml->lastLoginTime;
		if(count($xml->statusUpdatedAt))
			$this->statusUpdatedAt = (int)$xml->statusUpdatedAt;
		if(count($xml->deletedAt))
			$this->deletedAt = (int)$xml->deletedAt;
		if(!empty($xml->loginEnabled))
			$this->loginEnabled = true;
		$this->roleIds = (string)$xml->roleIds;
		$this->roleNames = (string)$xml->roleNames;
		if(!empty($xml->isAccountOwner))
			$this->isAccountOwner = true;
	}
	/**
	 * 
	 *
	 * @var string
	 */
	public $id = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $partnerId = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $screenName = null;

	/**
	 * DEPRECATED
	 *
	 * @var string
	 */
	public $fullName = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $email = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $dateOfBirth = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $country = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $state = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $city = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $zip = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $thumbnailUrl = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $description = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tags = null;

	/**
	 * Admin tags can be updated only by using an admin session
	 *
	 * @var string
	 */
	public $adminTags = null;

	/**
	 * 
	 *
	 * @var Kaltura_Client_Enum_Gender
	 */
	public $gender = null;

	/**
	 * 
	 *
	 * @var Kaltura_Client_Enum_UserStatus
	 */
	public $status = null;

	/**
	 * Creation date as Unix timestamp (In seconds)
	 *
	 * @var int
	 * @readonly
	 */
	public $createdAt = null;

	/**
	 * Last update date as Unix timestamp (In seconds)
	 *
	 * @var int
	 * @readonly
	 */
	public $updatedAt = null;

	/**
	 * Can be used to store various partner related data as a string 
	 *
	 * @var string
	 */
	public $partnerData = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $indexedPartnerDataInt = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $indexedPartnerDataString = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $storageSize = null;

	/**
	 * 
	 *
	 * @var string
	 * @insertonly
	 */
	public $password = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $firstName = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $lastName = null;

	/**
	 * 
	 *
	 * @var bool
	 */
	public $isAdmin = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $lastLoginTime = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $statusUpdatedAt = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $deletedAt = null;

	/**
	 * 
	 *
	 * @var bool
	 * @readonly
	 */
	public $loginEnabled = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $roleIds = null;

	/**
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $roleNames = null;

	/**
	 * 
	 *
	 * @var bool
	 * @readonly
	 */
	public $isAccountOwner = null;


}

