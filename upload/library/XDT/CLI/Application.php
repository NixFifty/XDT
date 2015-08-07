<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Helper.php';

/**
 * Main XfCli application
 */
class XDT_CLI_Application
{

    /**
     * @var string	XenForo base dir
     */
    public static $_baseDir 	= null;

    /**
     * @var array	Config (if any)
     */
    protected static $_config 	= null;

    /**
     * Initialize XfCli
     *
     * @return	void
     */
    public static function initialize()
    {
        self::registerAutoloader();
        self::setIncludePaths();
    }

    /**
     * Register Class Autoloader
     *
     * @return	void
     */
    protected static function registerAutoloader()
    {
        spl_autoload_register(array('XDT_CLI_Autoloader', 'run'));
    }

    /**
     * Set exception / error handlers
     *
     * @return		void
     */
    public static function setExceptionHandlers()
    {
        set_exception_handler(array('XDT_CLI_ExceptionHandler', 'handleException'));
        set_error_handler(array('XDT_CLI_ExceptionHandler', 'handleError'));

        XDT_CLI_Abstract::$_useExceptions 	= true;
        XDT_CLI_Abstract::$_exceptionClass 	= 'XDT_CLI_Exception';
    }

    /**
     * Set include paths for Zend libraries
     *
     * @return	void
     */
    protected static function setIncludePaths()
    {
        set_include_path(
            dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . PATH_SEPARATOR .
            self::xfBaseDir()  . 'library' . DIRECTORY_SEPARATOR .
            '.' . PATH_SEPARATOR . get_include_path()
        );
    }

    /**
     * Get XenForo base dir
     *
     * @return	string
     */
    public static function xfBaseDir()
    {
        if (self::$_baseDir == null)
        {
            self::detectXenForo();
        }

        return self::$_baseDir;
    }

    /**
     * Detect XenForo installation
     *
     * @return	void
     */
    protected static function detectXenForo()
    {
        $ds 		= DIRECTORY_SEPARATOR;
        $baseDir 	= getcwd() . $ds;

        if ($baseDir = XDT_CLI_Helper::locate('Application.php', array('XenForo', 'library/XenForo')))
        {
            self::$_baseDir = realpath(dirname($baseDir) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR;
        }
        else
        {
            echo chr(27) . '[1;31m' . 'A valid XenForo installation could not be found. Aborting.' . chr(27) . '[0m';
            echo "\n";
            die();
        }
    }

    /**
     * Get config from filesystem
     *
     * @return	Object
     */
    public static function getConfig()
    {
        $config = XenForo_Application::getConfig();
        $addonConfig = array(
            'addon_id' => $config->xdt->addon_id,
            'name' => $config->xdt->name,
            'namespace' => $config->xdt->namespace,
            'path' => $config->xdt->path,
            'addon_config' => $config->xdt->addon_config,

            'file_mask' => '0644',
            'dir_mask' => '0755',
        );

        return $addonConfig;

        if ( ! empty(self::$_config))
        {
            return self::$_config;
        }

        $ds = DIRECTORY_SEPARATOR;
        $up = '..' . $ds;

        $config = self::loadConfigJson(dirname(__FILE__) . $ds.$up.$up. '.xfcli-config');
        // TODO: ability to overwrite this with --addon-config=path option. Useful for one off changes to something
        $config = XDT_CLI_Helper::objectMerge($config, self::loadConfigJson(self::xfBaseDir() . '.xfcli-config'));
        if ( ! empty($config->addon_config))
        {
            $file = XDT_CLI_Helper::locate($config->addon_config, array('library'), null, array(self::xfBaseDir()));

            if ($file)
            {
                $config = XDT_CLI_Helper::objectMerge($config, self::loadConfigJson($file));
            }
        }

        return $config;
    }

    /**
     * Loads the JSON config from a file into an array which it returns
     *
     * @param  string $filepath
     *
     * @return array
     */
    public static function loadConfigJson($filepath)
    {
        if ( ! file_exists($filepath))
        {
            return (object) array();
        }

        $config = file_get_contents($filepath);
        $config = json_decode($config);

        if ( ! $config)
        {
            return (object) array();
        }

        return $config;
    }

    /**
     * Write config to file
     *
     * @param	object			$config
     * @param	null|string		$file
     *
     * @return	object
     */
    public static function writeConfig($config, $file = null)
    {
        if ( ! is_array($config) AND ! is_object($config))
        {
            return false;
        }

        if (empty($file))
        {
            $file = self::xfBaseDir() . DIRECTORY_SEPARATOR .'.xfcli-config';
        }

        $existingConfig = self::loadConfigJson($file);
        $config 		= XDT_CLI_Helper::objectMerge($existingConfig, $config);

        if (!XDT_CLI_Helper::writeToFile($file, XDT_CLI_Helper::jsonEncode($config)))
        {
            return false;
        }

        return $config;
    }

    /**
     * Write addon config to file
     *
     * @param	object			$config
     *
     * @return	object
     */
    public static function writeAddonConfig($config)
    {
        $folder = self::getConfig()->addon->folder;

        if (empty($folder))
        {
            return false;
        }

        $file = $folder . DIRECTORY_SEPARATOR . '.xfcli-config';

        return self::writeConfig($config, $file);
    }

}