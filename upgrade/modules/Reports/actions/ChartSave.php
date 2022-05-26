<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Reports_ChartSave_Action extends Reports_Save_Action {

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();

		$record = $request->get('record');
		$reportModel = new Reports_Record_Model();
		$reportModel->setModule('Reports');
		if(!empty($record) && !$request->get('isDuplicate')) {
			$reportModel->setId($record);
		}

		$reportModel->set('reportname', $request->get('reportname'));
		$reportModel->set('folderid', $request->get('folderid'));
		$reportModel->set('description', $request->get('reports_description'));
		$reportModel->set('members', $request->get('members'));

		$reportModel->setPrimaryModule($request->get('primary_module'));

		$secondaryModules = $request->get('secondary_modules');
		$secondaryModules = implode(':', $secondaryModules);
		$reportModel->setSecondaryModule($secondaryModules);

		$reportModel->set('advancedFilter', $request->get('advanced_filter'));
		$reportModel->set('reporttype', 'chart');


		$dataFields = $request->get('datafields', 'count(*)');
		if(is_string($dataFields)) $dataFields = array($dataFields);

		$reportModel->set('reporttypedata', Zend_Json::encode(array(
																'type'=>$request->get('charttype', 'pieChart'),
																'groupbyfield'=>$request->get('groupbyfield'),
																'datafields'=>$dataFields)
															));
		$reportModel->save();

		$scheduleReportModel = new Reports_ScheduleReports_Model();
		$scheduleReportModel->set('scheduleid', $request->get('schtypeid'));
		$scheduleReportModel->set('schtime', date('H:i', strtotime($request->get('schtime'))));
		$scheduleReportModel->set('schdate', $request->get('schdate'));
		$scheduleReportModel->set('schdayoftheweek', $request->get('schdayoftheweek'));
		$scheduleReportModel->set('schdayofthemonth', $request->get('schdayofthemonth'));
		$scheduleReportModel->set('schannualdates', $request->get('schannualdates'));
		$scheduleReportModel->set('reportid', $reportModel->getId());
		$scheduleReportModel->set('recipients', $request->get('recipients'));
		$scheduleReportModel->set('isReportScheduled', $request->get('enable_schedule'));
		$scheduleReportModel->set('specificemails', $request->get('specificemails'));
		$scheduleReportModel->saveScheduleReport();
		$loadUrl = $reportModel->getDetailViewUrl();
		header("Location: $loadUrl");
	}
}
