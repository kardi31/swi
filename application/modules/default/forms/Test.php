<?php

/**
 * Default_Form_Test
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Default_Form_Test extends Zend_Form {
    
    public static $radioDecorators = array(
        'ViewHelper',
        array(array('ElementWrapper' => 'HtmlTag'), array('tag' => 'ul')),
        array('Label', array('tag' => 'li')),
        array(array('RowWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-row row-fluid'))
    );
    
    public function init() {
        
        $physique = $this->createElement('radio', 'physique');
        $physique->setLabel('Physique');
        $physique->setRequired(true);
        $physique->setDecorators(self::$radioDecorators);
        $physique->setSeparator('');
        
        $head = $this->createElement('radio', 'head');
        $head->setLabel('Head');
        $head->setRequired(true);
        $head->setDecorators(self::$radioDecorators);
        $head->setSeparator('');
        
        $forehead = $this->createElement('radio', 'forehead');
        $forehead->setLabel('Forehead');
        $forehead->setRequired(true);
        $forehead->setDecorators(self::$radioDecorators);
        $forehead->setSeparator('');
        
        $eyeBrowsEyeLashes = $this->createElement('radio', 'eyebrows_eyelashes');
        $eyeBrowsEyeLashes->setLabel('Eyebrows/eyelashes');
        $eyeBrowsEyeLashes->setRequired(true);
        $eyeBrowsEyeLashes->setDecorators(self::$radioDecorators);
        $eyeBrowsEyeLashes->setSeparator('');
        
        $eyes = $this->createElement('radio', 'eyes');
        $eyes->setLabel('Eyes');
        $eyes->setRequired(true);
        $eyes->setDecorators(self::$radioDecorators);
        $eyes->setSeparator('');
        
        $nose = $this->createElement('radio', 'nose');
        $nose->setLabel('Nose');
        $nose->setRequired(true);
        $nose->setDecorators(self::$radioDecorators);
        $nose->setSeparator('');
        
        $mouth = $this->createElement('radio', 'mouth');
        $mouth->setLabel('Mouth');
        $mouth->setRequired(true);
        $mouth->setDecorators(self::$radioDecorators);
        $mouth->setSeparator('');
        
        $teeth = $this->createElement('radio', 'teeth');
        $teeth->setLabel('Teeth');
        $teeth->setRequired(true);
        $teeth->setDecorators(self::$radioDecorators);
        $teeth->setSeparator('');
        
        $hair = $this->createElement('radio', 'hair');
        $hair->setLabel('Hair');
        $hair->setRequired(true);
        $hair->setDecorators(self::$radioDecorators);
        $hair->setSeparator('');
        
        $neck = $this->createElement('radio', 'neck');
        $neck->setLabel('Neck');
        $neck->setRequired(true);
        $neck->setDecorators(self::$radioDecorators);
        $neck->setSeparator('');
        
        $shoulders = $this->createElement('radio', 'shoulders');
        $shoulders->setLabel('Shoulders');
        $shoulders->setRequired(true);
        $shoulders->setDecorators(self::$radioDecorators);
        $shoulders->setSeparator('');
        
        $breast = $this->createElement('radio', 'breast');
        $breast->setLabel('Breast');
        $breast->setRequired(true);
        $breast->setDecorators(self::$radioDecorators);
        $breast->setSeparator('');
        
        $loins = $this->createElement('radio', 'loins');
        $loins->setLabel('Loins');
        $loins->setRequired(true);
        $loins->setDecorators(self::$radioDecorators);
        $loins->setSeparator('');
        
        $handsFeet = $this->createElement('radio', 'hands_feet');
        $handsFeet->setLabel('Hands and feet');
        $handsFeet->setRequired(true);
        $handsFeet->setDecorators(self::$radioDecorators);
        $handsFeet->setSeparator('');
        
        $nails = $this->createElement('radio', 'nails');
        $nails->setLabel('Nails');
        $nails->setRequired(true);
        $nails->setDecorators(self::$radioDecorators);
        $nails->setSeparator('');
        
        $joint = $this->createElement('radio', 'joint');
        $joint->setLabel('Joint');
        $joint->setRequired(true);
        $joint->setDecorators(self::$radioDecorators);
        $joint->setSeparator('');
        
        $skin = $this->createElement('radio', 'skin');
        $skin->setLabel('Skin');
        $skin->setRequired(true);
        $skin->setDecorators(self::$radioDecorators);
        $skin->setSeparator('');
        
        $appetite = $this->createElement('radio', 'appetite');
        $appetite->setLabel('Appetite');
        $appetite->setRequired(true);
        $appetite->setDecorators(self::$radioDecorators);
        $appetite->setSeparator('');
        
        $flavorsDishes = $this->createElement('radio', 'flavors_dishes');
        $flavorsDishes->setLabel('Popular flavors and dishes');
        $flavorsDishes->setRequired(true);
        $flavorsDishes->setDecorators(self::$radioDecorators);
        $flavorsDishes->setSeparator('');
        
        $desire = $this->createElement('radio', 'desire');
        $desire->setLabel('Desire');
        $desire->setRequired(true);
        $desire->setDecorators(self::$radioDecorators);
        $desire->setSeparator('');
        
        $feces = $this->createElement('radio', 'feces');
        $feces->setLabel('Feces');
        $feces->setRequired(true);
        $feces->setDecorators(self::$radioDecorators);
        $feces->setSeparator('');
        
        $sexDrive = $this->createElement('radio', 'sex_drive');
        $sexDrive->setLabel('Sex drive');
        $sexDrive->setRequired(true);
        $sexDrive->setDecorators(self::$radioDecorators);
        $sexDrive->setSeparator('');
        
        $activities = $this->createElement('radio', 'activities');
        $activities->setLabel('Activities');
        $activities->setRequired(true);
        $activities->setDecorators(self::$radioDecorators);
        $activities->setSeparator(''); 
        
        $voiceSpeaking = $this->createElement('radio', 'voice_speaking');
        $voiceSpeaking->setLabel('Voice / manner of speaking');
        $voiceSpeaking->setRequired(true);
        $voiceSpeaking->setDecorators(self::$radioDecorators);
        $voiceSpeaking->setSeparator('');
        
        $dream = $this->createElement('radio', 'dream');
        $dream->setLabel('Dream');
        $dream->setRequired(true);
        $dream->setDecorators(self::$radioDecorators);
        $dream->setSeparator('');
        
        $dreams = $this->createElement('radio', 'dreams');
        $dreams->setLabel('Dreams');
        $dreams->setRequired(true);
        $dreams->setDecorators(self::$radioDecorators);
        $dreams->setSeparator('');
        
        $emotionalState = $this->createElement('radio', 'emotional_state');
        $emotionalState->setLabel('Emotional state');
        $emotionalState->setRequired(true);
        $emotionalState->setDecorators(self::$radioDecorators);
        $emotionalState->setSeparator('');
        
        $memoryConcentration = $this->createElement('radio', 'memory_concentration');
        $memoryConcentration->setLabel('Memory / concentration');
        $memoryConcentration->setRequired(true);
        $memoryConcentration->setDecorators(self::$radioDecorators);
        $memoryConcentration->setSeparator('');
        
        $reasoning = $this->createElement('radio', 'reasoning');
        $reasoning->setLabel('Reasoning');
        $reasoning->setRequired(true);
        $reasoning->setDecorators(self::$radioDecorators);
        $reasoning->setSeparator('');
        
        $decisionMaking = $this->createElement('radio', 'decision_making');
        $decisionMaking->setLabel('Decision-making');
        $decisionMaking->setRequired(true);
        $decisionMaking->setDecorators(self::$radioDecorators);
        $decisionMaking->setSeparator('');
        
        $spendingMoney = $this->createElement('radio', 'spending_money');
        $spendingMoney->setLabel('Spending money');
        $spendingMoney->setRequired(true);
        $spendingMoney->setDecorators(self::$radioDecorators);
        $spendingMoney->setSeparator('');
        
        $stressfulSituations = $this->createElement('radio', 'stressful_situations');
        $stressfulSituations->setLabel('Stressful situations');
        $stressfulSituations->setRequired(true);
        $stressfulSituations->setDecorators(self::$radioDecorators);
        $stressfulSituations->setSeparator('');
        
        $disease = $this->createElement('radio', 'disease');
        $disease->setLabel('When the disease');
        $disease->setRequired(true);
        $disease->setDecorators(self::$radioDecorators);
        $disease->setSeparator('');
        
        $relations = $this->createElement('radio', 'relations');
        $relations->setLabel('Relations with other');
        $relations->setRequired(true);
        $relations->setDecorators(self::$radioDecorators);
        $relations->setSeparator('');
        
        $lifestyle = $this->createElement('radio', 'lifestyle');
        $lifestyle->setLabel('Lifestyle');
        $lifestyle->setRequired(true);
        $lifestyle->setDecorators(self::$radioDecorators);
        $lifestyle->setSeparator('');
        
        $wayOfThinking = $this->createElement('radio', 'way_of_thinking');
        $wayOfThinking->setLabel('Way of thinking');
        $wayOfThinking->setRequired(true);
        $wayOfThinking->setDecorators(self::$radioDecorators);
        $wayOfThinking->setSeparator('');
        
        $sensitivity = $this->createElement('radio', 'sensitivity');
        $sensitivity->setLabel('Sensitivity');
        $sensitivity->setRequired(true);
        $sensitivity->setDecorators(self::$radioDecorators);
        $sensitivity->setSeparator('');
        
        $submit = $this->createElement('submit', 'submit');
        $submit->setLabel('Check');
        $submit->setDecorators(array('ViewHelper'));
        
        $this->setElements(array(
            $submit,
            $physique,
            $forehead,
            $head,
            $eyeBrowsEyeLashes,
            $eyes,
            $nose,
            $mouth,
            $teeth,
            $hair,
            $neck,
            $shoulders,
            $breast,
            $loins,
            $handsFeet,
            $nails,
            $joint,
            $skin,
            $appetite,
            $flavorsDishes,
            $desire,
            $feces,
            $sexDrive,
            $activities,
            $voiceSpeaking,
            $dream,
            $dreams,
            $emotionalState,
            $memoryConcentration,
            $reasoning,
            $decisionMaking,
            $spendingMoney,
            $stressfulSituations,
            $disease,
            $relations,
            $lifestyle,
            $wayOfThinking,
            $sensitivity
            
        ));
    }
}

