<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_MiniList_Dashboard extends Vtiger_IndexAjax_View {

	public function process(Vtiger_Request $request, $widget=NULL) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
        $currentPage = $request->get('currentPage');
        if(empty($currentPage)) {
            $currentPage = 1;
            $nextPage = 1;
        } else {
            $nextPage = $currentPage + 1;
        }

		// Initialize Widget to the right-state of information
		if ($widget && !$request->has('widgetid')) {
			$widgetId = $widget->get('id');
		} else {
			$widgetId = $request->get('widgetid');
		}

		$widget = Vtiger_Widget_Model::getInstanceWithWidgetId($widgetId, $currentUser->getId());

		$minilistWidgetModel = new Vtiger_MiniList_Model();
		$minilistWidgetModel->setWidgetModel($widget);
        $minilistWidgetModel->set('nextPage', $nextPage);
        $minilistWidgetModel->set('currentPage', $currentPage);

		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('MINILIST_WIDGET_MODEL', $minilistWidgetModel);
		$viewer->assign('BASE_MODULE', $minilistWidgetModel->getTargetModule());
        $viewer->assign('CURRENT_PAGE', $currentPage);
        $viewer->assign('MORE_EXISTS', $minilistWidgetModel->moreRecordExists());
        $viewer->assign('SCRIPTS', $this->getHeaderScripts());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

		$content = $request->get('content');
		if(!empty($content)) {
			$viewer->view('dashboards/MiniListContents.tpl', $moduleName);
		} else {
			$widget->set('title', $minilistWidgetModel->getTitle());

			$viewer->view('dashboards/MiniList.tpl', $moduleName);
		}

	}
    
    function getHeaderScripts() {
        return $this->checkAndConvertJsScripts(array('modules.Emails.resources.MassEdit'));
	}
}
