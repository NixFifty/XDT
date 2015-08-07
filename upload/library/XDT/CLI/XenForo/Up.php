<?php

class XDT_CLI_XenForo_Up extends XDT_CLI_Abstract
{
    protected $_help = '
        Enables an inactive board.

        Usage:
          up
          up -h | --help

        Options:
          -h --help     Show this screen.
    ';

    public function run()
    {
        $dw = XenForo_DataWriter::create('XenForo_DataWriter_Option');
        $dw->setExistingData('boardActive', true);
        $dw->setOption(XenForo_DataWriter_Option::OPTION_REBUILD_CACHE, true);
        $dw->set('option_value', '1');
        $dw->save();

        $this->printInfo($this->colorText(
            'The board is now active.', self::LIGHT_GREEN
        ));
    }
}