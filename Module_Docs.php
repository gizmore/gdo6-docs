<?php
namespace GDO\Docs;

use GDO\Core\GDO_Module;

/**
 * Generate docs using phpDocumentor.
 * Show generated docs website.
 * @author gizmore
 * @version 6.10
 * @since 6.10
 */
final class Module_Docs extends GDO_Module
{
    public function onLoadLanguage() { return $this->loadLanguage('lang/docs'); }

    public function href_administrate_module() { return $this->href('Admin'); }

}
