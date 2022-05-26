<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

require_once 'vtlib/Vtiger/Cron.php';
class Reports_ScheduleReports_Model extends Vtiger_Base_Model {

	static $SCHEDULED_DAILY = 1;
	static $SCHEDULED_WEEKLY = 2;
	static $SCHEDULED_MONTHLY_BY_DATE = 3;
	static $SCHEDULED_ANNUALLY = 4;
	static $SCHEDULED_ON_SPECIFIC_DATE = 5;

	public static function getInstance(){
		return new self();
	}

	/**
	 * Function returns the Scheduled Reports Model instance
	 * @param <Number> $recordId
	 * @return <Reports_ScehduleReports_Model>
	 */
	public static function getInstanceById($recordId) {
		$db = PearDatabase::getInstance();
		$scheduledReportModel = new self();

		if (!empty($recordId)) {
			$scheduledReportResult = $db->pquery('SELECT * FROM vtiger_schedulereports WHERE reportid = ?', array($recordId));
			if ($db->num_rows($scheduledReportResult) > 0) {
				$reportScheduleInfo = $db->query_result_rowdata($scheduledReportResult, 0);
				$reportScheduleInfo['schdate'] = decode_html($reportScheduleInfo['schdate']);
				$reportScheduleInfo['schdayoftheweek'] = decode_html($reportScheduleInfo['schdayoftheweek']);
				$reportScheduleInfo['schdayofthemonth'] = decode_html($reportScheduleInfo['schdayofthemonth']);
				$reportScheduleInfo['schannualdates'] = decode_html($reportScheduleInfo['schannualdates']);
				$reportScheduleInfo['recipients'] = decode_html($reportScheduleInfo['recipients']);
				$reportScheduleInfo['specificemails'] = decode_html($reportScheduleInfo['specificemails']);
				$scheduledReportModel->setData($reportScheduleInfo);
			}
		}
		return $scheduledReportModel;
	}

	/**
	 * Function to save the  Scheduled Reports data
	 */
	public function saveScheduleReport() {
		$adb = PearDatabase::getInstance();

		$reportid = $this->get('reportid');
		$scheduleid = $this->get('scheduleid');
		$schtime = $this->get('schtime');
		if(!preg_match('/^[0-2]\d(:[0-5]\d){1,2}$/', $schtime) or substr($schtime,0,2)>23) {  // invalid time format
			$schtime='00:00';
		}
		$schtime .=':00';

		$schdate = null; $schdayoftheweek = null; $schdayofthemonth = null; $schannualdates = null;
		if ($scheduleid == self::$SCHEDULED_ON_SPECIFIC_DATE) {
			$date = $this->get('schdate');
			$dateDBFormat = DateTimeField::convertToDBFormat($date);
			$nextTriggerTime = $dateDBFormat.' '.$schtime;
			$currentTime = date('Y-m-d H:i:s');
			$user = Users::getActiveAdminUser();
			$dateTime = new DateTimeField($nextTriggerTime);
			$nextTriggerTime = $dateTime->getDBInsertDateTimeValue($user);
			if($nextTriggerTime > $currentTime) {
				$this->set('next_trigger_time', $nextTriggerTime);
			} else {
				$this->set('next_trigger_time', date('Y-m-d H:i:s', strtotime('+10 year')));
			}
			$schdate = Zend_Json::encode(array($dateDBFormat));
		} else if ($scheduleid == self::$SCHEDULED_WEEKLY) {
			$schdayoftheweek = Zend_Json::encode($this->get('schdayoftheweek'));
			$this->set('schdayoftheweek', $schdayoftheweek);
		} else if ($scheduleid == self::$SCHEDULED_MONTHLY_BY_DATE) {
			$schdayofthemonth = Zend_Json::encode($this->get('schdayofthemonth'));
			$this->set('schdayofthemonth', $schdayofthemonth);
		} else if ($scheduleid == self::$SCHEDULED_ANNUALLY) {
			$schannualdates = Zend_Json::encode($this->get('schannualdates'));
			$this->set('schannualdates', $schannualdates);
		}

		$recipients = Zend_Json::encode($this->get('recipients'));
		$specificemails = Zend_Json::encode($this->get('specificemails'));
		$isReportScheduled = $this->get('isReportScheduled');
		$fileFormat = $this->get('fileformat');

		if($scheduleid != self::$SCHEDULED_ON_SPECIFIC_DATE) {
			$nextTriggerTime = $this->getNextTriggerTime();
		}
		if ($isReportScheduled == '0' || $isReportScheduled == '' || $isReportScheduled == false) {
			$deleteScheduledReportSql = "DELETE FROM vtiger_schedulereports WHERE reportid=?";
			$adb->pquery($deleteScheduledReportSql, array($reportid));
		} else {
			$checkScheduledResult = $adb->pquery('SELECT next_trigger_time FROM vtiger_schedulereports WHERE reportid=?', array($reportid));
			if ($adb->num_rows($checkScheduledResult) > 0) {
				$scheduledReportSql = 'UPDATE vtiger_schedulereports SET scheduleid=?, recipients=?, schdate=?, schtime=?, schdayoftheweek=?, schdayofthemonth=?, schannualdates=?, specificemails=?, next_trigger_time=?, fileformat = ? WHERE reportid=?';
				$adb->pquery($scheduledReportSql, array($scheduleid, $recipients, $schdate, $schtime, $schdayoftheweek, $schdayofthemonth, $schannualdates, $specificemails, $nextTriggerTime, $fileFormat, $reportid));
			} else {
				$scheduleReportSql = 'INSERT INTO vtiger_schedulereports (reportid,scheduleid,recipients,schdate,schtime,schdayoftheweek,schdayofthemonth,schannualdates,next_trigger_time,specificemails, fileformat) VALUES (?,?,?,?,?,?,?,?,?,?,?)';
				$adb->pquery($scheduleReportSql, array($reportid, $scheduleid, $recipients, $schdate, $schtime, $schdayoftheweek, $schdayofthemonth, $schannualdates, $nextTriggerTime,$specificemails,$fileFormat));
			}
		}
	}

