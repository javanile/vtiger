<?php
/*+*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ******************************************************************************/
interface VTExpressionEnv{
	function get($var);
}

function __vt_add($arr){
	if(sizeof($arr)==1){
		return $arr[0];
	}else{
		if(strlen(substr(strrchr($arr[0], "."), 1))>strlen(substr(strrchr($arr[1], "."), 1))){
			$maxDigit = strlen(substr(strrchr($arr[0], "."), 1));
		} else {
			$maxDigit = strlen(substr(strrchr($arr[1], "."), 1));
		}
		return bcadd($arr[0], $arr[1] , $maxDigit);
	}
}

function __vt_sub($arr){
	if(sizeof($arr)==1){
		return -$arr[0];
	}else{
		if(strlen(substr(strrchr($arr[0], "."), 1))>strlen(substr(strrchr($arr[1], "."), 1))){
			$maxDigit = strlen(substr(strrchr($arr[0], "."), 1));
		} else {
			$maxDigit = strlen(substr(strrchr($arr[1], "."), 1));
		}
		return bcsub($arr[0], $arr[1], $maxDigit);
	}
}

function __vt_mul($arr){
	return $arr[0]*$arr[1];
}

function __vt_div($arr){
	if($arr[1] == 0 || empty($arr[1]))
		return 0;
	return $arr[0]/$arr[1];
}

function __vt_equals($arr){
	return $arr[0] == $arr[1];
}

function __vt_ltequals($arr) {
	return $arr[0] <= $arr[1];
}

function __vt_gtequals($arr) {
	return $arr[0] >= $arr[1];
}

function __vt_lt($arr) {
	return $arr[0] < $arr[1];
}

function __vt_gt($arr) {
	return $arr[0] > $arr[1];
}

function __vt_concat($arr){
	return implode($arr);
}
/* Date difference between (input times) or (current time and input time)
 *
 * @param Array $a $a[0] - Input time1, $a[1] - Input time2
 * (if $a[1] is not available $a[0] = Current Time, $a[1] = Input time1)
 * @return int difference timestamp
 */

function __vt_time_diff($arr) {
	$storedTimeZone = date_default_timezone_get();
	$defaultTimezone = vglobal('default_timezone');
	date_default_timezone_set($defaultTimezone);
	$time_operand1 = $time_operand2 = 0;
	if(count($arr) > 1) {
			$time_operand1 = $time1 = $arr[0];
			$time_operand2 = $time2 = $arr[1];

			$trimmedOperand1 = trim($time_operand1);
			$trimmedOperand2 = trim($time_operand2);
			list($date1,$time1) = explode(' ', $trimmedOperand1);
			list($date2,$time2) = explode(' ', $trimmedOperand2);
			if(strpos($date1,":")){
				$time1 = $date1;
				$date1 = '';
			}
			if(strpos($date2,":")){
			   $time2 = $date2;
			   $date2='';
			}
			if(empty($time1)) {
				$time_operand1 = $time_operand1." 00:00:00";
			} else {
				$time_operand1 = $userStartDateTime = DateTimeField::convertToUserTimeZone($time_operand1); // convert to user time 
				$time_operand1 = $time_operand1->format('Y-m-d H:i:s');
			}
			if(empty($time2)) {
				$time_operand2 = $time_operand2." 00:00:00";
			} else {
				$time_operand2 = $userStartDateTime = DateTimeField::convertToUserTimeZone($time_operand2); // convert to user time 
				$time_operand2 = $time_operand2->format('Y-m-d H:i:s');
			}
			} else {

		// Added as we need to compare with the values based on the user date format and timezone

		$time_operand1 = date('Y-m-d H:i:s'); // Current time
		$time_operand1 = $userStartDateTime = DateTimeField::convertToUserTimeZone($time_operand1); // convert to user time 
		$time_operand1 = $time_operand1->format('Y-m-d H:i:s');

		$time_operand2 = $arr[0];
		$trimmedOperand = trim($time_operand2);
		list($date,$time) = explode(' ', $trimmedOperand);
		if(empty($time)) {
			$time_operand2 = $time_operand2." 00:00:00";
		} else {
			$time_operand2 = $userStartDateTime = DateTimeField::convertToUserTimeZone($time_operand2); // convert to user time 
			$time_operand2 = $time_operand2->format('Y-m-d H:i:s');
		}
	}

	if(empty($time_operand1) || empty($time_operand2)) return 0;

	$time_operand1 = getValidDBInsertDateTimeValue($time_operand1);
	$time_operand2 = getValidDBInsertDateTimeValue($time_operand2);

	//to give the difference if it is only time field
	if(empty($time_operand1) && empty($time_operand2)) {
		$pattern = "/([01]?[0-9]|2[0-3]):[0-5][0-9]/";
		if(preg_match($pattern, $time1) && preg_match($pattern, $time2)){
			$timeDiff = strtotime($time1) - strtotime($time2);
			return date('H:i:s', $timeDiff);
		}
	}
	date_default_timezone_set($storedTimeZone);
	return (strtotime($time_operand1) - strtotime($time_operand2));
}
/**
 * Calculate the time difference (input times) or (current time and input time) and
 * convert it into number of days.
 * @param Array $a $a[0] - Input time1, $a[1] - Input time2
 * (if $a[1] is not available $a[0] = Current Time, $a[1] = Input time1)
 * @return int number of days
 */
