<?php
namespace GDO\Docs;

use GDO\Core\GDT_Array;

/**
 * An ignore list starts as empty array.
 * @author gizmore
 */
final class IgnoreList extends GDT_Array
{
    protected function __construct()
    {
        parent::__construct();
        $this->data = [];
    }
    
}
