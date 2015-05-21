<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Version1 extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->dropTable('newsletter_sent');
        $this->dropTable('newsletter_subscriber_group');
        $this->addColumn('newsletter_subscriber', 'token', 'string', '255', array(
             ));
        $this->addColumn('newsletter_subscriber', 'active', 'boolean', '25', array(
             'default' => '1',
             ));
    }

    public function down()
    {
        $this->createTable('newsletter_sent', array(
             'id' => 
             array(
              'primary' => '1',
              'autoincrement' => '1',
              'type' => 'integer',
              'length' => '6',
             ),
             'message_id' => 
             array(
              'type' => 'integer',
              'length' => '4',
             ),
             'subscriber_id' => 
             array(
              'type' => 'integer',
              'length' => '4',
             ),
             'group_id' => 
             array(
              'type' => 'integer',
              'length' => '4',
             ),
             'send_at' => 
             array(
              'type' => 'timestamp',
              'length' => '25',
             ),
             'sent' => 
             array(
              'type' => 'boolean',
              'length' => '25',
             ),
             'created_at' => 
             array(
              'notnull' => '1',
              'type' => 'timestamp',
              'length' => '25',
             ),
             'updated_at' => 
             array(
              'notnull' => '1',
              'type' => 'timestamp',
              'length' => '25',
             ),
             'deleted_at' => 
             array(
              'notnull' => '',
              'type' => 'timestamp',
              'length' => '25',
             ),
             ), array(
             'type' => 'MyISAM',
             'indexes' => 
             array(
             ),
             'primary' => 
             array(
              0 => 'id',
             ),
             'collate' => 'utf8_general_ci',
             'charset' => 'utf8',
             ));
        $this->createTable('newsletter_subscriber_group', array(
             'subscriber_id' => 
             array(
              'primary' => '1',
              'type' => 'integer',
              'length' => '4',
             ),
             'group_id' => 
             array(
              'primary' => '1',
              'type' => 'integer',
              'length' => '4',
             ),
             ), array(
             'type' => 'MyISAM',
             'indexes' => 
             array(
             ),
             'primary' => 
             array(
              0 => 'subscriber_id',
              1 => 'group_id',
             ),
             'collate' => 'utf8_general_ci',
             'charset' => 'utf8',
             ));
        $this->removeColumn('newsletter_subscriber', 'token');
        $this->removeColumn('newsletter_subscriber', 'active');
    }
}