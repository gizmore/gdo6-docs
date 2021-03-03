<?php
namespace GDO\Docs\Method;

use GDO\Core\MethodAdmin;
use GDO\Form\GDT_Form;
use GDO\Form\MethodForm;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Submit;

/**
 * Admin page for Docs module.
 * @author gizmore
 */
final class Generate extends MethodForm
{
    use MethodAdmin;
    
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
        $this->onGenerateAll();
    }

    public function onGenerateAll()
    {
        return $this->error('msg_docs_generated');
    }
    
}
