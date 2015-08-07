<?php

class XDT_ControllerAdmin_XDT extends XenForo_ControllerAdmin_Abstract
{
    public function actionOption()
    {
        $optionModel = $this->_getOptionModel();

        $fetchOptions = array('join' => XenForo_Model_Option::FETCH_ADDON);

        $group = $this->_getOptionGroupOrError('xdt', $fetchOptions);
        $groups = $optionModel->getOptionGroupList($fetchOptions);
        $options = $optionModel->getOptionsInGroup($group['group_id'], $fetchOptions);

        $canEdit = $optionModel->canEditOptionAndGroupDefinitions();

        $viewParams = array(
            'group' => $group,
            'groups' => $optionModel->prepareOptionGroups($groups, false),
            'preparedOptions' => $optionModel->prepareOptions($options, false),
            'canEditGroup' => $canEdit,
            'canEditOptionDefinition' => $canEdit,
        );

        return $this->responseView('XenForo_ViewAdmin_Option_ListOptions', 'xdt_option_list', $viewParams);
    }

    protected function _getOptionGroupOrError($groupId, array $fetchOptions = array())
    {
        $info = $this->_getOptionModel()->getOptionGroupById($groupId, $fetchOptions);

        if (!$info)
        {
            throw $this->responseException($this->responseError(new XenForo_Phrase('requested_option_group_not_found'), 404));
        }

        if (!empty($fetchOptions['join']) && $fetchOptions['join'] & XenForo_Model_Option::FETCH_ADDON)
        {
            if ($this->_getAddOnModel()->isAddOnDisabled($info))
            {
                throw $this->responseException($this->responseError(
                   new XenForo_Phrase('option_group_belongs_to_disabled_addon', array(
                       'addon' => $info['addon_title'],
                       'link' => XenForo_Link::buildAdminLink('add-ons', $info)
                   ))
                ));
            }
        }

        return $this->_getOptionModel()->prepareOptionGroup($info);
    }

    protected function _getOptionOrError($optionId)
    {
        $info = $this->_getOptionModel()->getOptionById($optionId);

        if ($info)
        {
            throw $this->responseException($this->responseError(new XenForo_Phrase('requested_option_not_found'), 404));
        }
    }

    /**
     * Lazy load the option model.
     *
     * @return XenForo_Model_Option
     */
    protected function _getOptionModel()
    {
        return $this->getModelFromCache('XenForo_Model_Option');
    }

    /**
     * Lazy load the add-on model.
     *
     * @return XenForo_Model_AddOn
     */
    protected function _getAddOnModel()
    {
        return $this->getModelFromCache('XenForo_Model_AddOn');
    }
}