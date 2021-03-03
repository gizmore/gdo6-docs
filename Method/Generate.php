<?php
namespace GDO\Docs\Method;

use GDO\Core\MethodAdmin;
use GDO\Form\GDT_Form;
use GDO\Form\MethodForm;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Submit;
use GDO\Core\GDT_Hook;
use GDO\Docs\IgnoreList;
use GDO\Core\GDT_Template;
use GDO\Core\ModuleLoader;

/**
 * Admin page for Docs module.
 * @author gizmore
 */
final class Generate extends MethodForm
{
    use MethodAdmin;
    
    public function isTrivial() { return false; }
    
    public function outputPath()
    {
        return $this->getModule()->filePath('DOCS');
    }
    
    public function configFilename()
    {
        $configFilename = $this->getModule()->filePath('config.xml');
        return $configFilename;
    }
    
    public function createForm(GDT_Form $form)
    {
        $form->addFields([
            GDT_AntiCSRF::make(),
        ]);
        $form->actions()->addFields([
            GDT_Submit::make(),
        ]);
    }
    
    public function formValidated(GDT_Form $form)
    {
        $this->generateAll();
    }
    
    public function generateIgnoreList()
    {
        # Build ignore list
        $ignore = IgnoreList::make();
        
        # Default list
        $ignore->data[] = 'GDO/*/bower_components/**/*';
        $ignore->data[] = 'GDO/*/node_modules/**/*';
        $ignore->data[] = 'GDO/*/3p/**/*';
        $ignore->data[] = 'GDO/*/Test/**/*';
        $ignore->data[] = 'protected/**/*';
        $ignore->data[] = 'vendor/**/*';
        $ignore->data[] = 'temp/**/*';
        $ignore->data[] = 'files/**/*';
        
        # Ignore list hook
        GDT_Hook::callHook('IgnoreDocsFiles', $ignore);
        
        # Ignore disabled modules
        $all = ModuleLoader::instance()->loadModules(false, true);
        foreach ($all as $module)
        {
            if (!$module->isEnabled())
            {
                $ignore->data[] = 'GDO/' . $module->getName() . '/**/*';
            }
        }
        
        return $ignore;
    }

    public function generateConfig()
    {
        $ignore = $this->generateIgnoreList();
        
        # Write config
        $config = GDT_Template::php('Docs', 'phpdoc_config.xml', [
            'ignore' => $ignore->data,
            'sourcePath' => GDO_PATH,
            'outputPath' => $this->outputPath(),
        ]);
        file_put_contents($this->configFilename(), $config);
        
        # Ignored
        return $config;
    }
    
    public function generateAll()
    {
        # 1. Generate config
        $this->generateConfig();
        
        # Launch generator
        
        return $this->message('msg_docs_generated');
    }
    
    public function launchGenerator()
    {
        set_time_limit(60*60); # 1h
        
    }
    
}
