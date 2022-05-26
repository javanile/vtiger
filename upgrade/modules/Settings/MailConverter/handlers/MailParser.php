<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_MailParser {

	var $msg = false;

	function __construct($string) {
		$this->msg = mb_convert_encoding($string, 'HTML-ENTITIES', 'UTF-8');
	}

	/*
	 * Function to parse html content to plain text preserving the html structure
	 */
	function parseHtml() {
		$this->msg = str_replace("\r\n", "\n", $this->msg);
		$this->msg = str_replace("\r", "\n", $this->msg);

		$domElement = new DOMDocument(null, 'UTF-8');
		if(!@$domElement->loadHTML($this->msg)) {
			return $this->msg;
		}

		$result = $this->parse($domElement);
		$result = preg_replace("/[ \t]*\n[ \t]*/im", "\n", $result);
		$result = str_replace("\xc2\xa0",' ',  $result);
		$result = trim(str_replace("Â", " ", strip_tags($result)));

		return $result;
	}

	function getNextNode($node) {
		$nextNode = $node->nextSibling;
		while($nextNode != null) {
			if($nextNode instanceof DOMElement) {
				break;
			}
			$nextNode = $nextNode->nextSibling;
		}
		$nextNodeName = null;
		if($nextNode instanceof DOMElement && $nextNode != null) {
			$nextNodeName = strtolower($nextNode->nodeName);
		}

		return $nextNodeName;
	}

	function getPrevNode($node) {
		$prevNode = $node->previousSibling;
		while ($prevNode != null) {
			if ($prevNode instanceof DOMElement) {
				break;
			}
			$prevNode = $prevNode->previousSibling;
		}
		$prevNodeName = null;
		if ($prevNode instanceof DOMElement && $prevNode != null) {
			$prevNodeName = strtolower($prevNode->nodeName);
		}

		return $prevNodeName;
	}

	function parse($node) {
		if($node instanceof DOMText) {
			return preg_replace("/[\\t\\n\\f\\r ]+/im", " ", $node->wholeText);
		}

		if($node instanceof DOMDocumentType) {
			return "";
		}

		$nextName = $this->getNextNode($node);
		$prevName = $this->getPrevNode($node);
		$name = strtolower($node->nodeName);
		$parentNodeName = strtolower($node->parentNode->nodeName);
		$firstChildNode='';
		$firstChildName='';
		if($node->childNodes){
			$firstChildNode = $node->childNodes->item(0);
			$firstChildName = strtolower($firstChildNode->nodeName);
		}
		switch($name) {
			case "hr"      : return "-------------------------------------------\n";

			case "style"   :
			case "head"    :
			case "title"   :
			case "meta"    :
			case "script"  : return "";

			case "h1"      :
			case "h2"      :
			case "h3"      :
			case "h4"      :
			case "h5"      :
			case "h6"      : $output = "";
							 break;
			case "p"       :
			case "div"     : if($firstChildName != "div") {
								 $output = "\n";
							 }
							 break;
			case "tr"      :
			case "ul"      :
			case "ol"      :
			case "li"      : $output = "\n";
							 break;

			case "td"      : $output = "\t";
							 break;

			default        : $output = "";
							 break;
		}

		if($node->childNodes) {
			for($i = 0; $i < $node->childNodes->length; $i++) {
				$n = $node->childNodes->item($i);
				$text = $this->parse($n);
				$output .= $text;
			}
		}

		switch($name) {
			case "style"   :
			case "head"    :
			case "title"   :
			case "meta"    :
			case "script"  : return "";

			case "h1"      :
			case "h2"      :
			case "h3"      :
			case "h4"      :
			case "h5"      :
			case "h6"      : $output .= "";
				break;
			case "p"       :
			case "br"      : if(($nextName != "div" && $parentNodeName != "div") || ($prevName!= null)) {
								$output .= "\n";
							 }
							 break;
			case "div"     : if($nextName != "div" && $nextName != "br" && $nextName != null) {
								 $output .= "\n";
							 }
							 break;

			case "img"     : $src = $node->getAttribute("src");
							 $alt = $node->getAttribute("alt");
							 if($alt == null) {
								 $output = "[Image]($src)";
							 } else {
								 $output = "[Image : $alt]($src)";
							 }
							 break;

			default        :
		}

		return $output;
	}
}