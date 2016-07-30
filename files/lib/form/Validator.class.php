<?php
/**
 *  Form-Builder DevTool
 *  Copyright (C) 2016  Christopher Mühl
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace wcf\form;

/**
 * Class to validate form inputs with
 *
 * @author      Christopher Mühl
 * @copyright   2016 Christopher Mühl
 * @package     io.padarom.devtools.formbuilder
 * @subpackage  form
 * @category    Community Framework
 */
class Validator{
	public static function validate($value, $rule){
		list($rule, $parameters) = self::parseRule($rule);

		if($rule == ''){
			return;
		}

		$methodName = "validate".$rule;

		if(!method_exists('wcf\form\Validator', $methodName))
			throw new \Exception("There is no validator for the rule '$rule'.");

		return self::$methodName($value, $parameters);
	}

	/**
	 * Validates that a given value is an integer.
	 *
	 * @param  mixed $value
	 *
	 * @return bool
	 */
	protected static function validateInteger($value){
		return is_null($value) || filter_var($value, FILTER_VALIDATE_INT) !== false;
	}

	/**
	 * Validates that a given value is numeric.
	 *
	 * @param  mixed $value
	 *
	 * @return bool
	 */
	protected static function validateNumeric($value){
		return is_null($value) || is_numeric($value);
	}

	/**
	 * Validates that a given value is of the type string.
	 *
	 * @param  mixed $value
	 *
	 * @return bool
	 */
	protected static function validateString($value){
		return is_null($value) || is_string($value);
	}

	/**
	 * Validates that a given value has exactly N digits.
	 *
	 * @param  mixed $value
	 * @param  array $parameters
	 *
	 * @return bool
	 */
	protected static function validateDigits($value, $parameters){
		return self::validateNumeric($value) && strlen((string)$value) == $parameters[0];
	}

	/**
	 * Validates that a given value is numeric and has between N and M digits.
	 *
	 * @param  mixed $value
	 * @param  array $parameters
	 *
	 * @return bool
	 */
	protected static function validateDigitsBetween($value, $parameters){
		$length = strlen((string)$value);

		return self::validateNumeric($value)
		&& $length >= $parameters[0] && $length <= $parameters[1];
	}

	/**
	 * Validates that a given value is a valid email address.
	 *
	 * @param  mixed $value
	 *
	 * @return bool
	 */
	protected static function validateEmail($value){
		return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
	}

