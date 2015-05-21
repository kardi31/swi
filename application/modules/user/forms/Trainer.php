<?php

/**
 * User_Form_Trainer
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class User_Form_Trainer extends Admin_Form {
    
    public function init() {
        $i18nService = MF_Service_ServiceBroker::getInstance()->getService('Default_Service_I18n');
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
        
        $type = $this->createElement('hidden', 'type');
        $type->setDecorators(array('ViewHelper'));
        
        $parentId = $this->createElement('hidden', 'parent_id');
        $parentId->setDecorators(self::$hiddenDecorators);
        
        $type = $this->createElement('radio', 'type');
        $type->setLabel('Type');
        $type->setRequired(true);
      
        $languages = $i18nService->getLanguageList();

        $translations = new Zend_Form_SubForm();

        foreach($languages as $language) {
            $translationForm = new Zend_Form_SubForm();
            $translationForm->setName($language);
            $translationForm->setDecorators(array(
                'FormElements'
            ));

            $academicTitle = $translationForm->createElement('text', 'academic_title');
            $academicTitle->setBelongsTo($language);
            $academicTitle->setLabel('Academic title');
            $academicTitle->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
            $academicTitle->setAttrib('class', 'span5');
            
            $specialization = $translationForm->createElement('text', 'specialization');
            $specialization->setBelongsTo($language);
            $specialization->setLabel('Specialization');
            $specialization->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
            $specialization->setAttrib('class', 'span5');

            $education = $translationForm->createElement('textarea', 'education');
            $education->setBelongsTo($language);
            $education->setLabel('Education (completed school, courses, year of graduation)');
            $education->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
            $education->setAttrib('class', 'span8 tinymce');
            $education->setAttrib('rows', '5');
            
            $experience = $translationForm->createElement('textarea', 'experience');
            $experience->setBelongsTo($language);
            $experience->setLabel('Experience');
            $experience->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
            $experience->setAttrib('class', 'span8 tinymce');
            $experience->setAttrib('rows', '5');
            
            $curriculumVitae = $translationForm->createElement('textarea', 'curriculum_vitae');
            $curriculumVitae->setBelongsTo($language);
            $curriculumVitae->setLabel('Curriculum vitae');
            $curriculumVitae->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
            $curriculumVitae->setAttrib('class', 'span8 tinymce');
            $curriculumVitae->setAttrib('rows', '18');
            
            $translationForm->setElements(array(
                $academicTitle,
                $specialization,
                $education,
                $experience,
                $curriculumVitae
            ));

            $translations->addSubForm($translationForm, $language);
        }
        
        $this->addSubForm($translations, 'translations');

        $name = $this->createElement('text', 'name');
        $name->setLabel('Name');
        $name->setRequired();
        $name->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $name->setAttrib('class', 'span8');
        
        $placeOfAdmission = $this->createElement('textarea', 'place_of_admission');
        $placeOfAdmission->setLabel('Place of admission (full details)');
        $placeOfAdmission->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $placeOfAdmission->setAttrib('class', 'span8 tinymce');
        $placeOfAdmission->setAttrib('rows', '5');
        
        $contact = $this->createElement('text', 'contact');
        $contact->setLabel('Contact');
        $contact->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $contact->setAttrib('class', 'span8');
        
        $website = $this->createElement('text', 'website');
        $website->setLabel('Website');
        $website->setDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        $website->setAttrib('class', 'span8');
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $id,
            $parentId,
            $type,
            $name,
            $placeOfAdmission,
            $contact,
            $website,
            $submit
        ));
    }
}

