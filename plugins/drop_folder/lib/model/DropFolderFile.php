<?php


/**
 * Skeleton subclass for representing a row from the 'drop_folder_file' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package plugins.dropFolder
 * @subpackage model
 */
class DropFolderFile extends BaseDropFolderFile
{

	const CUSTOM_DATA_HANDLE_BATCH_JOB_ID  = 'handle_batch_job_id';
	
	public function setFileSize($size)
	{
		parent::setFileSize($size);
		self::setFileSizeLastSetAt(time());	
	}
	
	public function getFileSizeLastSetAt($format = null)
	{
		return parent::getFileSizeLastSetAt($format);
	}
	
	/**
	 * @return string full path to file (drop folder path + file name)
	 */
	public function getFullPath()
	{
		$dropFolder = DropFolderPeer::retrieveByPK($this->getDropFolderId());
		if ($dropFolder && strlen($dropFolder->getPath()) > 0)
		{
			$fullPath = $dropFolder->getPath().'/'.$this->getFileName();
			return $fullPath;
		}
		return null;
	}
	
	public function setHandleBatchJobId($id)
	{
		$this->putInCustomData(self::CUSTOM_DATA_HANDLE_BATCH_JOB_ID, $id);
	}
	
	public function getHandleBatchJobId()
	{
		return $this->getFromCustomData(self::CUSTOM_DATA_HANDLE_BATCH_JOB_ID);
	}
		
} // DropFolderFile
