<?php

/*

  Author: Ben Malen, <ben@conceptfactory.com.au>
  Co-Maintainer: Simon Radford, <simon@conceptfactory.com.au>
  Web: www.conceptfactory.com.au
  Co-Maintainer: Jonathan Schmid, <hi@jonathanschmid.de>

  ListIt is a CMS Made Simple module that enables the web developer to create
  multiple lists throughout a site. It can be duplicated and given friendly
  names for easier client maintenance.

  When duplicating this module change the 'ListIt' below to the new module
  name, e.g. NewName. Follow the CMSMS module naming conventions, a-z with no
  punctuation characters or spaces. Also change the name of this file to match
  - NewName.module.php. Finally change the name of the ListIt folder to the
  same NewName

 */

class dev4auctions extends ModuleGenerator {

    public function __construct() {
        spl_autoload_register(array(&$this, '_autoloader'));
        parent::__construct();
    }

    #---------------------
    # Internal autoloader
    #---------------------	

    private final function _autoloader($classname) {
        $config = cmsms()->GetConfig();
        $fn = cms_join_path($config['root_path'], 'modules', 'ModuleGenerator') . "/lib/class.{$classname}.php";
        if (file_exists($fn)) {
            require_once($fn);
        }
    }

    public function GetName() {
        return get_class($this);
    }

    function GetFriendlyName() {
        return $this->GetPreference('friendlyname');
    }

    public function GetVersion() {
        return parent::GetVersion();
    }

    public function GetHelp() {
        return parent::GetHelp();
    }

    public function GetAuthor() {
        return parent::GetAuthor();
    }

    public function GetAuthorEmail() {
        return parent::GetAuthorEmail();
    }

    public function GetChangeLog() {
        return parent::GetChangeLog();
    }

    public function SetParameters() {

        $this->InitializeAdmin();
        $this->InitializeFrontend();
    }

    public function AllowAutoUpgrade() {
        return TRUE;
    }

    public function InitializeAdmin() {

        // auto load for generator_opts
        $config = cmsms()->GetConfig();
        $generator_opts = cms_join_path($config['root_path'], 'modules', 'ModuleGenerator', 'lib', 'class.generator_opts.php');
        require_once($generator_opts);

        generator_opts::init_admin($this);
    }

    public function InitializeFrontend() {

        $config = cmsms()->GetConfig();
        $generator_opts = cms_join_path($config['root_path'], 'modules', 'ModuleGenerator', 'lib', 'class.generator_opts.php');
        require_once($generator_opts);

        generator_opts::init($this);
    }

    public function AllowSmartyCaching() {
        return TRUE;
    }

    public function LazyLoadFrontend() {
        return TRUE;
    }

    public function LazyLoadAdmin() {
        return TRUE;
    }

    public function GetEventDescription($eventname) {
        return parent::GetEventDescription($eventname);
    }

    public function IsPluginModule() {
        return parent::IsPluginModule();
    }

    /**
     * DoAction - default add default params
     * @param type $name
     * @param type $id
     * @param type $params
     * @param type $returnid 
     */
    public function DoAction($name, $id, $params, $returnid = '') {
        global $CMS_ADMIN_PAGE;
        $config = cmsms()->GetConfig();
        $smarty = cmsms()->GetSmarty();
        $db = cmsms()->GetDb();
        
        parent::DoAction($name, $id, $params, $returnid);

        switch ($name) {
            default:
                // fix 4 smarty security width templates folder
                if (isset($CMS_ADMIN_PAGE) && $CMS_ADMIN_PAGE == 1) {
                    $config = cmsms()->GetConfig();
                    $templatedir = GENERATOR_MODLIB_PATH;

                    $smarty = cmsms()->GetSmarty();
                    $smarty->setTemplateDir($templatedir);
                }

                //include framework 
                $filename = cms_join_path(GENERATOR_MODLIB_PATH, 'action.' . $name . '.php');
                if (@is_file($filename)) {
                    include($filename);
                    return;
                }
        }
    }

    public function HasAdmin() {
        return $this->GetPreference('has_admin', false);
    }

    public function GetAdminSection() {
        return 'content';
    }

