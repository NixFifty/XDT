<?php

class XDT_CLI_XenForo_Debug extends XDT_CLI_Abstract
{
    public function run()
    {
        $config = XenForo_Application::getConfig();
        $this->printDebug($config->xdt);
    }
}