	/**
	 * Validates that a given value is a valid URL.
	 *
	 * @param  mixed $value
	 *
	 * @return bool
	 */
	protected static function validateUrl($value){
		/*
		 * This pattern is derived from Symfony\Component\Validator\Constraints\UrlValidator (2.7.4)
		 * (c) Fabien Potencier <fabien@symfony.com> http://symfony.com
		 */
		$pattern = '~^
            ((aaa|aaas|about|acap|acct|acr|adiumxtra|afp|afs|aim|apt|attachment|aw|barion|beshare|bitcoin|blob|bolo|callto|cap|chrome|chrome-extension|cid|coap|coaps|com-eventbrite-attendee|content|crid|cvs|data|dav|dict|dlna-playcontainer|dlna-playsingle|dns|dntp|dtn|dvb|ed2k|example|facetime|fax|feed|feedready|file|filesystem|finger|fish|ftp|geo|gg|git|gizmoproject|go|gopher|gtalk|h323|ham|hcp|http|https|iax|icap|icon|im|imap|info|iotdisco|ipn|ipp|ipps|irc|irc6|ircs|iris|iris.beep|iris.lwz|iris.xpc|iris.xpcs|itms|jabber|jar|jms|keyparc|lastfm|ldap|ldaps|magnet|mailserver|mailto|maps|market|message|mid|mms|modem|ms-help|ms-settings|ms-settings-airplanemode|ms-settings-bluetooth|ms-settings-camera|ms-settings-cellular|ms-settings-cloudstorage|ms-settings-emailandaccounts|ms-settings-language|ms-settings-location|ms-settings-lock|ms-settings-nfctransactions|ms-settings-notifications|ms-settings-power|ms-settings-privacy|ms-settings-proximity|ms-settings-screenrotation|ms-settings-wifi|ms-settings-workplace|msnim|msrp|msrps|mtqp|mumble|mupdate|mvn|news|nfs|ni|nih|nntp|notes|oid|opaquelocktoken|pack|palm|paparazzi|pkcs11|platform|pop|pres|prospero|proxy|psyc|query|redis|rediss|reload|res|resource|rmi|rsync|rtmfp|rtmp|rtsp|rtsps|rtspu|secondlife|service|session|sftp|sgn|shttp|sieve|sip|sips|skype|smb|sms|smtp|snews|snmp|soap.beep|soap.beeps|soldat|spotify|ssh|steam|stun|stuns|submit|svn|tag|teamspeak|tel|teliaeid|telnet|tftp|things|thismessage|tip|tn3270|turn|turns|tv|udp|unreal|urn|ut2004|vemmi|ventrilo|videotex|view-source|wais|webcal|ws|wss|wtai|wyciwyg|xcon|xcon-userid|xfire|xmlrpc\.beep|xmlrpc.beeps|xmpp|xri|ymsgr|z39\.50|z39\.50r|z39\.50s))://                                 # protocol
            (([\pL\pN-]+:)?([\pL\pN-]+)@)?          # basic auth
            (
                ([\pL\pN\pS-\.])+(\.?([\pL]|xn\-\-[\pL\pN-]+)+\.?) # a domain name
                    |                                              # or
                \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}                 # a IP address
                    |                                              # or
                \[
                    (?:(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-f]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,1}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,2}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,3}(?:(?:[0-9a-f]{1,4})))?::(?:(?:[0-9a-f]{1,4})):)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,4}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,5}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,6}(?:(?:[0-9a-f]{1,4})))?::))))
                \]  # a IPv6 address
            )
            (:[0-9]+)?                              # a port (optional)
            (/?|/\S+)                               # a /, nothing or a / with something
        $~ixu';

		return preg_match($pattern, $value) === 1;
	}

	/**
	 * Validates that a given value is an instance of DateTime or is parseable to a time.
	 *
	 * @param  mixed $value
	 *
	 * @return bool
	 */
	protected static function validateDate($value){
		if($value instanceof DateTime)
			return true;

		if(strtotime($value) === false)
			return false;

		$date = date_parse($value);

		return checkdate($date['month'], $date['day'], $date['year']);
	}


	/**
	 * Validates that a given value is set and not null.
	 *
	 * @param  mixed $value
	 *
	 * @return bool
	 */
	protected static function validateIsset($value){
		return isset($value);
	}

	/**
	 * Validates that a given ID instantiates a valid object.
	 *
	 * @param  mixed $value
	 * @param  array $parameters
	 *
	 * @return bool
	 */
	protected static function validateClass($value, $parameters){
		$class = $parameters[0];

		$object = new $class($value);
		$attribute = $class::getDatabaseTableIndexName();

		return $object->$attribute;
	}

	protected static function parseRule($rules){
		if(is_array($rules)){
			$rules = self::parseArrayRule($rules);
		}else{
			$rules = self::parseStringRules($rules);
		}

		$rules[0] = self::normalizeRule($rules[0]);

		return $rules;
	}

	protected static function parseArrayRule(array $rules){
		return array(self::studly($rules[0]), array_slice($rules, 1));
	}

	protected static function parseStringRules($rules){
		$parameters = [];

		if(strpos($rules, ':') !== false){
			list($rules, $parameter) = explode(':', $rules, 2);

			$parameters = self::parseParameters($rules, $parameter);
		}

		return array(self::studly(trim($rules)), $parameters);
	}

	protected static function parseParameters($rule, $parameter){
		if(strtolower($rule) == 'regex'){
			return array($parameter);
		}

		return str_getcsv($parameter);
	}

	protected static function normalizeRule($rule){
		switch($rule){
			case 'Int':
				return 'Integer';
			case 'Bool':
				return 'Boolean';
			default:
				return $rule;
		}
	}

	protected static function studly($value){
		$value = ucwords(str_replace(array('-', '_'), ' ', $value));

		return str_replace(' ', '', $value);
	}
}