    public function GetAdminDescription() {
        return parent::GetAdminDescription();
    }

    public function VisibleToAdminUser() {
        return $this->CheckPermission($this->_GetModuleAlias() . '_modify_item');
    }

    public function GetDependencies() {
        return parent::GetDependencies();
    }

    public function MinimumCMSVersion() {
        return parent::MinimumCMSVersion();
    }

    function InstallPostMessage() {
        return parent::InstallPostMessage();
    }

    function UninstallPostMessage() {
        return parent::UninstallPostMessage();
    }

    /**
     *  get module alias
     * @return type 
     */
    public function _GetModuleAlias() {
        $value = cms_utils::get_app_data(get_class() . __FUNCTION__);
        if ($value)
            return $value;

        $value = strtolower($this->GetName());
        cms_utils::set_app_data(get_class() . __FUNCTION__, $value);
        return $value;
    }

    public function GetHeaderHtml() {
        $module = cms_utils::get_module('ModuleGenerator');
        $output = '<script language="javascript" type="text/javascript" src="' . $module->GetModuleURLPath() . '/js/mColorPicker.min.js"></script>';

        if ($this->GetPreference('has_gallery')) {
            $output .= '<script type="text/javascript" src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script>';
            $plupload_libs = array('plupload.js', 'plupload.gears.js', 'plupload.silverlight.js', 'plupload.flash.js', 'plupload.browserplus.js', 'plupload.html4.js', 'plupload.html5.js');
            foreach ($plupload_libs as $plupload_lib) {
                $fn = $module->GetModuleURLPath() . '/js/plupload/' . $plupload_lib;
                $output .= '<script type="text/javascript" src="' . $fn . '"></script>' . "\n";
            }
            $output .= '<!-- <script type="text/javascript"  src="http://getfirebug.com/releases/lite/1.2/firebug-lite-compressed.js"></script> -->';
        }

        $fn = $module->GetModuleURLPath() . '/js/jquery.tablednd_0_5.js';
        $output .= '<script type="text/javascript" src="' . $fn . '"></script>' . "\n";

        return $output;
    }

    public function SearchResultWithParams($returnid, $item_id, $attr = '', $params = '') {
        if (!$this->GetPreference('searchable'))
            return;
        return generator_tools::get_search_result($this, $returnid, $item_id, $attr, $params);
    }

    public function SearchReindex($module) {
        if (!$this->GetPreference('searchable'))
            return;
        return generator_tools::search_reindex($this, $module);
    }

    public function CreateStaticRoutes() {

        // auto load for generator_opts
        $config = cmsms()->GetConfig();
        $generator_opts = cms_join_path($config['root_path'], 'modules', 'ModuleGenerator', 'lib', 'class.generator_opts.php');
        require_once($generator_opts);
        generator_opts::init_static_routes($this);
    }

    // install
    public function Install() {
        $config = cmsms()->GetConfig();
        $smarty = cmsms()->GetSmarty();
        $db = cmsms()->GetDb();

        $response = FALSE;

        $filename = GENERATOR_MODLIB_PATH . '/method.install.php';
        if (@is_file($filename)) {

            $res = include($filename);
            if ($res == 1 || $res == '') {
                $response = FALSE;
            } else {
                $response = $res;
            }
        }


        return $response;
    }

    public function Upgrade($oldversion, $newversion) {
        $config = cmsms()->GetConfig();
        $smarty = cmsms()->GetSmarty();
        $db = cmsms()->GetDb();

        $response = FALSE;

        $filename = GENERATOR_MODLIB_PATH . '/method.upgrade.php';
        if (@is_file($filename)) {

            $res = include($filename);
            if ($res == 1 || $res == '')
                $response = TRUE;
        }
        return $response;
    }

    public function Uninstall() {
        $config = cmsms()->GetConfig();
        $smarty = cmsms()->GetSmarty();
        $db = cmsms()->GetDb();

        $response = FALSE;

        $filename = GENERATOR_MODLIB_PATH . '/method.uninstall.php';
        if (@is_file($filename)) {

            $res = include($filename);
            if ($res == 1 || $res == '') {
                $response = FALSE;
            } else {
                $response = $res;
            }
        }

        return $response;
    }

}

?>