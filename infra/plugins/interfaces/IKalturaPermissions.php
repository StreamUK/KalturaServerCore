<?php
interface IKalturaPermissions extends IKalturaBase
{
	/**
	 * @param int $partnerId
	 * @return bool
	 */
	public static function isAllowedPartner($partnerId);
}