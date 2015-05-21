<?php

/**
 * Newsletter_Form_Message
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Newsletter_Form_Message extends Admin_Form {
    
    public function init() {
        
        $id = $this->createElement('hidden', 'id');
        $id->setDecorators(array('ViewHelper'));
      
        $title = $this->createElement('text', 'title');
        $title->setLabel('Title');
        $title->setRequired();
        $title->setDecorators(self::$textDecorators);
        $title->setAttrib('class', 'span8');
        

        $content = $this->createElement('textarea', 'content');
        $content->setLabel('Content');
        $content->setDecorators(self::$tinymceDecorators);
        $content->setAttrib('class', 'span8 tinymce2');
        
        $type = $this->createElement('select', 'type');
        $type->setLabel('Type');
        $type->setDecorators(self::$selectDecorators);
        
        $allSubscribers = $this->createElement('checkbox', 'all_subscribers');
        $allSubscribers->setLabel('All subscribers');
        $allSubscribers->setDecorators(self::$checkgroupDecorators);
        $allSubscribers->setAttrib('class', 'span8');
        
        $subscriberId = $this->createElement('multiselect', 'subscriber_id');
        $subscriberId->setLabel('Subscribers');
        $subscriberId->setDecorators(self::$selectDecorators);
        $subscriberId->setAttrib('multiple', 'multiple');
        
        $groupId = $this->createElement('multiselect', 'group_id');
        $groupId->setLabel('Groups');
        $groupId->setDecorators(self::$selectDecorators);
        $groupId->setAttrib('multiple', 'multiple');
        
        $newsId = $this->createElement('multiselect', 'news_id');
        $newsId->setLabel('Choose news');
        $newsId->setDecorators(self::$selectDecorators);
        $newsId->setAttrib('multiple', 'multiple');
        
        $eventId = $this->createElement('multiselect', 'event_id');
        $eventId->setLabel('Choose events');
        $eventId->setDecorators(self::$selectDecorators);
        $eventId->setAttrib('multiple', 'multiple');
        
        $productId = $this->createElement('multiselect', 'product_id');
        $productId->setLabel('Choose new products or promotion products');
        $productId->setDecorators(self::$selectDecorators);
        $productId->setAttrib('multiple', 'multiple');
        
        $companyId = $this->createElement('multiselect', 'company_id');
        $companyId->setLabel('Choose companies');
        $companyId->setDecorators(self::$selectDecorators);
        $companyId->setAttrib('multiple', 'multiple');
        
        /*
        $sendDate = $this->createElement('text', 'send_date');
        $sendDate->setLabel('Send date');
        $sendDate->setDecorators(self::$datepickerDecorators);
        $sendDate->setAttrib('class', 'span8');
        */
        
        $submit = $this->createElement('button', 'submit');
        $submit->setLabel('Save');
        $submit->setDecorators(array('ViewHelper'));
        $submit->setAttribs(array('class' => 'btn btn-info', 'type' => 'submit'));

        $this->setElements(array(
            $title,
            $subscriberId,
            $content,
            $groupId,
            $allSubscribers,
            $id,
            $type,
          //  $newsId,
           // $eventId,
            $productId,
           // $companyId,
            $submit
        ));
        
    }
}

