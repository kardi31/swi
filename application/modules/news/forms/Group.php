<?php

/**
 * News_Form_Group
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class News_Form_Group extends Admin_Form {
    
    public function init() {
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
  
         $title = $this->createElement('text', 'title');
        $title->setLabel('Title');
        $title->setDecorators(self::$textDecorators);
        $title->setAttrib('class', 'span8');

        $content = $this->createElement('textarea', 'content');
        $content->setLabel('Content');
        $content->setDecorators(self::$tinymceDecorators);
        $content->setAttrib('class', 'span8 tinymce');


        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttrib('type', 'submit');
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $id,
            $title,
            $content,
            $submit
        ));
    }
}

