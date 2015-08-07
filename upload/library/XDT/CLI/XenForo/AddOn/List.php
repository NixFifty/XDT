<?php

class XDT_CLI_XenForo_Addon_List extends XDT_CLI_Abstract
{
    public function run()
    {
        /** @var XenForo_Model_AddOn $addOnModel */
        $addOnModel = XenForo_Model::create('XenForo_Model_AddOn');
        $addOns = $addOnModel->getAllAddOns();
        $tableAddOns = array();

        foreach ($addOns AS $addOn)
        {
            $tableAddOns[] = array(
                'Title' => $addOn['title'],
                'ID' => $addOn['addon_id'],
                'Version' => $addOn['version_string'],
                'Version ID' => $addOn['version_id'],
                'Status' => $addOn['active'] ?
                    $this->colorText('Enabled', self::LIGHT_GREEN) : $this->colorText('Disabled', self::RED)
            );
        }

        $this->printTable($tableAddOns);
    }
}
