<?php

/**
 * Product_Model_Doctrine_BaseProductSet
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $product_id
 * @property array $set_products
 * @property Product_Model_Doctrine_Product $Product
 * 
 * @package    Admi
 * @subpackage Product
 * @author     Michał Folga <michalfolga@gmail.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Product_Model_Doctrine_BaseProductSet extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('product_product_set');
        $this->hasColumn('product_id', 'integer', 4, array(
             'primary' => true,
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('set_products', 'array', 10000, array(
             'type' => 'array',
             'length' => '10000',
             ));

        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_general_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Product_Model_Doctrine_Product as Product', array(
             'local' => 'product_id',
             'foreign' => 'id'));
    }
}