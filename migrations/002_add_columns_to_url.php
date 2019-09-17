<?php

namespace Fuel\Migrations;

class Add_columns_to_url
{
	public function up()
	{
		try {
			\DBUtil::add_fields('url_url', array(
				'expired_at'     => array('constraint' => 11, 'type' => 'int', 'null' => true),
				'is_download'     => array('type' => 'boolean'),
			));
		} catch (\Database_Exception $e){
			\Messages::error('Une erreur est survenue lors de l\'insertion du champ');
		}
	}

	public function down (){
		try {
			\DBUtil::drop_fields('url_url', 'expired_at');
			\DBUtil::drop_fields('url_url', 'is_download');
		} catch (\Database_Exception $e){
			\Messages::error('Une erreur est survenue lors de la suppression du champ');
		}
	}
}