	public function getRecipientEmails() {
		$recipientsInfo = $this->get('recipients');

		if (!empty($recipientsInfo)) {
			$recipients = array();
			$recipientsInfo = Zend_Json::decode($recipientsInfo);
			foreach ($recipientsInfo as $key => $recipient) {
				if (strpos($recipient,'USER') !== false) {
					$id = explode('::', $recipient);
					$recipients['Users'][] = $id[1];
				}else if (strpos($recipient,'GROUP') !== false) {
					$id = explode('::', $recipient);
					$recipients['Groups'][] = $id[1];
				}else if (strpos($recipient,'ROLE') !== false) {
					$id = explode('::', $recipient);
					$recipients['Roles'][] = $id[1];
				}
			}
		}
		$recipientsList = array();
		if (!empty($recipients)) {
			if (!empty($recipients['Users'])) {
				$recipientsList = array_merge($recipientsList, $recipients['Users']);
			}

			if (!empty($recipients['Roles'])) {
				foreach ($recipients['Roles'] as $roleId) {
					$roleUsers = getRoleUsers($roleId);
					foreach ($roleUsers as $userId => $userName) {
						array_push($recipientsList, $userId);
					}
				}
			}

			if (!empty($recipients['Groups'])) {
				require_once 'include/utils/GetGroupUsers.php';
				foreach ($recipients['Groups'] as $groupId) {
					$userGroups = new GetGroupUsers();
					$userGroups->getAllUsersInGroup($groupId);

					//Clearing static cache for sub groups
					GetGroupUsers::$groupIdsList = array();
					$recipientsList = array_merge($recipientsList, $userGroups->group_users);
				}
			}
		}
		$recipientsList = array_unique($recipientsList);
		$recipientsEmails = array();
		if (!empty($recipientsList) && count($recipientsList) > 0) {
			foreach ($recipientsList as $userId) {
				if(!Vtiger_Util_Helper::isUserDeleted($userId)) {
					$userName = getUserFullName($userId);
					$userEmail = getUserEmail($userId);
					if (!in_array($userEmail, $recipientsEmails)) {
						$recipientsEmails[$userName] = $userEmail;
					}
				}
			}
		}
		//Added for specific email address.
		$specificemails = explode(',', Zend_Json::decode($this->get('specificemails')));
		if (!empty($specificemails)) {
			$recipientsEmails = array_merge($recipientsEmails, $specificemails);
		}

		return $recipientsEmails;
	}

