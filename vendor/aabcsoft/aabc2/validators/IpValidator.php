<?php


namespace aabc\validators;

use Aabc;
use aabc\base\InvalidConfigException;
use aabc\helpers\Html;
use aabc\helpers\Json;
use aabc\web\JsExpression;


class IpValidator extends Validator
{
    
    const IPV6_ADDRESS_LENGTH = 128;
    
    const IPV4_ADDRESS_LENGTH = 32;
    
    const NEGATION_CHAR = '!';

    
    public $networks = [
        '*' => ['any'],
        'any' => ['0.0.0.0/0', '::/0'],
        'private' => ['10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16', 'fd00::/8'],
        'multicast' => ['224.0.0.0/4', 'ff00::/8'],
        'linklocal' => ['169.254.0.0/16', 'fe80::/10'],
        'localhost' => ['127.0.0.0/8', '::1'],
        'documentation' => ['192.0.2.0/24', '198.51.100.0/24', '203.0.113.0/24', '2001:db8::/32'],
        'system' => ['multicast', 'linklocal', 'localhost', 'documentation'],
    ];
    
    public $ipv6 = true;
    
    public $ipv4 = true;
    
    public $subnet = false;
    
    public $normalize = false;
    
    public $negation = false;
    
    public $expandIPv6 = false;
    
    public $ipv4Pattern = '/^(?:(?:2(?:[0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9])\.){3}(?:(?:2([0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9]))$/';
    
    public $ipv6Pattern = '/^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))$/';
    
    public $message;
    
    public $ipv6NotAllowed;
    
    public $ipv4NotAllowed;
    
    public $wrongCidr;
    
    public $noSubnet;
    
    public $hasSubnet;
    
    public $notInRange;

    
    private $_ranges = [];


    
    public function init()
    {
        parent::init();

        if (!$this->ipv4 && !$this->ipv6) {
            throw new InvalidConfigException('Both IPv4 and IPv6 checks can not be disabled at the same time');
        }

        if (!defined('AF_INET6') && $this->ipv6) {
            throw new InvalidConfigException('IPv6 validation can not be used. PHP is compiled without IPv6');
        }

        if ($this->message === null) {
            $this->message = Aabc::t('aabc', '{attribute} must be a valid IP address.');
        }
        if ($this->ipv6NotAllowed === null) {
            $this->ipv6NotAllowed = Aabc::t('aabc', '{attribute} must not be an IPv6 address.');
        }
        if ($this->ipv4NotAllowed === null) {
            $this->ipv4NotAllowed = Aabc::t('aabc', '{attribute} must not be an IPv4 address.');
        }
        if ($this->wrongCidr === null) {
            $this->wrongCidr = Aabc::t('aabc', '{attribute} contains wrong subnet mask.');
        }
        if ($this->noSubnet === null) {
            $this->noSubnet = Aabc::t('aabc', '{attribute} must be an IP address with specified subnet.');
        }
        if ($this->hasSubnet === null) {
            $this->hasSubnet = Aabc::t('aabc', '{attribute} must not be a subnet.');
        }
        if ($this->notInRange === null) {
            $this->notInRange = Aabc::t('aabc', '{attribute} is not in the allowed range.');
        }
    }

    
    public function setRanges($ranges)
    {
        $this->_ranges = $this->prepareRanges((array) $ranges);
    }

    
    public function getRanges()
    {
        return $this->_ranges;
    }

    
    protected function validateValue($value)
    {
        $result = $this->validateSubnet($value);
        if (is_array($result)) {
            $result[1] = array_merge(['ip' => is_array($value) ? 'array()' : $value], $result[1]);
            return $result;
        } else {
            return null;
        }
    }

    
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;

