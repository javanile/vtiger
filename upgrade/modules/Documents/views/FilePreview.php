<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Documents_FilePreview_View extends Vtiger_IndexAjax_View {

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();

		if(!Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $request->get('record'))) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', $moduleName));
		}
	}

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$basicFileTypes = array('txt','csv','ics');
		$imageFileTypes = array('image/gif','image/png','image/jpeg');
		//supported by video js
		$videoFileTypes = array('video/mp4','video/ogg','audio/ogg','video/webm');
		$audioFileTypes = array('audio/mp3','audio/mpeg','audio/wav');
		//supported by viewer js
		$opendocumentFileTypes = array('odt','ods','odp','fodt');

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId,$moduleName);
		$fileDetails = $recordModel->getFileDetails();

		$fileContent = false;
		if (!empty ($fileDetails)) {
			$filePath = $fileDetails['path'];
			$fileName = $fileDetails['name'];

			if ($recordModel->get('filelocationtype') == 'I') {
				$fileName = html_entity_decode($fileName, ENT_QUOTES, vglobal('default_charset'));
				$savedFile = $fileDetails['attachmentsid']."_".$fileName;

				$fileSize = filesize($filePath.$savedFile);
				$fileSize = $fileSize + ($fileSize % 1024);

				if (fopen($filePath.$savedFile, "r")) {
					$fileContent = fread(fopen($filePath.$savedFile, "r"), $fileSize);
				}
			}
		}

		$path = $fileDetails['path'].$fileDetails['attachmentsid'].'_'.$fileDetails['name'];
		$type = $fileDetails['type'];
		$contents = $fileContent;
		$filename = $fileDetails['name'];
		$parts = explode('.',$filename);
		if ($recordModel->get('filestatus') && $recordModel->get('filename') && $recordModel->get('filelocationtype') === 'I') {
			$downloadUrl =  $recordModel->getDownloadFileURL();
		}
		//support for plain/text document
		$extn = 'txt';
		if(count($parts) > 1){
			$extn = end($parts);
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME',$moduleName);
		if(in_array($extn,$basicFileTypes))
			$viewer->assign('BASIC_FILE_TYPE','yes');
		else if(in_array($type,$videoFileTypes))
			$viewer->assign('VIDEO_FILE_TYPE','yes');
		else if(in_array($type,$imageFileTypes))
			$viewer->assign('IMAGE_FILE_TYPE','yes');
		else if(in_array($type,$audioFileTypes))
			$viewer->assign('AUDIO_FILE_TYPE','yes');
		else if (in_array($extn, $opendocumentFileTypes)) {
			$viewer->assign('OPENDOCUMENT_FILE_TYPE', 'yes');
			$downloadUrl .= "&type=$extn";
		} else if ($extn == 'pdf') {
			$viewer->assign('PDF_FILE_TYPE', 'yes');
		} else {
			$viewer->assign('FILE_PREVIEW_NOT_SUPPORTED','yes');
		}

		$viewer->assign('DOWNLOAD_URL',$downloadUrl);
		$viewer->assign('FILE_PATH',$path);
		$viewer->assign('FILE_NAME',$filename);
		$viewer->assign('FILE_EXTN',$extn);
		$viewer->assign('FILE_TYPE',$type);
		$viewer->assign('FILE_CONTENTS',$contents);
		global $site_URL;
		$viewer->assign('SITE_URL',$site_URL);

		echo $viewer->view('FilePreview.tpl',$moduleName,true);
	}

}