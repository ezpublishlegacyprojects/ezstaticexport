<?php

abstract class eZStaticExportAbstractExport extends eZPersistentObject 
{
	protected $db;
	
	public function __construct($row = array())
	{
		parent::eZPersistentObject( $row );
		$this->db = eZDB::instance();
	}
    
}