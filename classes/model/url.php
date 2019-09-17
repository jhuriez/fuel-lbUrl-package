<?php

namespace LbUrl;

class Model_Url extends \Orm\Model
{

    protected static $_properties = array(
        'id',
        'id_url_master' => array(
            'form' => array('type' => false),
        ),
        'slug' => array(
            'label' => 'url_model_url.slug',
            'null' => false,
        ),
        'url_target' => array(
            'label' => 'url_model_url.url_target',
            'null' => false,
        ),
        'code' => array(
            'label' => 'url_model_url.code',
            'default' => '302',
            'null' => false,
            'form' => array('type' => 'select', 'value' => '302', 'options' => array('302' => 'url_model_url.302', '301' => 'url_model_url.301')),
        ),
        'method' => array(
            'label' => 'url_model_url.method',
            'default' => 'location',
            'null' => false,
            'form' => array('type' => 'select', 'value' => 'location', 'options' => array('location' => 'url_model_url.location', 'refresh' => 'url_model_url.refresh')),
        ),
        'description' => array(
            'label' => 'url_model_url.description',
            'form' => array('type' => 'textarea'),
        ),
        'active' => array(
            'label' => 'url_model_url.active',
            'default' => true,
            'null' => false,
            'form' => array('type' => 'select', 'value' => '0', 'options' => array('0' => 'url_model_url.no', '1' => 'url_model_url.yes')),
        ),
        'is_download' => array(
            'label' => 'url_model_url.is_download',
            'default' => false,
            'null' => false,
            'form' => array('type' => 'select', 'value' => '0', 'options' => array('0' => 'url_model_url.no', '1' => 'url_model_url.yes')),
        ),
        'hits' => array(
            'default' => 0,
            'null' => true,
            'form' => array('type' => false),
        ),
        'expired_at' => array(
            'label' => 'url_model_url.expired_at',
            'null' => true,
        ),
        'created_at' => array(
            'form' => array('type' => false),
        ),
        'updated_at' => array(
            'form' => array('type' => false),
        ),
    );
    protected static $_observers = array(
        'Orm\Observer_CreatedAt' => array(
            'events' => array('before_insert'),
            'mysql_timestamp' => false,
        ),
        'Orm\Observer_UpdatedAt' => array(
            'events' => array('before_update'),
            'mysql_timestamp' => false,
        ),
    );
    
    protected static $_table_name = 'url_url';
    
    public static function _init()
    {
        \Lang::load('url_model_url', true);
    }

    protected static $_has_many = array(
        'associated_urls' => array(
            'key_from' => 'id',
            'model_to' => 'LbUrl\Model_Url',
            'key_to' => 'id_url_master',
            'cascade_save' => true,
            'cascade_delete' => true,
        ),
    );

    protected static $_belongs_to = array(
        'url_master' => array(
            'key_from' => 'id_url_master',
            'model_to' => 'LbUrl\Model_Url',
            'key_to' => 'id',
            'cascade_save' => false,
            'cascade_delete' => false,
        )
    );

}
