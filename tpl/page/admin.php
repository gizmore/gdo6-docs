<?php
use GDO\UI\GDT_Bar;
use GDO\UI\GDT_Link;

$bar = GDT_Bar::make();

$bar->addFields([
    GDT_Link::make('generate')->href(href('Docs', 'Generate')),
]);

echo $bar->render();
