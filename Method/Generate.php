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
            GDT_Enum::make('visibility')->enumValues('private', 'protected', 'public')->initial('public'),
            GDT_Module::make('module')->emptyInitial('select_entry', ''),
            GDT_AntiCSRF::make(),
        ]);
        $form->actions()->addFields([
            GDT_Submit::make(),
        ]);
    }
    
    /**
     * @return GDO_Module
     */
    public function getSingleModule() { return $this->getForm()->getFormValue('module'); }
    
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
        
        # Ignore list hook
        GDT_Hook::callHook('IgnoreDocsFiles', $ignore);
        
        if ($singleModule = $this->getSingleModule()) {
            $all = Installer::getDependencyModules($singleModule->getName());
            $pathes->data[] = 'GDO6.php';
            $pathes->data[] = 'DOCS';
            $pathes->data[] = 'install';
            $pathes->data[] = 'gdo.php';
            $pathes->data[] = 'index.php';
            $pathes->data[] = 'GDO/Classic';
            $pathes->data[] = 'GDO/Date';
            $pathes->data[] = 'GDO/DB';
            $pathes->data[] = 'GDO/File';
            $pathes->data[] = 'GDO/Form';
            $pathes->data[] = 'GDO/Install';
            $pathes->data[] = 'GDO/Mail';
            $pathes->data[] = 'GDO/Net';
            $pathes->data[] = 'GDO/UI';
            $pathes->data[] = 'GDO/Util';
        } else {
            $all = ModuleLoader::instance()->loadModules(false, true);
            $pathes->data[] = '.';
        }
        
        # Ignore disabled modules
        foreach ($all as $module)
        {
            if (!$module->isEnabled())
            {
                $ignore->data[] = 'GDO/' . $module->getName() . '/**/*';
            }
            if ($singleModule)
            {
                $pathes->data[] = 'GDO/' . $module->getName();
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
        # $path = php phpDocumentor.phar -c config.xml
        set_time_limit(60*60); # 1h
    }
    
}