	public function sendEmail() {
		require_once 'vtlib/Vtiger/Mailer.php';
		$currentUserModel = Users_Record_Model::getCurrentUserModel();

		$vtigerMailer = new Vtiger_Mailer();

		$recipientEmails = $this->getRecipientEmails();
		Vtiger_Utils::ModuleLog('ScheduleReprots', $recipientEmails);
		foreach ($recipientEmails as $name => $email) {
			$vtigerMailer->AddAddress($email, decode_html($name));
		}
		vimport('~modules/Report/models/Record.php');
		$reportRecordModel = Reports_Record_Model::getInstanceById($this->get('reportid'));
		$currentTime = date('Y-m-d H:i:s');
		Vtiger_Utils::ModuleLog('ScheduleReprots Send Mail Start ::', $currentTime);
		$reportname = decode_html($reportRecordModel->getName());
		$subject = $reportname;
		Vtiger_Utils::ModuleLog('ScheduleReprot Name ::', $reportname);
		if(empty($vtigerMailer->From)) {
			$fromName = trim($currentUserModel->get('first_name').' '.$currentUserModel->get('last_name'));
			$vtigerMailer->ConfigSenderInfo($currentUserModel->get('email1'), $fromName);
		}
		$vtigerMailer->Subject = $subject;
		$vtigerMailer->Body = $this->getEmailContent($reportRecordModel);
		$plainBody = decode_html($vtigerMailer->Body);
		$plainBody = preg_replace(array("/<p>/i","/<br>/i","/<br \/>/i"),array("\n","\n","\n"),$plainBody);
		$plainBody = strip_tags($plainBody);
		$plainBody = Emails_Mailer_Model::convertToAscii($plainBody);
		$vtigerMailer->AltBody = $plainBody;

		$vtigerMailer->IsHTML();

		$baseFileName = preg_replace("/[^\p{L}\p{N}\s]+/", "", $reportname);

		$oReportRun = ReportRun::getInstance($this->get('reportid'));
		$reportFormat = $this->get('fileformat');
		$attachments = array();
		$reportType = $reportRecordModel->get('reporttype');

		if($reportType != 'chart') {
			if ($reportFormat == 'CSV') {
				$fileName = $baseFileName . '.csv';
				$filePath = 'storage/' . $fileName;
				$attachments[$fileName] = $filePath;
				$oReportRun->writeReportToCSVFile($filePath);
			} else if($reportFormat == 'XLS') {
				$fileName = $baseFileName . '.xls';
				$filePath = 'storage/' . $fileName;
				$attachments[$fileName] = $filePath;
				$oReportRun->writeReportToExcelFile($filePath);
			}

			foreach ($attachments as $attachmentName => $path) {
				$vtigerMailer->AddAttachment($path, decode_html($attachmentName));
			}
		}
		$status = $vtigerMailer->Send(true);

		if($reportType != 'chart') {
			foreach ($attachments as $attachmentName => $path) {
				unlink($path);
			}
		}
		return $status;
	}

	/**
	 * Function gets the next trigger for the workflows
	 * @global <String> $default_timezone
	 * @return type
	 */
	function getNextTriggerTime() {
		require_once 'modules/com_vtiger_workflow/VTWorkflowManager.inc';
		$default_timezone = vglobal('default_timezone');
		$admin = Users::getActiveAdminUser();
		$adminTimeZone = $admin->time_zone;
		@date_default_timezone_set($adminTimeZone);

		$scheduleType = $this->get('scheduleid');
		$nextTime = null;

		$workflow = new Workflow();
		if ($scheduleType == self::$SCHEDULED_DAILY) {
			$nextTime = $workflow->getNextTriggerTimeForDaily($this->get('schtime'));
		}
		if ($scheduleType == self::$SCHEDULED_WEEKLY) {
			$nextTime = $workflow->getNextTriggerTimeForWeekly($this->get('schdayoftheweek'), $this->get('schtime'));
		}

		if ($scheduleType == self::$SCHEDULED_MONTHLY_BY_DATE) {
			$nextTime = $workflow->getNextTriggerTimeForMonthlyByDate($this->get('schdayofthemonth'), $this->get('schtime'));
		}

		if ($scheduleType == self::$SCHEDULED_ANNUALLY) {
			$nextTime = $workflow->getNextTriggerTimeForAnnualDates($this->get('schannualdates'), $this->get('schtime'));
		}
		@date_default_timezone_set($default_timezone);
		if($scheduleType != self::$SCHEDULED_ON_SPECIFIC_DATE) {
			$dateTime = new DateTimeField($nextTime);
			$nextTime = $dateTime->getDBInsertDateTimeValue($admin);
		}
		return $nextTime;
	}

	public function updateNextTriggerTime() {
		$adb = PearDatabase::getInstance();
		$nextTriggerTime = $this->getNextTriggerTime();
		Vtiger_Utils::ModuleLog('ScheduleReprot Next Trigger Time >> ', $nextTriggerTime);
		$adb->pquery('UPDATE vtiger_schedulereports SET next_trigger_time=? WHERE reportid=?', array($nextTriggerTime, $this->get('reportid')));
		Vtiger_Utils::ModuleLog('ScheduleReprot', 'Next Trigger Time updated');
	}

