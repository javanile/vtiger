<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/** Classes to avoid logging */
class LoggerManager
{
    protected static $overrideinfo = null;

    public static function getlogger($name = 'ROOT')
    {
        if (static::$overrideinfo === null) {
            static::$overrideinfo = [];
            foreach (preg_split('/\\s*,\\s*/', trim(getenv('VT_DEBUG'))) as $config) {
                list($key, $value) = preg_split('/\\s*:\\s*/', $config);
                if ($value !== null) {
                    static::$overrideinfo[strtoupper($key)] = strtoupper($value);
                } else {
                    static::$overrideinfo['ROOT'] = strtoupper($key);
                }
            }
        }

        $configinfo = LoggerPropertyConfigurator::getInstance()->getConfigInfo($name);

        if ($configinfo && isset(static::$overrideinfo['ROOT'])) {
            $configinfo['level'] = static::$overrideinfo['ROOT'];
        }

        if ($configinfo && isset(static::$overrideinfo[$name])) {
            $configinfo['level'] = static::$overrideinfo[$name];
        }

        return new Logger($name, $configinfo);
    }
}

/**
 * Core logging class.
 */
class Logger
{
    private $name = false;
    private $appender = false;
    private $configinfo = false;

    /**
     * Writing log file information could cost in-terms of performance.
     * Enable logging based on the levels here explicitly.
     */
    private $enableLogLevel = [
        'FATAL' => false,
        'ERROR' => false,
        'WARN'  => false,
        'INFO'  => false,
        'DEBUG' => false,
    ];

    /**
     * @var array
     */
    private $logLevelWeight = [
        'FATAL' => 0,
        'ERROR' => 1,
        'WARN'  => 2,
        'INFO'  => 3,
        'DEBUG' => 4,
    ];

    /**
     * Logger constructor.
     *
     * @param $name
     * @param bool $configinfo
     */
    public function __construct($name, $configinfo = false)
    {
        $this->name = $name;
        $this->configinfo = $configinfo;
        $debug = getenv('VT_DEBUG') ?: null;

        if ($configinfo && isset($debug) && $debug && strtolower($debug) != 'false' && $debug != '0') {
            foreach ($this->enableLogLevel as $level => $flag) {
                $this->enableLogLevel[$level] = $this->isLevelRelevantThen($level, $configinfo['level']);
            }
        }

        /* For migration log-level we need debug turned-on */
        if (strtoupper($name) == 'MIGRATION') {
            $this->enableLogLevel['DEBUG'] = true;
        }
    }

    /**
     * @param $level
     * @param $message
     */
    public function emit($level, $message)
    {
        if (!$this->appender) {
            $filename = 'logs/vtigercrm.log';
            if ($this->configinfo && isset($this->configinfo['appender']['File'])) {
                $filename = $this->configinfo['appender']['File'];
            }
            $this->appender = new LoggerAppenderFile($filename, 0777);
        }

        $mypid = @getmypid();

        $this->appender->emit("$level [$mypid] $this->name - ", $message);
    }

    /**
     * @param $message
     */
    public function info($message)
    {
        if ($this->isLevelEnabled('INFO')) {
            $this->emit('INFO', $message);
        }
    }

    public function debug($message)
    {
        if ($this->isDebugEnabled()) {
            $this->emit('DEBUG', $message);
        }
    }

    public function warn($message)
    {
        if ($this->isLevelEnabled('WARN')) {
            $this->emit('WARN', $message);
        }
    }

    public function fatal($message)
    {
        if ($this->isLevelEnabled('FATAL')) {
            $this->emit('FATAL', $message);
        }
    }

    /**
     * @param $message
     */
    public function error($message)
    {
        if ($this->isLevelEnabled('ERROR')) {
            $this->emit('ERROR', $message);
        }
    }

    /**
     * @param $level
     *
     * @return bool
     */
    public function isLevelEnabled($level)
    {
        if ($this->enableLogLevel[$level] && $this->configinfo) {
            return $this->isLevelRelevantThan($level, $this->configinfo['level']);
        }

        return false;
    }

    /**
     * @param $level1
     * @param $level2
     */
    public function isLevelRelevantThen($level1, $level2)
    {
        return $this->logLevelWeight[$level1] <= $this->logLevelWeight[$level2];
    }

    /**
     * @return bool
     */
    public function isDebugEnabled()
    {
        return $this->isLevelEnabled('DEBUG');
    }
}

/**
 * Log message appender to file.
 */
class LoggerAppenderFile
{
    /**
     * @var
     */
    private $filename;

    /**
     * @var int
     */
    private $chmod;

    /**
     * LoggerAppenderFile constructor.
     *
     * @param $filename
     * @param int $chmod
     */
    public function __construct($filename, $chmod = 0222)
    {
        $this->filename = $filename;
        $this->chmod = $chmod;
    }

    /**
     * @param $prefix
     * @param $message
     */
    public function emit($prefix, $message)
    {
        if ($this->chmod != 0777 && file_exists($this->filename)) {
            if (is_readable($this->filename)) {
                chmod($this->filename, $this->chmod);
            }
        }

        $fh = fopen($this->filename, 'a');

        if ($fh) {
            $err = fwrite($fh, date('Y-m-d H:i:s')." $prefix $message\n");
            fclose($fh);
        }
    }
}
