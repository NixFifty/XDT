<?php

class XDT_CLI_XenForo_Addon_Disable extends XDT_CLI_Abstract
{
    public function run()
    {
        $addOnId = $this->getOption('addon-id');
        $config = XDT_CLI_Application::getConfig();

        if (!$addOnId)
        {
            if (!$config OR empty($config['addon_id']))
            {
                $this->bail('There is no addon selected and the --addon-id is not set');
            }

            $addOnId = $config['addon_id'];
        }

        $dw = XenForo_DataWriter::create('XenForo_DataWriter_AddOn');
        $dw->setExistingData($addOnId);
        $dw->set('active', 0);
        $dw->save();
        $this->printInfo($this->colorText($addOnId . ' was disabled successfully.', self::LIGHT_GREEN));
    }
}