function __vt_time_diffdays($arr) {
	//Two Input timediffdays
	if(count($arr)>1){
		$operand1 = explode(' ',trim($arr[0]));
		$operand2 = explode(' ',trim($arr[1]));
		//If both inputs are datetime 
		if($operand1[1] && $operand2[1]){
			$timediff  = __vt_time_diff($arr);
		}else{
			//If one of input is date then ignore time part
			$storedTimeZone = date_default_timezone_get();
			date_default_timezone_set(vglobal('default_timezone'));
			$arr[0]=$operand1[1]?DateTimeField::convertToUserTimeZone($arr[0])->format('Y-m-d'):$operand1[0];  
			$arr[1]=$operand2[1]?DateTimeField::convertToUserTimeZone($arr[1])->format('Y-m-d'):$operand2[0]; 
			date_default_timezone_set($storedTimeZone);
			$timediff  = __vt_time_diff($arr);
		}
	//One input timediffdays
	}else{
		$operand1 = explode(' ',trim($arr[0]));
		$operand2=$arr[0];
		if($operand1[1]){
			$arr[0]=date('Y-m-d H:i:s'); // Current DateTime- user timezone
			$arr[1]=$operand2;
			$timediff  = __vt_time_diff($arr);
		}else{
			$arr[0]=date('Y-m-d'); // Current date - user timezone
			$arr[1]=$operand1[0];
			$timediff  = __vt_time_diff($arr);
		}
	}
	$days_diff = round($timediff / (60 * 60 * 24));
	return $days_diff;
}

function __vt_add_days($arr) {

	if (count($arr) > 1) {
		$baseDate = $arr[0];
		$noOfDays = $arr[1];
	} else {
		$noOfDays = $arr[0];
	}
	if($baseDate==null || empty($baseDate)) {
		$baseDate = date('Y-m-d'); // Current date
	}
	preg_match('/\d\d\d\d-\d\d-\d\d/', $baseDate, $match);
	$baseDate = strtotime($match[0]);
	$date = strftime('%Y-%m-%d', $baseDate + ($noOfDays * 24 * 60 * 60));
	return $date;
}

function __vt_sub_days($arr) {

	if (count($arr) > 1) {
		$baseDate = $arr[0];
		$noOfDays = $arr[1];
	} else {
		$noOfDays = $arr[0];
	}
	if($baseDate==null || empty($baseDate)) {
		$baseDate = date('Y-m-d'); // Current date
	}
	preg_match('/\d\d\d\d-\d\d-\d\d/', $baseDate, $match);
	$baseDate = strtotime($match[0]);
	$date = strftime('%Y-%m-%d', $baseDate - ($noOfDays * 24 * 60 * 60));
	return $date;
}

