<?php

/**
 * XenForo Command Line Interface class
 */
class XDT_CLI_XenForo extends XDT_CLI_Abstract
{
    // TODO: Add more info here or something. Correct tabbing too.
	protected $_help = '
        Xen Development Tools (XDT) by NixFifty.
        Based on code from Robbo and Naatan.'
    ;

	/**
	 * Default run method
	 * 
	 * @return	void							
	 */
	public function run()
	{
		$class = 'CLI_' . ucfirst(strtolower($this->getArgumentAt(0)));

		// If a sub command is called, attempt to forward the call
		// this is to allow addon developers to create CLI command handlers
		// inside their XF install
		if ($class != __CLASS__ AND class_exists($class))
		{
			$arguments = $this->getArguments();
			array_shift($arguments);
			
			$callStructure = $this->_callStructure;
			$callStructure[] = $this;

			new $class($class, $arguments, $this->getFlags(), $this->getOptions(), $callStructure);
		}
		else
		{
			$this->showHelp();
			$this->printEmptyLine();
		}
		
	}

	public function runVersion()
	{
		$this->printMessage(sprintf(
			"Current XenForo version: %s (%s)",
			XenForo_Application::$versionId, XenForo_Application::$version
		), 2);
	}

    public function runJQueryVersion()
    {
        $this->printMessage(sprintf(
            "Current XenForo jQuery version: %s ",
            XenForo_Application::$jQueryVersion
        ), 2);
    }

	public function initialize()
	{
		parent::initialize();

		$this->loadConfig();
	}

	/**
	 * Loads the global and add-on (if applicable) flags and options from the configs
	 * 
	 * @return void 
	 */
	protected function loadConfig()
	{
		$config = XenForo_Application::getConfig();

		// We set any flags and options from the config, if already set it has priority so skip
		foreach ($config AS $option => $value)
		{
			if ($option == 'flags')
			{
				foreach ($value as $flag)
				{
					if (!$this->hasFlag($flag))
					{
						$this->setFlag($flag);
					}
				}

				continue;
			}

			if (!$this->hasOption($option))
			{
				$this->setOption($option, $value);
			}
		}
	}

	/**
	 * Loads the JSON config from a file into an array which it returns
	 * 
	 * @param  string $filepath 
	 * @return array           
	 */
	protected function loadConfigJson($filepath)
	{
		$config = file_get_contents($filepath);
		$config = json_decode($config, true);
		
		if ($config === null)
		{
			return array();
		}

		return $config;
	}
}