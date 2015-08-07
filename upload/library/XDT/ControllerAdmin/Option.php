<?php

class XDT_ControllerAdmin_Option extends XFCP_XDT_ControllerAdmin_Option
{
    public function actionList()
    {
        $groupId = $this->_input->filterSingle('group_id', XenForo_Input::STRING);

        if ($groupId == 'xdt')
        {
            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                XenForo_Link::buildAdminLink('xdt/option')
            );
        }

        return parent::actionList();
    }

    public function actionXDTSave()
    {
        $this->_assertPostOnly();

        $input = $this->_input->filter(array(
            'group_id' => XenForo_Input::STRING,
            'options' => XenForo_Input::ARRAY_SIMPLE,
            'options_listed' => array(XenForo_Input::STRING, array('array' => true))
        ));

        foreach ($input['options_listed'] AS $optionName)
        {
            if (!isset($input['options'][$optionName]))
            {
                $input['options'][$optionName] = '';
            }
        }

        $optionModel = $this->_getOptionModel();
        $optionModel->updateOptions($input['options']);

        $group = $optionModel->getOptionGroupById($input['group_id']);

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $this->getDynamicRedirect(XenForo_Link::buildAdminLink('options/list', $group))
        );
    }
}