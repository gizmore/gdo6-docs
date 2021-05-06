<?php
namespace GDO\Docs;

use GDO\Core\GDO_Module;
use GDO\File\FileUtil;
use GDO\DB\GDT_Checkbox;
use GDO\UI\GDT_Page;
use GDO\UI\GDT_Link;

/**
 * Generate docs using phpDocumentor.
 * Show generated docs website.
 * 
 * Currently you can generate the config via gdo6 and afterwards run a cli command.
 * 
 * @TODO make a bin/generate.sh
 * 
 * @author gizmore
 * @version 6.10.2
 * @since 6.10.0
 */
final class Module_Docs extends GDO_Module
{
    ##############
    ### Module ###
    ##############
    public $module_priority = 200;
    
    public function href_administrate_module() { return $this->href('Admin'); }
    public function onLoadLanguage() { return $this->loadLanguage('lang/docs'); }

    ###############
    ### Install ###
    ###############
    public function onInstall()
    {
        FileUtil::createDir($this->filePath('DOCS'));
    }

    ##############
    ### Config ###
    ##############
    public function getConfig()
    {
        return [
            GDT_Checkbox::make('bottom_bar')->initial('1'),
            GDT_Checkbox::make('ignore_disabled_modules')->initial('0'),
        ];
    }
    public function cfgBottomBar() { return $this->getConfigValue('bottom_bar'); }
    public function cfgIgnoreDisabledModules() { return $this->getConfigValue('ignore_disabled_modules'); }

    #############
    ### Hooks ###
    #############
    public function onInitSidebar()
    {
        if ($this->cfgBottomBar())
        {
            GDT_Page::$INSTANCE->bottomNav->addField(
                GDT_Link::make()->href($this->hrefDocs())->label('link_docs')
            );
        }
    }

    ############
    ### Docs ###
    ############
    public function hrefDocs() { return $this->wwwPath('DOCS'); }
    
}
