<?php

class XDT_CLI_XenForo_Version extends XDT_CLI_Abstract
{
    public function run()
    {
        $this->printMessage(sprintf(
            "Current XenForo version: %s (%s)",
            XenForo_Application::$versionId, XenForo_Application::$version
        ), 2);
    }
}