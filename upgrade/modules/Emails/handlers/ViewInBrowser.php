<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/
include_once 'modules/Users/Users.php';

class Emails_ViewInBrowser_Handler {

	function isRequestAuthorized(Vtiger_Request $request) {
		$urlParameters = array();
		$urlParameters['rid'] = $request->get('rid');
		$urlParameters['applicationKey'] = vglobal('application_unique_key');

		$verifyValue = $request->get('rv');
		$url = http_build_query($urlParameters);

		if ($verifyValue == md5($url))
			return true;
		else
			return false;
	}

	/**
	 * Function verifies the application key and get the html content
	 * by replacing merge tags with appropriate values.
	 * @global type $current_user
	 * @global type $site_URL
	 * @param type $data
	 */
	function viewInBrowser($data) {
		$request = new Vtiger_Request(vtlib_purify($_REQUEST));
		$isRequestAuthorized = $this->isRequestAuthorized($request);
		if ($isRequestAuthorized) {
			$applicationKey = $request->get('applicationKey');
			if (vglobal('application_unique_key') !== $applicationKey) {
				exit;
			}
			global $current_user, $site_URL;
			$current_user = Users::getActiveAdminUser();

			$emailId = $data['emailId'];
			$parentModule = $data['parentModule'];

			$shorturlId = $request->get('id');
			$recipientIdWithModule = $request->get('rid');
			$recipientId = substr($recipientIdWithModule, 1);
			$recordModel = Emails_Record_Model::getInstanceById($emailId);
			$recordModel->updateTrackDetails($recipientId); // Function increases track access count to 1
			$description = $recordModel->get('description');
			$urlParameters = array();
			$urlParameters['rid'] = $recipientIdWithModule;
			$urlParameters['applicationKey'] = $applicationKey;
			$url = http_build_query($urlParameters);
			$rlock = md5($url);
			$viewInBrowserMergeTagURL = $site_URL . "/shorturl.php?id=$shorturlId&$url&rv=$rlock";
			$mergedDescription = str_replace(EmailTemplates_Module_Model::$BROWSER_MERGE_TAG, $viewInBrowserMergeTagURL, $description);
			$htmlContent = getMergedDescription($mergedDescription, $recipientId, $parentModule);
			header('Content-Type: text/html; charset=utf-8');
			$docTypeAddedContent = self::addDoctypeIfNotExist($htmlContent);
			echo $docTypeAddedContent;
		}
	}

	/**
	 * Function to add doctype if not exist
	 * @staticvar string $DEFAULT_DOCTYPE
	 * @param type $htmlContent
	 * @return string
	 */
	static function addDoctypeIfNotExist($htmlContent) {
		$content = decode_html($htmlContent);
		$DEFAULT_DOCTYPE = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
		$matches = array();
		preg_match("/(<!DOCTYPE.+\>)/i", $content, $matches);
		if (!empty($matches)) {
			$docTypeRemovedContent = str_replace($matches[0], " ", $content);
			$htmlContent = $matches[0] . "<div style='padding:0px 300px;'>" . $docTypeRemovedContent . "</div>";
		} else {
			$htmlContent = $DEFAULT_DOCTYPE . "<div style='padding:0px 300px;'>" . $content . "</div>";
		}
		return $htmlContent;
	}

}
?>