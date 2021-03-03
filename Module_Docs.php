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
 * @XXX The first and only module that needs gdo_post_install.sh
 * 
 * @author gizmore
 * @version 6.10
 * @since 6.10
 */
final class Module_Docs extends GDO_Module
{
    ##############
    ### Module ###
    ##############
    public $module_priority = 95;
    
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
        ];
    }
    public function cfgBottomBar() { return $this->getConfigValue('bottom_bar'); }

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
