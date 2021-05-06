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
use GDO\Core\GDT_Module;
use GDO\Core\GDO_Module;
use GDO\Install\Installer;
use GDO\DB\GDT_Enum;
use GDO\DB\GDT_Checkbox;
use GDO\Docs\Module_Docs;

/**
 * Config generator for phpDocumentor.
 * 
 * @author gizmore
 * @version 6.10.2
 * @since 6.10.0
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
            GDT_Enum::make('visibility')->enumValues('private', 'protected', 'public')->initial('public'),
            GDT_Module::make('module')->emptyInitial('select_all_modules', ''),
            GDT_Checkbox::make('include_core')->initial('1'),
            GDT_Checkbox::make('include_dependencies')->initial('1'),
            GDT_AntiCSRF::make(),
        ]);
        $form->actions()->addField(GDT_Submit::make());
    }
    
    /**
     * @return GDO_Module
     */
    public function getSingleModule() { return $this->getForm()->getFormValue('module'); }
    
    public function includeCore() { return $this->getForm()->getParameterValue('include_core'); }
    public function includeDeps() { return $this->getForm()->getParameterValue('include_dependencies'); }
    
    public function formValidated(GDT_Form $form)
    {
        $this->generateAll();
    }
    
    public function generateIgnoreList()
    {
        # Build ignore list
        $ignore = IgnoreList::make('ignore');
        $pathes = IgnoreList::make('include');
        
        # Default list
        $ignore->data[] = 'GDO/*/bower_components/**/*';
        $ignore->data[] = 'GDO/*/node_modules/**/*';
        $ignore->data[] = 'GDO/*/3p/**/*';
        $ignore->data[] = 'GDO/*/Test/**/*';
        $ignore->data[] = 'protected/**/*';
        $ignore->data[] = 'vendor/**/*';
        $ignore->data[] = 'temp/**/*';
        $ignore->data[] = 'files/**/*';
        $ignore->data[] = 'files_test/**/*';
        
        # Ignore list hook
        GDT_Hook::callHook('IgnoreDocsFiles', $ignore);
        
        # Single module mode
        if ($singleModule = $this->getSingleModule())
        {
            if ($this->includeCore())
            {
                $core = $this->getSingleModule()->gdoDependencies();
                $core = array_map(function($moduleName){
                    return ModuleLoader::instance()->getModule($moduleName);}, $core);
            }
                
            if ($this->includeDeps())
            {
                # Single module and all it's dependencies.
                $all = Installer::getDependencyModules($singleModule->getName());
            }
            else
            {
                $all = [];
                if ($this->includeCore())
                {
                    $all = $core;
                }
                $all[] = $singleModule;
            }

            if ($this->includeCore())
            {
                $pathes->data[] = 'DOCS'; # DOCS in md of the gdo6 core 
                $pathes->data[] = 'gdo.php';
                $pathes->data[] = 'GDO6.php';
                $pathes->data[] = 'index.php';
                $pathes->data[] = 'install';
                foreach ($core as $path)
                {
                    $pathes->data[] = $path;
                }
            }
        }
        else # All modules
        {
            $all = ModuleLoader::instance()->loadModules(false, true);
            $pathes->data[] = '.*';
            $pathes->data[] = 'install/*';
            $pathes->data[] = 'install/**/*';
            $pathes->data[] = 'GDO/**/*';
            $pathes->data[] = 'DOCS/*';
        }
        
        # Ignore disabled modules
        $ignoring = Module_Docs::instance()->cfgIgnoreDisabledModules();
        foreach ($all as $module)
        {
            if ($singleModule)
            {
                $pathes->data[] = 'GDO/' . $module->getName();
            }
            elseif ($ignoring && !$module->isEnabled())
            {
                $ignore->data[] = 'GDO/' . $module->getName();
            }
        }

        return [$ignore, $pathes];
    }

    public function generateConfig()
    {
        list($ignore, $pathes) = $this->generateIgnoreList();
        
        # Write config
        $config = GDT_Template::php('Docs', 'phpdoc_config.xml', [
            'ignore' => $ignore->data,
            'pathes' => $pathes->data,
            'sourcePath' => GDO_PATH,
            'cachePath' => $this->getModule()->filePath('.DOCS_CACHE'),
            'outputPath' => $this->outputPath(),
            'visibility' => $this->getForm()->getFormVar('visibility'),
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
        $this->launchGenerator();
        
        return $this->message('msg_docs_generated');
    }
    
    public function launchGenerator()
    {
        # @TODO create a gdo6-proc module that handles async requests from website to proc with progress bar.
        # @TODO actually launch the generator
        # Use the following command to run the generation via CLI.
        # $path = php phpDocumentor.phar -c config.xml
        set_time_limit(60*60); # 1h
    }
    
}
