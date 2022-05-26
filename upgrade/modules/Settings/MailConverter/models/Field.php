<?php

/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_MailConverter_Field_Model extends Vtiger_Field_Model {
    
    public static $timeZonePickListValues = array(' '=>'LBL_I_DONT_KNOW',
                                    '-12:00' => '(GMT -12:00 hours) Eniwetok, Kwajalein',
                                    '-11:00' => '(GMT -11:00 hours) Midway Island, Samoa',
                                    '-10:00' => '(GMT -10:00 hours) Hawaii',
                                    '-9:00' => '(GMT -9:00 hours) Alaska',
                                    '-8:00'=>'(GMT -8:00 hours) Pacific Time (US & Canada)',
                                    '-7:00'=>'(GMT -7:00 hours) Mountain Time (US & Canada)',
                                    '-6:00' => '(GMT -6:00 hours) Central Time (US & Canada), Mexico City',
                                    '-5:00' =>'(GMT -5:00 hours) Eastern Time (US & Canada), Bogota, Lima, Quito',
                                    '-4:00' => '(GMT -4:00 hours) Atlantic Time (Canada), Caracas, La Paz',
                                    '-3:30' =>'(GMT -3:30 hours) Newfoundland',
                                    '-3:00' => '(GMT -3:00 hours) Brazil, Buenos Aires, Georgetown',
                                    '-2:00' => '(GMT -2:00 hours) Mid-Atlantic',
                                    '-1:00' => '(GMT -1:00 hours) Azores, Cape Verde Islands',
                                    '0:00' =>'(GMT) Western Europe Time, London, Lisbon, Casablanca, Monrovia',
                                    '+1:00' => '(GMT +1:00 hours) CET(Central Europe Time), Brussels, Copenhagen, Madrid, Paris',
                                    '+2:00' => '(GMT +2:00 hours) EET(Eastern Europe Time), Kaliningrad, South Africa',
                                    '+3:00' => '(GMT +3:00 hours) Baghdad, Kuwait, Riyadh, Moscow, St. Petersburg, Volgograd, Nairobi',	
                                    '+3:30' => '(GMT +3:30 hours) Tehran',
                                    '+4:00' => '{GMT +4:00 hours) Abu Dhabi, Muscat, Baku, Tbilisi',
                                    '+4:30' => '(GMT +4:30 hours) Kabul]',	
                                    '+5:00' => '(GMT +5:00 hours) Ekaterinburg, Islamabad, Karachi, Tashkent',
                                    '+5:30' => '(GMT +5:30 hours) Bombay, Calcutta, Madras, New Delhi',
                                    '+6:00' => '(GMT +6:00 hours) Almaty, Dhaka, Colombo',
                                    '+7:00' => '(GMT +7:00 hours) Bangkok, Hanoi, Jakarta',
                                    '+8:00' => '(GMT +8:00 hours) Beijing, Perth, Singapore, Hong Kong, Chongqing, Urumqi, Taipei',
                                    '+9:00' => '(GMT +9:00 hours) Tokyo, Seoul, Osaka, Sapporo, Yakutsk',
                                    '+9:30'  => '(GMT +9:30 hours) Adelaide, Darwin',
                                    '+10:00' => '(GMT +10:00 hours) EAST(East Australian Standard), Guam, Papua New Guinea, Vladivostok',
                                    '+11:00' => '(GMT +11:00 hours) Magadan, Solomon Islands, New Caledonia',
                                    '+12:00' => '(GMT +12:00 hours) Auckland, Wellington, Fiji, Kamchatka, Marshall Island');


    public function getFieldDataType() {
        return $this->get('datatype');
    }
    
    public function getPickListValues() {
        $fieldName = $this->getName();
        $pickListValues = array();
        if($fieldName == 'searchfor') {
            $optionList = array('ALL','UNSEEN');
            foreach($optionList as $option) {
                $pickListValues[$option] = vtranslate($option, 'Settings::MailConverter');
            }
        }else if ($fieldName == 'markas') {
            $optionList = array('UNSEEN','SEEN');
            foreach($optionList as $option) {
				$pickListValues[$option] = vtranslate($option, 'Settings::MailConverter');
            }
        }else if ($fieldName == 'time_zone') {
            $pickListValues = self::$timeZonePickListValues;
            
        }
        return $pickListValues;
    }
    
    public function getRadioOptions() {
        $fieldName = $this->getName();
        if($fieldName == 'ssltype') {
            $options['notls'] = vtranslate('No TLS','Settings::MailConverter');
            $options['tls'] = vtranslate('TLS','Settings::MailConverter');
            $options['ssl'] = vtranslate('SSL','Settings::MailConverter');
        }
        elseif($fieldName == 'sslmethod') {
            $options['validate-cert'] = vtranslate('Validate SSL Certificate','Settings::MailConverter');
            $options['novalidate-cert'] = vtranslate('Do Not Validate SSL Certificate','Settings::MailConverter');
        }
        else if($fieldName == 'protocol') {
            $options['imap'] = vtranslate('IMAP2', 'Settings::MailConverter');
            $options['imap4'] = vtranslate('IMAP4', 'Settings::MailConverter');
        }
        return $options;
    }
    
    public function isEditable() {
        if(!property_exists($this, 'isEditable')){
            return true;
        }
        return $this->isEditable;
    }

	public function getPicklistColors() {
		return array();
	}
}