        $result = $this->validateSubnet($value);
        if (is_array($result)) {
            $result[1] = array_merge(['ip' => is_array($value) ? 'array()' : $value], $result[1]);
            $this->addError($model, $attribute, $result[0], $result[1]);
        } else {
            $model->$attribute = $result;
        }
    }

    
    private function validateSubnet($ip)
    {
        if (!is_string($ip)) {
            return [$this->message, []];
        }

        $negation = null;
        $cidr = null;
        $isCidrDefault = false;

        if (preg_match($this->getIpParsePattern(), $ip, $matches)) {
            $negation = ($matches[1] !== '') ? $matches[1] : null;
            $ip = $matches[2];
            $cidr = isset($matches[4]) ? $matches[4] : null;
        }

        if ($this->subnet === true && $cidr === null) {
            return [$this->noSubnet, []];
        }
        if ($this->subnet === false && $cidr !== null) {
            return [$this->hasSubnet, []];
        }
        if ($this->negation === false && $negation !== null) {
            return [$this->message, []];
        }

        if ($this->getIpVersion($ip) == 6) {
            if ($cidr !== null) {
                if ($cidr > static::IPV6_ADDRESS_LENGTH || $cidr < 0) {
                    return [$this->wrongCidr, []];
                }
            } else {
                $isCidrDefault = true;
                $cidr = static::IPV6_ADDRESS_LENGTH;
            }

            if (!$this->validateIPv6($ip)) {
                return [$this->message, []];
            }
            if (!$this->ipv6) {
                return [$this->ipv6NotAllowed, []];
            }

            if ($this->expandIPv6) {
                $ip = $this->expandIPv6($ip);
            }
        } else {
            if ($cidr !== null) {
                if ($cidr > static::IPV4_ADDRESS_LENGTH || $cidr < 0) {
                    return [$this->wrongCidr, []];
                }
            } else {
                $isCidrDefault = true;
                $cidr = static::IPV4_ADDRESS_LENGTH;
            }
            if (!$this->validateIPv4($ip)) {
                return [$this->message, []];
            }
            if (!$this->ipv4) {
                return [$this->ipv4NotAllowed, []];
            }
        }

        if (!$this->isAllowed($ip, $cidr)) {
            return [$this->notInRange, []];
        }

        $result = $negation . $ip;

        if ($this->subnet !== false && (!$isCidrDefault || $isCidrDefault && $this->normalize)) {
            $result .= "/$cidr";
        }

        return $result;
    }

    
    private function expandIPv6($ip)
    {
        $hex = unpack('H*hex', inet_pton($ip));
        return substr(preg_replace('/([a-f0-9]{4})/i', '$1:', $hex['hex']), 0, -1);
    }

    
    private function isAllowed($ip, $cidr)
    {
        if (empty($this->ranges)) {
            return true;
        }

        foreach ($this->ranges as $string) {
            list($isNegated, $range) = $this->parseNegatedRange($string);
            if ($this->inRange($ip, $cidr, $range)) {
                return !$isNegated;
            }
        }

        return false;
    }

    
    private function parseNegatedRange($string)
    {
        $isNegated = strpos($string, static::NEGATION_CHAR) === 0;
        return [$isNegated, $isNegated ? substr($string, strlen(static::NEGATION_CHAR)) : $string];
    }

    
    private function prepareRanges($ranges)
    {
        $result = [];
        foreach ($ranges as $string) {
            list($isRangeNegated, $range) = $this->parseNegatedRange($string);
            if (isset($this->networks[$range])) {
                $replacements = $this->prepareRanges($this->networks[$range]);
                foreach ($replacements as &$replacement) {
                    list($isReplacementNegated, $replacement) = $this->parseNegatedRange($replacement);
                    $result[] = ($isRangeNegated && !$isReplacementNegated ? static::NEGATION_CHAR : '') . $replacement;
                }
            } else {
                $result[] = $string;
            }
        }
        return array_unique($result);
    }

    
    protected function validateIPv4($value)
    {
        return preg_match($this->ipv4Pattern, $value) !== 0;
    }

    
    protected function validateIPv6($value)
    {
        return preg_match($this->ipv6Pattern, $value) !== 0;
    }

    
    private function getIpVersion($ip)
    {
        return strpos($ip, ':') === false ? 4 : 6;
    }

    
    private function getIpParsePattern()
    {
        return '/^(' . preg_quote(static::NEGATION_CHAR) . '?)(.+?)(\/(\d+))?$/';
    }

    
    private function inRange($ip, $cidr, $range)
    {
        $ipVersion = $this->getIpVersion($ip);
        $binIp = $this->ip2bin($ip);

        $parts = explode('/', $range);
        $net = array_shift($parts);
        $range_cidr = array_shift($parts);


        $netVersion = $this->getIpVersion($net);
        if ($ipVersion !== $netVersion) {
            return false;
        }
        if ($range_cidr === null) {
            $range_cidr = $netVersion === 4 ? static::IPV4_ADDRESS_LENGTH : static::IPV6_ADDRESS_LENGTH;
        }

        $binNet = $this->ip2bin($net);
        return substr($binIp, 0, $range_cidr) === substr($binNet, 0, $range_cidr) && $cidr >= $range_cidr;
    }

    
    private function ip2bin($ip)
    {
        if ($this->getIpVersion($ip) === 4) {
            return str_pad(base_convert(ip2long($ip), 10, 2), static::IPV4_ADDRESS_LENGTH, '0', STR_PAD_LEFT);
        } else {
            $unpack = unpack('A16', inet_pton($ip));
            $binStr = array_shift($unpack);
            $bytes = static::IPV6_ADDRESS_LENGTH / 8; // 128 bit / 8 = 16 bytes
            $result = '';
            while ($bytes-- > 0) {
                $result = sprintf('%08b', isset($binStr[$bytes]) ? ord($binStr[$bytes]) : '0') . $result;
            }
            return $result;
        }
    }

    
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'aabc.validation.ip(value, messages, ' . Json::htmlEncode($options) . ');';
    }

    
    public function getClientOptions($model, $attribute)
    {
        $messages = [
            'ipv6NotAllowed' => $this->ipv6NotAllowed,
            'ipv4NotAllowed' => $this->ipv4NotAllowed,
            'message' => $this->message,
            'noSubnet' => $this->noSubnet,
            'hasSubnet' => $this->hasSubnet,
        ];
        foreach ($messages as &$message) {
            $message = Aabc::$app->getI18n()->format($message, [
                'attribute' => $model->getAttributeLabel($attribute),
            ], Aabc::$app->language);
        }

        $options = [
            'ipv4Pattern' => new JsExpression(Html::escapeJsRegularExpression($this->ipv4Pattern)),
            'ipv6Pattern' => new JsExpression(Html::escapeJsRegularExpression($this->ipv6Pattern)),
            'messages' => $messages,
            'ipv4' => (bool) $this->ipv4,
            'ipv6' => (bool) $this->ipv6,
            'ipParsePattern' => new JsExpression(Html::escapeJsRegularExpression($this->getIpParsePattern())),
            'negation' => $this->negation,
            'subnet' => $this->subnet,
        ];
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        return $options;
    }
}
