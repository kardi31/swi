<?php

/**
 * Invoice_Model_Doctrine_BaseInvoice
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $code
 * @property integer $user_id
 * @property decimal $sum
 * @property timestamp $execution_start_date
 * @property timestamp $execution_end_date
 * @property Doctrine_Collection $Items
 * @property Invoice_Model_Doctrine_Payment $Payment
 * 
 * @package    Admi
 * @subpackage Invoice
 * @author     Michał Folga <michalfolga@gmail.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Invoice_Model_Doctrine_BaseInvoice extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('invoice_invoice');
        $this->hasColumn('id', 'integer', 4, array(
             'primary' => true,
             'autoincrement' => true,
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('code', 'string', 128, array(
             'type' => 'string',
             'length' => '128',
             ));
        $this->hasColumn('user_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('sum', 'decimal', null, array(
             'type' => 'decimal',
             'scale' => 2,
             ));
        $this->hasColumn('execution_start_date', 'timestamp', null, array(
             'type' => 'timestamp',
             ));
        $this->hasColumn('execution_end_date', 'timestamp', null, array(
             'type' => 'timestamp',
             ));

        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_general_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('Invoice_Model_Doctrine_Item as Items', array(
             'local' => 'id',
             'foreign' => 'invoice_id'));

        $this->hasOne('Invoice_Model_Doctrine_Payment as Payment', array(
             'local' => 'id',
             'foreign' => 'invoice_id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}