function __vt_get_date($arr) {
	$type = $arr[0];
	switch ($type) {
		case 'today': return date('Y-m-d');
			break;
		case 'tomorrow': return date('Y-m-d', strtotime('+1 day'));
			break;
		case 'yesterday': return date('Y-m-d', strtotime('-1 day'));
			break;
		default : return date('Y-m-d');
			break;
	}
}

function __vt_add_time($arr) {
	if(count($arr) > 1) {
		$baseTime = $arr[0];
		$minutes = $arr[1];
	} else {
		$baseTime = date('H:i:s');
		$minutes = $arr[0];
	}
	$endTime = strtotime("+$minutes minutes", strtotime($baseTime));
	return date('H:i:s',$endTime);
}

function __vt_sub_time($arr) {
	if(count($arr) > 1) {
		$baseTime = $arr[0];
		$minutes = $arr[1];
	} else {
		$baseTime = date('H:i:s');
		$minutes = $arr[0];
	}
	$endTime = strtotime("-$minutes minutes", strtotime($baseTime));
	return date('H:i:s',$endTime);
}

function __vt_power($elements) {
	if(!empty($elements[0])) {
		$exponent = ($elements[1]) ? $elements[1] : 0;
		return pow($elements[0], $exponent);
	}
	return 0;
}

/** END * */
class VTFieldExpressionEvaluater{
	function __construct($expr){

		$this->operators = array(
				'+'  => '__vt_add',
				'-'  => '__vt_sub',
				'*'  => '__vt_mul',
				'/'  => '__vt_div',
				'==' => '__vt_equals',
				'<=' => '__vt_ltequals',
				'>=' => '__vt_gtequals',
				'<' => '__vt_lt',
				'>' => '__vt_gt',
		);
		$this->functions = array(
				'concat'=>'__vt_concat',
				'time_diff' => '__vt_time_diff',
				'time_diffdays' => '__vt_time_diffdays',
				'add_days' => '__vt_add_days',
				'sub_days' => '__vt_sub_days',
				'get_date' => '__vt_get_date',
				'add_time' => '__vt_add_time',
				'sub_time' => '__vt_sub_time',
				'power'	   => '__vt_power'
		);

		$this->operations = array_merge($this->functions, $this->operators);
		$this->expr = $expr;

	}

	function evaluate($env){
		$this->env = $env;
		return $this->exec($this->expr);
	}

	function exec($expr){
		if($expr instanceof VTExpressionSymbol){
			return $this->env($expr);
		}else if($expr instanceof VTExpressionTreeNode){
			$op = $expr->getName();
			if($op->value=='if'){
				$params = $expr->getParams();
				$cond = $this->exec($params[0]);
				if($cond){
					return $this->exec($params[1]);
				}else{
					return $this->exec($params[2]);
				}
			}else{
				$params = array_map(array($this, 'exec'), $expr->getParams());
				$func = $this->operations[$op->value];
				return $func($params);
			}
		}else{
			return $expr;
		}
	}

