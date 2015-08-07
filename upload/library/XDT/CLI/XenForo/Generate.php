<?php

class XDT_CLI_XenForo_Generate extends XDT_CLI_Abstract
{
    /**
     * Default run method
     *
     * @return	void
     */
    public function run()
    {
        $config = XDT_CLI_Application::getConfig();

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