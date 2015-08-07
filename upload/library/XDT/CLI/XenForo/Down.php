<?php

class XDT_CLI_XenForo_Down extends XDT_CLI_Abstract
{
    protected $_help = '
        Closes the board down to only administrators.

        Usage:
          down
          down -h | --help

        Options:
          -h --help     Show this screen.
    ';

    public function run()
    {
        $dw = XenForo_DataWriter::create('XenForo_DataWriter_Option');
        $dw->setExistingData('boardActive', true);
        $dw->setOption(XenForo_DataWriter_Option::OPTION_REBUILD_CACHE, true);
        $dw->set('option_value', '0');
        $dw->save();

        $this->printInfo($this->colorText(
            'The board is no longer active.', self::BROWN
        ));
    }
}