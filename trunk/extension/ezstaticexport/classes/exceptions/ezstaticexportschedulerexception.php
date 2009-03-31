<?php

class eZStaticExportSchedulerException extends Exception
{
	// Scheduler
	const EXPORT_NOT_DEFINED = -1;
	const UNSUPPORTED_METHOD = -2;
	
	private $cli;
	
	public function __construct($message, $code=0)
	{
		$this->cli = eZCLI::instance();
		
		parent::__construct($message, $code);
	}
	
	public function __toString()
	{
		$this->cli->error((string) $this);
		return __CLASS__ . ": [$this->code] : $this->message\n";
	}
}