	public static function getScheduledReports() {
		$adb = PearDatabase::getInstance();

		$currentTimestamp = date("Y-m-d H:i:s");
		$result = $adb->pquery("SELECT reportid FROM vtiger_schedulereports
								INNER JOIN vtiger_reportmodules ON vtiger_reportmodules.reportmodulesid = vtiger_schedulereports.reportid
								INNER JOIN vtiger_tab ON vtiger_tab.name = vtiger_reportmodules.primarymodule AND presence = 0
								WHERE next_trigger_time <= ? AND next_trigger_time IS NOT NULL", array($currentTimestamp));

		$scheduledReports = array();
		$noOfScheduledReports = $adb->num_rows($result);
		for ($i = 0; $i < $noOfScheduledReports; ++$i) {
			$recordId = $adb->query_result($result, $i, 'reportid');
			$scheduledReports[$recordId] = self::getInstanceById($recordId);
		}
		return $scheduledReports;
	}

	public static function runScheduledReports() {
		vimport('~~modules/com_vtiger_workflow/VTWorkflowUtils.php');
		$util = new VTWorkflowUtils();
		$util->adminUser();

		global $currentModule, $current_language;
		if(empty($currentModule)) $currentModule = 'Reports';
		if(empty($current_language)) $current_language = 'en_us';

		$scheduledReports = self::getScheduledReports();
		foreach ($scheduledReports as $reportId => $scheduledReport) {
			$reportRecordModel = Reports_Record_Model::getInstanceById($reportId);
			$reportType = $reportRecordModel->get('reporttype');
			if($reportType == 'chart') {
				$status = $scheduledReport->sendEmail();
			} else {
				$query = $reportRecordModel->getReportSQL();
				$countQuery = $reportRecordModel->generateCountQuery($query);
				if($reportRecordModel->getReportsCount($countQuery) > 0){
					$status = $scheduledReport->sendEmail();
				}
			}
			Vtiger_Utils::ModuleLog('ScheduleReprot Send Mail Status ', $status);
			$scheduledReport->updateNextTriggerTime();
		}
		$util->revertUser();
		return $status;
	}

	function getEmailContent($reportRecordModel){
		$site_URL = vglobal('site_URL');
		$currentModule = vglobal('currentModule');
		$companydetails = getCompanyDetails();
		$logo = $site_URL.'/test/logo/'.$companydetails['logoname'];

		$body = '<table width="700" cellspacing="0" cellpadding="0" border="0" align="center" style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; font-weight: normal; text-decoration: none; ">
			<tr>
				<td> </td>
			</tr>
			<tr>
				<td>
				<table width="100%" cellspacing="0" cellpadding="0" border="0">
						<tr>
							<td>
							<table width="100%" cellspacing="0" cellpadding="0" border="0">
									<tr>
										<td rowspan="4" ><img height="30" src='.$logo.'></td>
									</tr>
							</table>
							</td>
						</tr>
						<tr>
							<td>
							<table width="100%" cellspacing="0" cellpadding="0" border="0" style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; font-weight: normal; color: rgb(0, 0, 0); background-color: rgb(255, 255, 255);">
									<tr>
										<td valign="top">
										<table width="100%" cellspacing="0" cellpadding="5" border="0">
												<tr>
													<td align="right" style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; font-weight: bolder; text-decoration: none; color: rgb(66, 66, 253);"> </td>
												</tr>
												<tr>
													<td> </td>
												</tr>
												<tr>
													<td style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(0, 0, 0); font-weight: normal; text-align: justify; line-height: 20px;"> '.  vtranslate('LBL_AUTO_GENERATED_REPORT_EMAIL', $currentModule).'</td>
												</tr>
												<tr>
													<td align="center">
													<table width="75%" cellspacing="0" cellpadding="10" border="0" style="border: 2px solid rgb(180, 180, 179); background-color: rgb(226, 226, 225); font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(0, 0, 0); font-weight: normal;">
															<tr>
																<td><b>' . vtranslate('LBL_REPORT_NAME', $currentModule) . ' </b> : <font color="#990000"><strong> <a href=' .$site_URL.'/'. $reportRecordModel->getDetailViewUrl() . '>' . $reportRecordModel->getName() . '</a></strong></font> </td>
															</tr>
															<tr>
																<td><b>' . vtranslate('LBL_DESCRIPTION', $currentModule) . ' :</b> <font color="#990000"><strong>' . $reportRecordModel->get('description') . '</strong></font> </td>
															</tr>
													</table>
													</td>
												</tr>
										</table>
										</td>
										<td width="1%" valign="top"> </td>
									</tr>
							</table>
							</td>
						</tr>
				</table>
				</td>
			</tr>
			<tr>
				<td> </td>
			</tr>
			<tr>
				<td> </td>
			</tr>
			<tr>
				<td> </td>
			</tr>
	</table>';

	return $body;
	}

	public function getNextTriggerTimeInUserFormat() {
		$dateTime = new DateTimeField($this->get('next_trigger_time'));
		$nextTriggerTime = $dateTime->getDisplayDateTimeValue();
		$valueParts = explode(' ', $nextTriggerTime);
		$value = $valueParts[0].' '.Vtiger_Time_UIType::getDisplayValue($valueParts[1]);
		return $value;
	}

}

