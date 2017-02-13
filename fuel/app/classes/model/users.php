<?php

class Model_users extends Orm\Model
{
    protected static $_table_name = 'usuarios';
    protected static $_primary_key = array('id');
    protected static $_properties = array(
        'id', // both validation & typing observers will ignore the PK

        'username' => array(
            'data_type' => 'varchar',
            'validation' => array('required'),
            
        ),

        'password' => array(
            'data_type' => 'varchar',
            'validation' => array('required')
            
        ),

        'email' => array(
            'data_type' => 'varchar',
            'validation' => array('required')
            
        ),

        'foto' => array(
            'data_type' => 'varchar',
            'validation' => array('required')

        ),
        
    );

}