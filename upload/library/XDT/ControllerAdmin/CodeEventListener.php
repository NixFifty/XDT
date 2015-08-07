<?php

class XDT_ControllerAdmin_CodeEventListener extends XFCP_XDT_ControllerAdmin_CodeEventListener
{
    public function actionSave()
    {
        $response = parent::actionSave();

        $eventListenerId = $this->_input->filterSingle('event_listener_id', XenForo_Input::UINT);
        $dwInput = $this->_input->filter(array(
            'event_id' => XenForo_Input::STRING,
            'description' => XenForo_Input::STRING,
            'hint' => XenForo_Input::STRING
        ));

        if (empty($dwInput['description']))
        {
            // TODO: Phrase this stuff
            switch($dwInput['event_id'])
            {
                case 'load_class_controller':
                    $description = 'Listens for the '.$dwInput['hint'].' class.';
                    break;
                default:
                    $description = XenForo_Application::getOptions()->xdtDefaultEventListenerDesc;
            }

            $dw = XenForo_DataWriter::create('XenForo_DataWriter_CodeEventListener');
            if ($eventListenerId)
            {
                $dw->setExistingData($eventListenerId);
            }
            $dw->set('description', $description);
            $dw->save();
        }

        return $response;
    }
}