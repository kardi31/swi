<?php

/**
 * Gallery_Model_Doctrine_VideoTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Gallery_Model_Doctrine_VideoTable extends Doctrine_Table
{
    /**
     * Returns an instance of this class.
     *
     * @return object Gallery_Model_Doctrine_VideoTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Gallery_Model_Doctrine_Video');
    }
}