	function env($sym){
		if($this->env) {
			global $current_user;
			$fieldName = $sym->value;
			//if the field is reference fields field name 
			// format (account_id : (Accounts) phone)
			preg_match('/\((\w+) : \((\w+)\) (\w+)\)/',$fieldName,$matches);
			if(count($matches) > 0){
				//reference field update
				$referenceField = $matches[1];
				$referencedModule = $matches[2];
				$referencedFieldName = $matches[3];
				$referenceRecordId = $this->env->get($referenceField);
				if(empty($referenceRecordId)) return '';
				else{
					global $current_user;
					$referenceRecordEntity = VTEntityCache::getCachedEntity($referenceRecordId);
					if(empty($referenceRecordEntity)) {
					$referenceEntity = new VTEntityCache($current_user);
					$referenceRecordEntity = $referenceEntity->forId($referenceRecordId);
						VTEntityCache::setCachedEntity($referenceRecordId, $referenceRecordEntity);
					}
					$referenceModuleHandler = vtws_getModuleHandlerFromName($referencedModule, $current_user);
					$referenceModuleMeta = $referenceModuleHandler->getMeta();
					$referenceModuleFields = $referenceModuleMeta->getModuleFields();
					$referenceFieldInstance = $referenceModuleFields[$referencedFieldName];
					if(!empty($referenceFieldInstance) && ($referenceFieldInstance->getFieldDataType() == "reference")){
						$referenceWsRecordId = $referenceRecordEntity->get($referencedFieldName);
						$referenceRecordIdComponents = vtws_getIdComponents($referenceWsRecordId);
						$referenceRecordId = $referenceRecordIdComponents[1];
						if(!empty($referenceRecordId)) {
							$referenceList = $referenceFieldInstance->getReferenceList();
							if((count($referenceList) == 1) && $referenceList[0] == "Users") {
								$userRecordLabels = Vtiger_Functions::getOwnerRecordLabels($referenceRecordId);
								return $userRecordLabels[$referenceRecordId];
							}
							return Vtiger_Util_Helper::getRecordName($referenceRecordId);
						}
						return $referenceRecordId;
					}

					if ($referenceFieldInstance && $referenceFieldInstance->getUIType() == '72') {
						$currencyConversionRate = $referenceRecordEntity->get('conversion_rate');
						if (!empty($currencyConversionRate)) {
							if (!empty($this->fieldInstance) && $this->fieldInstance->getFieldDataType() == "currency" && $this->fieldInstance->getUIType() != 72) {
								$rawBaseCurrencyValue = CurrencyField::convertToDollar($referenceRecordEntity->get($referencedFieldName), $currencyConversionRate);
								return $rawBaseCurrencyValue;
							}
						}
					}

					return $referenceRecordEntity->get($referencedFieldName);
				}
			}

			$moduleName = $this->env->getModuleName();
			$moduleHandler = vtws_getModuleHandlerFromName($moduleName, $current_user);
			$handlerMeta = $moduleHandler->getMeta();
			$moduleFields = $handlerMeta->getModuleFields();
			$fieldInstance = $moduleFields[$sym->value];
			if(!empty($fieldInstance)&& ($fieldInstance->getFieldDataType() == "reference")){
				$referenceWsRecordId = $this->env->get($sym->value);
				$referenceRecordIdComponents = vtws_getIdComponents($referenceWsRecordId);
				$referenceRecordId = $referenceRecordIdComponents[1];
				if(!empty($referenceRecordId)) {
					$referenceList = $fieldInstance->getReferenceList();
					if((count($referenceList) == 1) && $referenceList[0] == "Users") {
						$userRecordLabels = Vtiger_Functions::getOwnerRecordLabels($referenceRecordId);
						return $userRecordLabels[$referenceRecordId];
					}
					return Vtiger_Util_Helper::getRecordName($referenceRecordId);
				}
				return $referenceRecordId;
			}

			if (!empty($fieldInstance) && $fieldInstance->getUIType() == '72') {
				$currencyConversionRate = $this->env->get('conversion_rate');
				if (!empty($currencyConversionRate)) {
					if (!empty($this->fieldInstance) && $this->fieldInstance->getFieldDataType() == "currency" && $this->fieldInstance->getUIType() != 72) {
						$rawBaseCurrencyValue = CurrencyField::convertToDollar($this->env->get($sym->value), $currencyConversionRate);
						return $rawBaseCurrencyValue;
					}
				}
			}
			return $this->env->get($sym->value);
		} else {
			return $sym->value;
		}
	}
}
?>