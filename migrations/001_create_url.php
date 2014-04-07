<?php

namespace Fuel\Migrations;

class Create_url
{
	public function up()
	{
		\DBUtil::create_table('url_url', array(
			'id'            => array('constraint' => 11, 'type' => 'int', 'auto_increment' => true, 'unsigned' => true),
			'id_url_master' => array('constraint' => 11, 'type' => 'int', 'null' => true),
			'slug'          => array('constraint' => 255, 'type' => 'varchar'),
			'url_target'    => array('constraint' => 255, 'type' => 'varchar'),
			'code'          => array('constraint' => 255, 'type' => 'varchar'),
			'method'        => array('constraint' => 255, 'type' => 'varchar'),
			'description'   => array('type' => 'text', 'null' => true),
			'active'        => array('type' => 'boolean'),
			'hits'          => array('type' => 'int', 'null' => true),
			'created_at'    => array('constraint' => 11, 'type' => 'int', 'null' => true),
			'updated_at'    => array('constraint' => 11, 'type' => 'int', 'null' => true),
		), array('id'));
	}

	public function down()
	{
		\DBUtil::drop_table('url_url');
	}
}