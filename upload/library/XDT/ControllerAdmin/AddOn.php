<?php

class XDT_ControllerAdmin_AddOn extends XFCP_XDT_ControllerAdmin_AddOn
{
    public function actionBuild()
    {
        $addOnModel = $this->_getAddOnModel();
        $addOnId = $this->_input->filterSingle('addon_id', XenForo_Input::STRING);
        $addOn = $this->_getAddOnOrError($addOnId);

        $xenOptions = XenForo_Application::getOptions();
        $buildLocation = $xenOptions->xdtDefaultBuildLocation;
        $addOn['addon_file_path'] = preg_replace('/[_]/', '/', $addOn['addon_id']);

        $addOn['addon_id_library'] = $addOn['addon_file_path'];
        $addOn['addon_id_js'] = strtolower($addOn['addon_file_path']);
        $addOn['addon_id_style'] = 'default/' . strtolower($addOn['addon_file_path']);

        $addOn['libraryDirExists'] = is_dir('library/' . $addOn['addon_id_library']);
        $addOn['jsDirExists'] = is_dir('js/' . $addOn['addon_id_js']);
        $addOn['styleDirExists'] = is_dir('styles/' . $addOn['addon_id_style']);

        $addOn['install_xml'] = 1;
        $addOn['zip_check'] = 1;

        $viewParams = array(
            'addOn' => $addOn,
            'buildLocation' => $buildLocation,
            'archiveName' => $addOnId,
            'canAccessDevelopment' => $addOnModel->canAccessAddOnDevelopmentAreas()
        );

        return $this->responseView(
            'XDT_ViewAdmin_AddOn_Build',
            'xdt_addon_build',
            $viewParams
        );
    }

    public function actionBuilder()
    {
        $this->_assertPostOnly();

        $addOnId = $this->_input->filterSingle('addon_id', XenForo_Input::STRING);
        $addOn = $this->_getAddOnOrError($addOnId);
        $archiveName = trim($this->_input->filterSingle('archive_name', XenForo_Input::STRING));

        $addOnModel = $this->_getAddOnModel();

        $input = $this->_input->filter(array(
            'version_increment_check' => XenForo_Input::UINT,
            'increment_method' => XenForo_Input::STRING,
            'increment_custom_version' => XenForo_Input::UINT,
            'library_check' => XenForo_Input::UINT,
            'js_check' => XenForo_Input::UINT,
            'styles_check' => XenForo_Input::UINT,
            'extra_check' => XenForo_Input::UINT,
            'build_location' => XenForo_Input::STRING,
            'library_dir' => XenForo_Input::STRING,
            'js_dir' => XenForo_Input::STRING,
            'styles_dir' => XenForo_Input::STRING,
            'extra_dirs' => XenForo_Input::STRING,
            'install_xml' => XenForo_Input::UINT,
            'zip_check' => XenForo_Input::UINT,
        ));

        if ($input['library_check'])
        {
            $source = 'library/' . $input['library_dir'];
            $destination = $input['build_location'] . $addOnId . '/upload/library/' . $input['library_dir'];

            $copyFiles = $addOnModel->addOnBuilderCopyFiles($source, $destination);

            if (!$copyFiles)
            {
                return $this->responseError(new XenForo_Phrase('could_not_copy_files_did_you_specify_correct_dir'));
            }
        }

        if ($input['js_check'])
        {
            $source = 'js/' . $input['js_dir'];
            $destination = $input['build_location'] . $addOnId . '/upload/js/' . $input['js_dir'];

            $copyFiles = $addOnModel->addOnBuilderCopyFiles($source, $destination);

            if (!$copyFiles)
            {
                return $this->responseError(new XenForo_Phrase('could_not_copy_files_did_you_specify_correct_dir'));
            }
        }

        if ($input['styles_check'])
        {
            $source = 'styles/' . $input['styles_dir'];
            $destination = $input['build_location'] . $addOnId . '/upload/styles/' . $input['styles_dir'];

            $copyFiles = $addOnModel->addOnBuilderCopyFiles($source, $destination);

            if (!$copyFiles)
            {
                return $this->responseError(new XenForo_Phrase('could_not_copy_files_did_you_specify_correct_dir'));
            }
        }

        if ($input['extra_check'])
        {
            $extraDirs = preg_split("/\r\n|\n|\r/", $input['extra_dirs']);

            foreach ($extraDirs AS $key => $dir)
            {
                $destination = $input['build_location'] . $addOnId . '/upload/' . $dir;
                $copyFiles = $addOnModel->xcopy($dir, $destination);
                if (!$copyFiles)
                {
                    return $this->responseError(new XenForo_Phrase('could_not_copy_files_did_you_specify_correct_dir'));
                }
            }
        }

        $destXml = $input['build_location'] . $addOnId . '/addon-' . $addOnId . '.xml';

        $xmlObject = $this->_getAddOnModel()->getAddOnXml($addOn);
        $xmlObject->save($destXml);

        if ($input['install_xml'])
        {
            $destination = $input['build_location'] . $addOnId . '/upload/install/data';
            $addOnModel->createDir($destination);
            $destXml = $destination . '/addon-' . $addOnId . '.xml';
            $xmlObject->save($destXml);
        }

        if ($input['zip_check'])
        {
            require_once 'library/XDT/Helper/RecurseZip.php';

            $zip = new recurseZip();

            $source = $input['build_location'] . $addOnId . '/upload';
            $destination = $input['build_location'] . $addOnId;

            $zip->compress($source, $destination, $input['build_location'], $archiveName . '-' . $addOn['version_string']);
        }

        if ($input['version_increment_check'])
        {
            switch ($input['increment_method'])
            {
                case 'custom':
                    $addOnDw = XenForo_DataWriter::create('XenForo_DataWriter_AddOn');
                    $addOnDw->setExistingData($addOn);
                    $addOnDw->set('version_id', $input['increment_custom_version']);
                    $addOnDw->save();
                    break;
                case 'auto':
                default:
                    $addOnDw = XenForo_DataWriter::create('XenForo_DataWriter_AddOn');
                    $addOnDw->setExistingData($addOn);
                    $addOnDw->set('version_id', $addOn['version_id'] + 1);
                    $addOnDw->save();
            }
        }

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildAdminLink('add-ons') . $this->getLastHash($addOnId),
            new XenForo_Phrase('xdt_your_addon_has_been_built_successfully')
        );
    }

    public function actionBuilderPurge()
    {
        $addOnId = $this->_input->filterSingle('addon_id', XenForo_Input::STRING);
        $addOn = $this->_getAddOnOrError($addOnId);

        if ($this->isConfirmedPost())
        {
            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                XenForo_Link::buildAdminLink('add-ons') . $this->getLastHash($addOnId),
                new XenForo_Phrase('xdt_previous_builds_have_been_purged')
            );
        }
        else
        {
            $viewParams = array(
                'addon' => $addOn
            );

            return $this->responseView(
                'XDT_ViewAdmin_AddOn_Build_Purge',
                'xdt_build_purge',
                $viewParams
            );
        }
    }

    /**
     * Gets the add-on model object.
     *
     * @return XenForo_Model_AddOn
     */
    protected function _getAddOnModel()
    {
        return $this->getModelFromCache('XenForo_Model_AddOn');
    }
}