<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
include_once 'vtlib/Vtiger/Mailer.php';
include_once 'include/simplehtmldom/simple_html_dom.php';
include_once 'libraries/InStyle/InStyle.php';
include_once 'libraries/ToAscii/ToAscii.php';
include_once 'include/database/PearDatabase.php';

class Emails_Mailer_Model extends Vtiger_Mailer {

	public static function getInstance() {
		return new self();
	}

	/**
	 * Function returns error from phpmailer
	 * @return <String>
	 */
	function getError() {
		return $this->ErrorInfo;
	}

	/**
	 * Function to replace space with %20 to make image url as valid
	 * @param type $htmlContent
	 * @return type
	 */
	public function makeImageURLValid($htmlContent) {
		$doc = new DOMDocument();
		$imageUrls = array();
		if (!empty($htmlContent)) {
			@$doc->loadHTML($htmlContent);
			$tags = $doc->getElementsByTagName('img');
			foreach ($tags as $tag) {
				$imageUrl = $tag->getAttribute('src');
				$imageUrls[$imageUrl] = str_replace(" ", "%20", $imageUrl);
			}
		}
		foreach ($imageUrls as $key => $value) {
			$htmlContent = str_replace($key, $value, $htmlContent);
		}
		return $htmlContent;
	}

	public static function convertCssToInline($content) {
		if (preg_match('/<style[^>]+>(?<css>[^<]+)<\/style>/s', $content)) {
			$instyle = new InStyle();
			$convertedContent = $instyle->convert($content);
			if ($convertedContent) {
				return $convertedContent;
			}
		}

		return $content;
	}

	public static function retrieveMessageIdFromMailroom($crmId) {
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT messageid FROM vtiger_mailscanner_ids WHERE crmid=?', array($crmId));
		return $db->query_result($result, 'messageid', 0);
	}

	/**
	 * Function generates randomId with host details
	 * @return type
	 */
	public static function generateMessageID() {
		$generateId = sprintf("<%s.%s@%s>", base_convert(microtime(), 10, 36), base_convert(bin2hex(openssl_random_pseudo_bytes(8)), 16, 36), gethostname());
		return $generateId;
	}

	/**
	 * Function inserts new message for a crmid which was not present in 
	 * below table
	 * @param type $crmId
	 */
	public static function updateMessageIdByCrmId($messageId, $crmId) {
		$db = PearDatabase::getInstance();
		$existingResult = array();
		//Get existing refids for a given crm id and update new refids to the crmid
		$existingResultObject = $db->pquery("SELECT refids FROM vtiger_mailscanner_ids WHERE crmid=? AND refids != 'null'", array($crmId));
		$num_rows = $db->num_rows($existingResultObject);
		if ($num_rows > 0) {
			$existingResult = json_decode($db->query_result($existingResultObject, 'refids', 0), true);
			// Checking if first parameter is not an array
			if (is_array($existingResult)) {
				$existingResultValue = array_merge($existingResult, array($messageId));
				$refIds = json_encode($existingResultValue);
				$db->pquery("UPDATE vtiger_mailscanner_ids SET refids=? WHERE crmid=? ", array($refIds, $crmId));
			}
		} else {
			$db->pquery("INSERT INTO vtiger_mailscanner_ids (messageid, crmid) VALUES(?,?)", array($messageId, $crmId));
		}
	}

	public function convertToValidURL($htmlContent) {
		if (!$this->dom) {
			$this->dom = new DOMDocument();
			@$this->dom->loadHTML($htmlContent);
		}
		$anchorElements = $this->dom->getElementsByTagName('a');
		$urls = array();
		foreach ($anchorElements as $anchorElement) {
			$url = $anchorElement->getAttribute('href');
			if (!empty($url)) {
				//If url start with mailto:,tel:,#,news: then skip those urls 
				if (!preg_match("~^(?:f|ht)tps?://~i", $url) && (strpos('$', $url[0]) !== 0) && (strpos($url, 'mailto:') !== 0 ) && (strpos($url, 'tel:') !== 0 ) && $url[0] !== '#' && !preg_match("/news:\/\//i", $url)) {
					$url = "http://" . $url;
					$urls[$anchorElement->getAttribute('href')] = $url;
					$htmlContent = $this->replaceURLWithValidURLInContent($htmlContent, $anchorElement->getAttribute('href'), $url);
				}
			}
		}
		return $htmlContent;
	}

	public function replaceURLWithValidURLInContent($htmlContent, $searchURL, $replaceWithURL) {
		$search = '"' . $searchURL . '"';
		$toReplace = '"' . $replaceWithURL . '"';
		$pos = strpos($htmlContent, $search);
		if ($pos != false) {
			$replacedContent = substr_replace($htmlContent, $toReplace, $pos) . substr($htmlContent, $pos + strlen($search));
			return $replacedContent;
		}
		return $htmlContent;
	}

	public static function getProcessedContent($content) {
		// remove script tags from whole html content
		$processedContent = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $content);
		return $processedContent;
	}

	/**
	 * Function to Convert an UTF-8 string to Ascii string 
	 * @param <string> $content - string containing utf-8 characters
	 * @param <string> $subst_chr - if the character is not found it replaces with this value
	 * @return <string> Ascii String
	 */
	public static function convertToAscii($content, $subst_chr = '') {
		return ToAscii::convertToAscii($content, $subst_chr);
	}

}
