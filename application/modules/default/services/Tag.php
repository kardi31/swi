<?php

/**
 * Default_Service_Tag
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Default_Service_Tag extends MF_Service_ServiceAbstract {
    
    protected $tagTable;
    
    public function init() {
        $this->tagTable = Doctrine_Core::getTable('Default_Model_Doctrine_Tag');
    }
    
    public function getTag($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        return $this->tagTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getI18nTag($name, $language, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->tagTable->getTagQuery($language);
        $q->andWhere('tt.name = ?', $name);
        return $q->fetchOne(array(), $hydrationMode);
    }
    
    public function fetchTag($name, $language) {
        if(!$tag = $this->getI18nTag($name, $language)) {
            $tag = $this->createTag($name, $language);
        }
        return $tag;
    }
    
    protected function createTag($name, $language) {
        $tag = $this->tagTable->getRecord();
        $tag->Translation[$language]->name = $name;
        $tag->Translation[$language]->slug = MF_Text::createUniqueTableSlug('Default_Model_Doctrine_TagTranslation', $name);
        $tag->save();
        return $tag;
    }
    
    public function saveTagsFromString($string, $language, $delimiter = ',') {
        $result = new Doctrine_Collection('Default_Model_Doctrine_Tag');
        $tagNames = array();
        $tmpTags = explode($delimiter, $string);
        foreach($tmpTags as $tag) {
            $tagNames[] = trim(strtolower($tag));
        }
        foreach($tagNames as $name) {
            $tag = $this->fetchTag($name, $language);
            $result->add($tag);
        }
        return $result;
    }
}

