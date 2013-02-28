<?php
class Error {
	
	const MODEL_GET_NONE = 4000;
	const MODEL_GET_NOID = 4001;
	
	const HOST_ACTIVATE_NOCODES = 1001;
	const HOST_ACTIVATE_EMPTYCODE = 1002;
	const HOST_ACTIVATE_EXISTINGACTIVE = 1003;
	const HOST_ACTIVATE_NOHOST = 1004;
	const HOST_ACTIVATE_EXPIREDCODE = 1005;
	const HOST_DELETE_NOID = 1006;
	const HOST_DELETE_EMPTYID = 1007;
	const HOST_DELETE_NOHOST = 1008;
	const HOST_RECREATE_EMPTYID = 1009;
	const HOST_RECREATE_NOID = 1010;
	const HOST_RECREATE_NOHOST = 1011;
	const HOST_RECREATE_EXISTINGACTIVE = 1012;
	const HOST_CREATE_NOID = 1013;
	const HOST_CREATE_EMPTYID = 1014;
	const HOST_CREATE_EXISTINGID = 1015;
	const HOST_GET_NOHOSTS = 1016;
	const HOST_GET_NOID = 1017;
	
	const METRIC_GET_NOMETRIC = 2001;
	const METRIC_CREATE_NODATA = 2002;
	
	const DEFAULT_ERROR = 9999;
	
	public static $message = array(
		self::HOST_ACTIVATE_NOCODES => "No host codes were sent to activate",
		self::HOST_ACTIVATE_EMPTYCODE => "A host activation code is empty",
		self::HOST_ACTIVATE_EXISTINGACTIVE => "Attempt to activate an existing active host",
		self::HOST_ACTIVATE_NOHOST => "A host code supplied doesn't exist",
		self::HOST_ACTIVATE_EXPIREDCODE => "A host code supplied has expired",
		self::HOST_DELETE_NOID => "No host ID's to delete",
		self::HOST_DELETE_EMPTYID => "A host ID for delete is empty",
		self::HOST_DELETE_NOHOST => "A host doesn't exist so can't be deleted",
		self::HOST_RECREATE_EMPTYID => "A Host ID is empty",
		self::HOST_RECREATE_NOID => "No host ID's to recreate",
		self::HOST_RECREATE_NOHOST => "A supplied host doesn't exist so can't be recreated",
		self::HOST_RECREATE_EXISTINGACTIVE => "A supploed host has already been activated",
		self::HOST_CREATE_NOID => "Missing host ID",
		self::HOST_CREATE_EMPTYID => "A supplied host ID is empty",
		self::HOST_CREATE_EXISTINGID => "A supplied host ID already exists",
		self::HOST_GET_NOID => "You must specify a host ID to lookup",
		self::HOST_GET_NOHOSTS => "No hosts were found matching the requested ID's",
		
		self::METRIC_GET_NOMETRIC => "No metrics with that name, or no metric provided",
		self::METRIC_CREATE_NODATA => "Could not create the metric cause no data was provided",
			
		self::DEFAULT_ERROR => "An error has occured",
	);
	
	/**
	 * Returns the error message associated with the code
	 * 
	 * @param unknown_type $code
	 * @return multitype:string
	 */
	static function Message($code) {
		if (array_key_exists($code, self::$message)) {
			return self::$message[$code];
		}
		else {
			return self::$message[9999];
		}
	}
}