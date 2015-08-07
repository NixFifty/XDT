<?php

class XDT_CLI_XenForo_Addon_Build extends XDT_CLI_Abstract
{
    public function run()
    {
        $config = XDT_CLI_Application::getConfig();
        $this->printMessage($this->colorText('Active Add-on: ', XDT_CLI_Abstract::BROWN), false);
        // TODO: Not yet finished...
    }
}