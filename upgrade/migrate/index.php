<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/

chdir (dirname(__FILE__) . '/..');
include_once 'vtigerversion.php';
include_once 'data/CRMEntity.php';
include_once 'includes/main/WebUI.php';

$errorMessage = $_REQUEST['error'];
if (!$errorMessage) {
	/* 7.x module compatability check when coming from earlier version */
	if (version_compare($vtiger_current_version, '7.0.0') < 0) {
		/* NOTE: Add list-of modules that you own / sure to upgrade later */
		$skipCheckForModules = array();

		$extensionStoreInstance = Settings_ExtensionStore_Extension_Model::getInstance();
		$vtigerStandardModules = array('Accounts', 'Assets', 'Calendar', 'Campaigns', 'Contacts', 'CustomerPortal', 
			'Dashboard', 'Emails', 'EmailTemplates', 'Events', 'ExtensionStore',
			'Faq', 'Google', 'HelpDesk', 'Home', 'Import', 'Invoice', 'Leads', 
			'MailManager', 'Mobile', 'ModComments', 'ModTracker',
			'PBXManager', 'Portal', 'Potentials', 'PriceBooks', 'Products', 'Project', 'ProjectMilestone', 
			'ProjectTask', 'PurchaseOrder', 'Quotes', 'RecycleBin', 'Reports', 'Rss', 'SalesOrder', 
			'ServiceContracts', 'Services', 'SMSNotifier', 'Users', 'Vendors',
			'Webforms', 'Webmails', 'WSAPP');

		$skipCheckForModules = array_merge($skipCheckForModules, $vtigerStandardModules);

		$nonPortedExtns = array();
		$moduleModelsList = array();
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT name FROM vtiger_tab WHERE isentitytype != ? AND presence != ? AND trim(name) NOT IN ('.generateQuestionMarks($skipCheckForModules).')', array(1, 1, $skipCheckForModules));
		if ($db->num_rows($result)) {
			$moduleModelsList = $extensionStoreInstance->getListings();
		}

		$moduleModelsListByName = array();
		$moduleModelsListByLabel = array();
		foreach ($moduleModelsList as $moduleId => $moduleModel) {
			if ($moduleModel->get('name') != $moduleModel->get('label')) {
				$moduleModelsListByName[$moduleModel->get('name')] = $moduleModel;
			} else {
				$moduleModelsListByLabel[$moduleModel->get('label')] = $moduleModel;
			}
		}

		if ($moduleModelsList) {
			while($row = $db->fetch_row($result)) {
				$moduleName = $row['name'];//label
				if ($moduleName) {
					unset($moduleModel);
					if (array_key_exists($moduleName, $moduleModelsListByName)) {
						$moduleModel = $moduleModelsListByName[$moduleName];
					} else if (array_key_exists($moduleName, $moduleModelsListByLabel)) {
						$moduleModel = $moduleModelsListByLabel[$moduleName];
					}

					if ($moduleModel) {
						$vtigerVersion = $moduleModel->get('vtigerVersion');
						$vtigerMaxVersion = $moduleModel->get('vtigerMaxVersion');
						if (($vtigerVersion && strpos($vtigerVersion, '7.') === false)
								&& ($vtigerMaxVersion && strpos($vtigerMaxVersion, '7.') === false)) {
							$nonPortedExtns[] = $moduleName;
						}
					}
				}
			}

			if ($nonPortedExtns) {
				$portingMessage = 'Following custom modules are not compatible with Vtiger 7. Please disable these modules to proceed.';
				foreach ($nonPortedExtns as $moduleName) {
					$portingMessage .= "<li>$moduleName</li>";
				}
				$portingMessage .= '</ul>';
			}
		}
	}
}
?>
<!doctype>
<html>
	<head>
		<title>Vtiger CRM Setup</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<script type="text/javascript" src="resources/js/jquery-min.js"></script>
		<link href="resources/todc/css/bootstrap.min.css" rel="stylesheet">
		<link href="resources/todc/css/todc-bootstrap.min.css" rel="stylesheet">
		<link href="resources/css/mkCheckbox.css" rel="stylesheet">
		<link href="resources/css/style.css" rel="stylesheet">
	</head>
	<body style="font-size: 14px !important;">
		<div class="container-fluid page-container">
			<div class="row">
				<div class="col-lg-6">
					<div class="logo">
						<img src="resources/images/vt1.png" alt="Vtiger Logo"/>
					</div>
				</div>
				<div class="col-lg-6">
					<div class="head pull-right">
						<h3>Migration Wizard</h3>
					</div>
				</div>
			</div>
			<div class="row main-container">
				<div class="col-lg-12 inner-container">
					<div class="row">
						<div class="col-lg-10">
							<h4 class="">Welcome</h4>
						</div>
						<div class="col-lg-2">
							<a href="https://wiki.vtiger.com/vtiger7/" target="_blank" class="pull-right">
								<img src="resources/images/help40.png" alt="Help-Icon"/>
							</a>
						</div>
					</div>
					<hr>
					<div class="row">
						<div class="col-lg-4 welcome-image">
							<img src="resources/images/migration_screen.png" alt="Vtiger Logo" style="width: 100%; margin-left: 15px;"/>
						</div>
						<?php
							$currentVersion = explode('.', $vtiger_current_version);
							 if ($portingMessage) { ?>
								<div class="col-lg-1"></div>
								<div class="col-lg-7">
									<h3><font color="red">WARNING : Cannot continue with Migration</font></h3><br>
									<p><?php echo $portingMessage;?></p>
								</div>
							</div>
							<div class="button-container col-lg-12">
								<div class="pull-right">
									<form action="../index.php?module=Migration&action=DisableModules&mode=fromMig" method="POST">
										<input type="hidden" name="modulesList" <?php echo 'value="'.Vtiger_Util_Helper::toSafeHTML(Zend_JSON::encode($nonPortedExtns)).'"'; ?> />
										<input type="submit" class="btn btn-warning" value="Disable modules & Proceed"/>
										<input type="button" onclick="window.location.href='../index.php'" class="btn btn-default" value="Close"/>
									</form>
								</div>
						<?php } else if($currentVersion[0] >= 6 && $currentVersion[1] >= 0) { ?>
							<div class="col-lg-8" style="padding-left: 30px;">
								<h3> Welcome to Vtiger Migration</h3>
								<?php if(isset($errorMessage)) {
									echo '<span><font color="red"><b>'.filter_var($errorMessage, FILTER_SANITIZE_STRING).'</b></font></span><br><br>';
								} ?>
								<p>We have detected that you have <strong>Vtiger <?php echo $vtiger_current_version ?></strong> installation.<br><br></p>
								<p>
									<strong>Warning: </strong>
									Please note that it is not possible to revert back to <?php echo $vtiger_current_version ?>&nbsp;after the upgrade to vtiger 7 <br>
									So, it is important to take a backup of the <?php echo $vtiger_current_version ?> installation, including the source files and database.
								</p><br>
								<form action="../index.php?module=Migration&action=Extract&mode=fromMig" method="POST">
									<div><input type="checkbox" id="checkBox1" name="checkBox1"/><div class="chkbox"></div> I have taken the backup of database <a href="http://community.vtiger.com/help/vtigercrm/administrators/backup.html" target="_blank" >(how to?)</a></div><br>
									<div><input type="checkbox" id="checkBox2" name="checkBox2"/><div class="chkbox"></div> I have taken the backup of source folder <a href="http://community.vtiger.com/help/vtigercrm/administrators/backup.html" target="_blank" >(how to?)</a></div><br>
									<br>
									<div>
										<span id="error"></span>
										User Name <span class="no">&nbsp;</span>
										<input type="text" value="" name="username" id="username" />&nbsp;&nbsp;
										Password <span class="no">&nbsp;</span>
										<input type="password" value="" name="password" id="password" />&nbsp;&nbsp;
									</div>
									<br><br><br>
									<div class="button-container">
										<input type="submit" class="btn btn-primary" id="startMigration" name="startMigration" value="Start Migration" />
									</div>
								</form>
							</div>
						<?php } else if($currentVersion[0] < 6) { ?>
							<div class="col-lg-1"></div>
							<div class="col-lg-7">
								<h3><font color="red">WARNING : Cannot continue with Migration</font></h3><br>
								<p>We detected that this installation is running <strong>Vtiger CRM</strong>
										<?php
											if($vtiger_current_version < 6 ) {
												echo '<b>'.$vtiger_current_version.'</b>';
											}
										?>.
									Please upgrade to <strong>5.4.0</strong> first before continuing with this wizard.
								</p>
							</div>
							<div class="button-container col-lg-12">
								<input type="button" onclick="window.location.href='index.php'" class="btn btn-primary pull-right" value="Finish"/>
						<?php } else { ?>
							<div class="col-lg-1"></div>
							<div class="col-lg-7">
								<h3><font color="red">WARNING : Cannot continue with Migration</font></h3>
								<br>
								<p>
									We detected that this source is upgraded latest version.
								</p>
							</div>
							<div class="button-container col-lg-12">
								<input type="button" onclick="window.location.href='index.php'" class="btn btn-primary pull-right" value="Finish"/>
						<?php } ?>
					</div>
				</div>
			</div>
			<script>
				$(document).ready(function(){
					$('input[name="startMigration"]').click(function(){
						if($("#checkBox1").is(':checked') == false || $("#checkBox2").is(':checked') == false){
							alert('Before starting migration, please take your database and source backup');
							return false;
						}
						if($('#username').val() == '' || $('#password').val() == ''){
							alert('Please enter Admin credentials to start Migration');
							return false;
						}
						return true;
					});
				});
			</script>
	</body>
</html>
