<?php

class XDT_Listener
{
    public static $addExternal = false;

    public static function loadAddOnController($class, array &$extend)
    {
        if ($class == 'XenForo_ControllerAdmin_AddOn')
        {
            $extend[] = 'XDT_ControllerAdmin_AddOn';
        }
    }

    public static function loadOptionController($class, array &$extend)
    {
        if ($class == 'XenForo_ControllerAdmin_Option')
        {
            $extend[] = 'XDT_ControllerAdmin_Option';
        }
    }

    public static function loadPhraseController($class, array &$extend)
    {
        if ($class == 'XenForo_ControllerAdmin_Phrase')
        {
            $extend[] = 'XDT_ControllerAdmin_Phrase';
        }
    }

    public static function loadToolsController($class, array &$extend)
    {
        if ($class == 'XenForo_ControllerAdmin_Tools')
        {
            $extend[] = 'XDT_ControllerAdmin_Tools';
        }
    }

    public static function loadAddOnModel($class, array &$extend)
    {
        if ($class == 'XenForo_Model_AddOn')
        {
            $extend[] = 'XDT_Model_AddOn';
        }
    }

    public static function loadCodeEventListenerController($class, array &$extend)
    {
        if ($class == 'XenForo_ControllerAdmin_CodeEventListener')
        {
            $extend[] = 'XDT_ControllerAdmin_CodeEventListener';
        }
    }

    /**
     * Listen for post render effects so we can append externals to the header in the
     * PAGE_CONTAINER template
     *
     * @param    string $templateName
     * @param    string $content
     * @param    array                        array
     * @param    XenForo_Template_Abstract $template
     *
     * @return    void
     *
     */
    public static function template_post_render($templateName, &$content, array &$containerData, XenForo_Template_Abstract $template)
    {
        if (self::$addExternal AND $templateName == 'PAGE_CONTAINER')
        {
            $params = $template->getParams();
            $baseUrl = $params['requestPaths']['fullBasePath'];

            $addonModel = new XenForo_Model_AddOn;
            $version = $addonModel->getAddOnVersion('XDT');
            $version = $version['version_id'];

            $options = XenForo_Application::get('options');

            $append = '<link rel="stylesheet" href="' . $baseUrl . 'js/xdt/codemirror/lib/codemirror.css?v=' . $version . '">' . "\n";
            $append .= '<link rel="stylesheet" href="' . $baseUrl . 'js/xdt/codemirror/lib/util/dialog.css?v=' . $version . '">' . "\n";
            $append .= '<link rel="stylesheet" href="' . $baseUrl . 'admin.php?_css/&css=templatesyntax&v=' . $version . '">' . "\n";

            if ($options->xdtCmTheme != 'default' AND !empty($options->xdtCmTheme))
            {
                $append .= '<link rel="stylesheet" href="' . $baseUrl . 'js/xdt/codemirror/theme/' . $options->xdtCmTheme . '.css?v=' . $version . '">' . "\n";
            }

            $append .= '<script src="' . $baseUrl . 'js/xdt/codemirror/lib/codemirror.js?v=' . $version . '"></script>' . "\n";
            $append .= '<script src="' . $baseUrl . 'js/xdt/templatesyntax/templatesyntax.js?v=' . $version . '"></script>' . "\n";
            $append .= '<script src="' . $baseUrl . 'js/xdt/templatesyntax/zen.min.js?v=' . $version . '"></script>' . "\n";

            $append .= '<script src="' . $baseUrl . 'js/xdt/codemirror/mode/xml/xml.js?v=' . $version . '"></script>' . "\n";
            $append .= '<script src="' . $baseUrl . 'js/xdt/codemirror/mode/javascript/javascript.js?v=' . $version . '"></script>' . "\n";
            $append .= '<script src="' . $baseUrl . 'js/xdt/codemirror/mode/css/css.js?v=' . $version . '"></script>' . "\n";
            $append .= '<script src="' . $baseUrl . 'js/xdt/codemirror/mode/htmlmixed/htmlmixed.js?v=' . $version . '"></script>' . "\n";

            $append .= '<script src="' . $baseUrl . 'js/xdt/codemirror/lib/util/formatting.js?v=' . $version . '"></script>' . "\n";
            $append .= '<script src="' . $baseUrl . 'js/xdt/codemirror/lib/util/search.js?v=' . $version . '"></script>' . "\n";
            $append .= '<script src="' . $baseUrl . 'js/xdt/codemirror/lib/util/searchcursor.js?v=' . $version . '"></script>' . "\n";
            $append .= '<script src="' . $baseUrl . 'js/xdt/codemirror/lib/util/dialog.js?v=' . $version . '"></script>' . "\n";

            if ($options->xdtCmKeyMap != 'default' AND !empty($options->xdtCmKeyMap))
            {
                $append .= '<script src="' . $baseUrl . 'js/xdt/codemirror/keymap/' . $options->xdtCmKeyMap . '.js?v=' . $version . '"></script>' . "\n";
            }

            if (isset($options->xdtCmFeatures['matchBrackets']))
            {
                $append .= '<script src="' . $baseUrl . 'js/xdt/codemirror/lib/util/closetag.js?v=' . $version . '"></script>' . "\n";
            }

            if (isset($options->xdtCmFeatures['foldCode']))
            {
                $append .= '<script src="' . $baseUrl . 'js/xdt/codemirror/lib/util/foldcode.js?v=' . $version . '"></script>' . "\n";
            }

            $config = array(
                'features' => $options->xdtCmFeatures,
                'keymap' => $options->xdtCmKeyMap,
                'tabSize' => $options->xdtCmTabSize,
                'theme' => $options->xdtCmTheme,
                'keybinding' => array(
                    'maximize' => $options->xdtCmMaximizeKeybinding,
                    'save' => $options->xdtCmSaveKeybinding,
                    'zen' => $options->xdtCmZenKeyBinding,
                    'format' => $options->xdtCmFormatKeyBinding,
                )
            );

            $append .= '<script>tsConfig = ' . json_encode($config) . ';</script>' . "\n";

            $content = str_replace('</head>', $append . '</head>', $content);

            self::$addExternal = false;
        }

    }

    /**
     * Listen for controller pre dispatch events so we can detect when the externals
     * are required
     *
     * @param    XenForo_Controller $controller
     * @param    string $action
     *
     * @return    void
     *
     */
    public static function controller_pre_dispatch(XenForo_Controller $controller, $action)
    {
        if ((($controller instanceof XenForo_ControllerAdmin_AdminTemplate OR $controller instanceof XenForo_ControllerAdmin_Template OR
                    $controller instanceof XenForo_ControllerAdmin_Style) AND in_array($action, array('Add', 'Edit'))
            ) OR (class_exists('TMS_ControllerAdmin_Modification', false) AND $controller instanceof TMS_ControllerAdmin_Modification)
            //OR ($controller instanceof XenForo_ControllerAdmin_TemplateModification)
        )
        {
            self::$addExternal = true;
        }
    }

    public static function front_controller_pre_view(XenForo_FrontController $fc, XenForo_ControllerResponse_Abstract &$controllerResponse, XenForo_ViewRenderer_Abstract &$viewRenderer, array &$containerParams)
    {
        if (defined('TS_DISABLE'))
        {
            self::$addExternal = false;
        }
    }
}