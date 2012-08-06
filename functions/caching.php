<?php 

class PLS_Cache {

	public $type = 'general';
	public $transient_id = false;

	function __construct ($type = 'general') {
		$this->$type = $type;
	}

	function get () {
		if (defined('WP_DEBUG')) {
			return false;
		}
		$arg_hash = md5(http_build_query(func_get_args()));
		$this->transient_id = 'pl_' . $this->type . '_' . $arg_hash;
        $transient = get_transient($this->transient_id);
        if ($transient) {
        	return $transient;
        } else {
        	return false;
        }
	}

	public function save ($result, $duration = 172800) {
		if ($this->transient_id) {
			set_transient($this->transient_id, $result , $duration);
		}
	}

//end class
}