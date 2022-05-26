<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_Vtiger_CompanyDetailsSave_Action extends Settings_Vtiger_Basic_Action {

	public function process(Vtiger_Request $request) {
		$moduleModel = Settings_Vtiger_CompanyDetails_Model::getInstance();
		$reloadUrl = $moduleModel->getIndexViewUrl();

		try{
			$this->Save($request);
		} catch(Exception $e) {
			if($e->getMessage() == "LBL_INVALID_IMAGE") {
				$reloadUrl .= '&error=LBL_INVALID_IMAGE';
			} else if($e->getMessage() == "LBL_FIELDS_INFO_IS_EMPTY") {
				$reloadUrl = $moduleModel->getEditViewUrl() . '&error=LBL_FIELDS_INFO_IS_EMPTY';
			}
		}
		header('Location: ' . $reloadUrl);
	}

	public function Save(Vtiger_Request $request) {
		$moduleModel = Settings_Vtiger_CompanyDetails_Model::getInstance();
		$status = false;
		if ($request->get('organizationname')) {
			$saveLogo = $status = true;
			if(!empty($_FILES['logo']['name'])) {
				$logoDetails = $_FILES['logo'];
				$fileType = explode('/', $logoDetails['type']);
				$fileType = $fileType[1];

				if (!$logoDetails['size'] || !in_array($fileType, Settings_Vtiger_CompanyDetails_Model::$logoSupportedFormats)) {
					$saveLogo = false;
				}

				//mime type check
				$mimeType = mime_content_type($logoDetails['tmp_name']);
				$mimeTypeContents = explode('/', $mimeType);
				if (!$logoDetails['size'] || $mimeTypeContents[0] != 'image' || !in_array($mimeTypeContents[1], Settings_Vtiger_CompanyDetails_Model::$logoSupportedFormats)) {
					$saveLogo = false;
				}

				// Check for php code injection
				$imageContents = file_get_contents($logoDetails["tmp_name"]);
				if (preg_match('/(<\?php?(.*?))/i', $imageContents) == 1) {
					$saveLogo = false;
				}
				if ($saveLogo) {
					$logoName = ltrim(basename(' '.Vtiger_Util_Helper::sanitizeUploadFileName($logoDetails['name'], vglobal('upload_badext'))));
					$moduleModel->saveLogo($logoName);
				}
			}else{
				$saveLogo = true;
			}
			$fields = $moduleModel->getFields();
			foreach ($fields as $fieldName => $fieldType) {
				$fieldValue = $request->get($fieldName);
				if ($fieldName === 'logoname') {
					if (!empty($logoDetails['name'])) {
						$fieldValue = decode_html(ltrim(basename(" " . $logoDetails['name'])));
					} else {
						$fieldValue = decode_html($moduleModel->get($fieldName));
					}
				}
				// In OnBoard company detail page we will not be sending all the details
				if($request->has($fieldName) || ($fieldName == "logoname")) {
					$moduleModel->set($fieldName, $fieldValue);
				}
			}
			$moduleModel->save();
		}
		if ($saveLogo && $status) {
			return ;
		} else if (!$saveLogo) {
			throw new Exception('LBL_INVALID_IMAGE',103);
			//$reloadUrl .= '&error=';
		} else {
			throw new Exception('LBL_FIELDS_INFO_IS_EMPTY',103);
			//$reloadUrl = $moduleModel->getEditViewUrl() . '&error=';
		}
		return;
	}

	public function validateRequest(Vtiger_Request $request) {
		$request->validateWriteAccess();
	}
}
