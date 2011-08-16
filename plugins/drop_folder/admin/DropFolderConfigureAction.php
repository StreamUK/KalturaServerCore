<?php
class DropFolderConfigureAction extends KalturaAdminConsolePlugin
{	
	/**
	 * @return string - absolute file path of the phtml template
	 */
	public function getTemplatePath()
	{
		return realpath(dirname(__FILE__));
	}
	
	public function getRequiredPermissions()
	{
		return array(Kaltura_Client_Enum_PermissionName::SYSTEM_ADMIN_DROP_FOLDER_MODIFY);
	}
	
	public function doAction(Zend_Controller_Action $action)
	{
		$action->getHelper('layout')->disableLayout();
		$request = $action->getRequest();
		$dropFolderId = $this->_getParam('drop_folder_id');
		$partnerId = $this->_getParam('new_partner_id');
		$dropFolderForm = null;
		
		try
		{
			if ($request->isPost())
			{
				$partnerId = $this->_getParam('partnerId');
				$dropFolderForm = new Form_DropFolderConfigure($partnerId);
				$this->processForm($dropFolderForm, $request->getPost(), $dropFolderId);
			}
			else
			{
				if (!is_null($dropFolderId))
				{
					$client = Infra_ClientHelper::getClient();
					$dropFolderPluginClient = Kaltura_Client_DropFolder_Plugin::get($client);
					$dropFolder = $dropFolderPluginClient->dropFolder->get($dropFolderId);
					$partnerId = $dropFolder->partnerId;
					$dropFolderForm = new Form_DropFolderConfigure($partnerId);
					$dropFolderForm->populateFromObject($dropFolder, false);
				}
				else
				{
					$dropFolderForm = new Form_DropFolderConfigure($partnerId);
					$dropFolderForm->getElement('partnerId')->setValue($partnerId);
				}
			}
		}
		catch(Exception $e)
		{
			KalturaLog::err($e->getMessage() . "\n" . $e->getTraceAsString());
			$action->view->errMessage = $e->getMessage();
		}
		
		$action->view->form = $dropFolderForm;
	}
	
	private function processForm(Form_DropFolderConfigure $form, $formData, $dropFolderId = null)
	{
		if ($form->isValid($formData))
		{
			$client = Infra_ClientHelper::getClient();
			$dropFolderPluginClient = Kaltura_Client_DropFolder_Plugin::get($client);
			
			$dropFolder = $form->getObject("Kaltura_Client_DropFolder_Type_DropFolder", $formData, false, true);
			unset($dropFolder->id);
			
			if (is_null($dropFolderId)) {
				$dropFolder->status = Kaltura_Client_DropFolder_Enum_DropFolderStatus::ENABLED;
				$dropFolder->type = Kaltura_Client_DropFolder_Enum_DropFolderType::LOCAL;
				$responseDropFolder = $dropFolderPluginClient->dropFolder->add($dropFolder);
			}
			else {
				$responseDropFolder = $dropFolderPluginClient->dropFolder->update($dropFolderId, $dropFolder);
			}
			$form->setAttrib('class', 'valid');
		}
		else
		{
			$form->populate($formData);
		}
	}
}

