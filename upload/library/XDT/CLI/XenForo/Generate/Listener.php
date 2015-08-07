<?php

class XDT_CLI_XenForo_Generate_Listener extends XDT_CLI_Abstract
{
    /**
     * Default run method
     * TODO: Needs more work
     * @return	void
     */
    public function run()
    {
        $this->printMessage('Generating code event listener...');
        $config = XDT_CLI_Application::getConfig();

        $namespace = $config['namespace'] . '_';
        $className = $namespace . 'Listener';

        if (!$namespace)
        {
            $namespace = '';
        }

        if (!XDT_CLI_Helper::classExists($className, false))
        {
            //$extendName = 'XenForo_Controller' . XfCli_Helpers::camelcaseString($type, false) . '_Abstract';

            $class 	= new Zend_CodeGenerator_Php_Class();
            $class->setName($className);
            //$class->setExtendedClass($extendName);

            XDT_CLI_ClassGenerator::create($className, $class);

            $this->printMessage('ok');
        }
        else
        {
            $this->printMessage('skipped (already exists)');
        }

        $this->printMessage($namespace);

        if (!empty($config['name']))
        {
            $this->printMessage($this->colorText('Active Add-on: ', XDT_CLI_Abstract::BROWN), false);
            $this->printMessage($config['name']);
        }
        else
        {
            $this->printMessage($this->colorText('No add-on selected.', XDT_CLI_Abstract::RED));
        }
    }
}