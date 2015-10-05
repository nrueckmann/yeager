<?php

	\framework\import("org.yeager.ui.common");
	\framework\import("org.yeager.ui.koala");

	$data = json_decode( $this->request->parameters['data'], true );

	for ($i = 1; $i < count($data); $i++ ) {
		$elements[] = '"'.$data[$i]['id'].'"';
	}
	$elements = implode(', ', $elements);

	// Check if we get an Array of fields
	if( $data[1]['isArray'] ) {

		// Remove Flag from Array
		unset( $data[1]['isArray'] );

		foreach( $data[1] as $element ) {
			$field->id = $element['id'];
			if ($element['name']) {
				$field->name = $element['name'];
			}
			$field->value = ($element['value'])?($element['value']):(null);
			$field->type = ($element['yg_type'])?($element['yg_type']):(null);
			$field->property = ($element['yg_property'])?($element['yg_property']):(null);
			$field->yg_id = ($element['yg_id'])?($element['yg_id']):(null);

			$fields[$field->property] = $field;
			$field = null;
		}

		$this->fields = $fields;
		$data[1] = $data[1][0];
	}

	// Load code specific to ajaxaction mapped in configfile
	$this->rawdata = $data[1];

	$this->elements = $elements;
	$this->handler = $this->request->parameters['handler'];
	$action = $this->handler;

	// Check if user is authenticated and allowed to access the backend
	$tmpUser = new User(Singleton::UserMgr()->getCurrentUserID());
	$backendAllowed = $tmpUser->checkPermission('RBACKEND');
	if ( ((!$this->authenticated) || (!$backendAllowed)) &&
		 (($action != 'userLogin') && ($action != 'recoverLogin') && ($action != 'setNewPassword')) ) {
		$header = $_SERVER['SERVER_PROTOCOL'].' 403 Forbidden';
		header($header);
		echo $header;
		die();
	}

	$this->code = sConfig()->getVar('CONFIG/AJAXACTIONS/'.strtoupper($this->handler).'/CODE');
	$this->event = $data[0];

	$indexname = ($this->rawdata['yg_property'])?($this->rawdata['yg_property']):(null);

	$this->responsedata = array();

	$this->reponsedata[$indexname]->id = $this->rawdata['id'];
	if ($this->rawdata['name']) {
		$this->reponsedata[$indexname]->name = $this->rawdata['name'];
	}
	$this->reponsedata[$indexname]->value = (strlen($this->rawdata['value'])||is_array($this->rawdata['value']))?($this->rawdata['value']):(null);
	$this->reponsedata[$indexname]->type = ($this->rawdata['yg_type'])?($this->rawdata['yg_type']):(null);
	$this->reponsedata[$indexname]->property = ($this->rawdata['yg_property'])?($this->rawdata['yg_property']):(null);
	$this->reponsedata[$indexname]->yg_id = ($this->rawdata['yg_id'])?($this->rawdata['yg_id']):(null);
	$this->reponsedata[$indexname]->wid = ($this->rawdata['wid'])?($this->rawdata['wid']):(null);

	if ($this->fields) {
		$this->reponsedata = array_merge($this->reponsedata, $this->fields);
	}

	// NEW Parameter-API:
	$this->params = $this->rawdata['params'];

	$koala = new Koala();
	if ($this->code != '') {
		sLog()->error($action);

		// Create AJAX-Responder Object
		$koala->setResponderData($this->reponsedata);
		$koala->setResponderHandler($this->handler);
		require_once ($this->approot.$this->code);
		// Initiate sending of data
	} else {
		// Throw error when no matching code found...
		$koala->log( 'No matching method found for "'.strtoupper($this->handler).'"!' );
		$koala->log( 'XX $this->id: '.$this->id );
		$koala->log( 'XX $this->name: '.$this->name );
		$koala->log( 'XX $this->value: '.$this->value );
		$koala->log( 'XX $this->event: '.$this->event );
		$koala->log( 'XX $this->handler: '.$this->handler );
		$koala->log( 'XX $this->type: '.$this->type );
		$koala->log( 'XX $this->property: '.$this->property );
		$koala->log( 'XX $this->yg_id: '.$this->yg_id );
	}
	$koala->go();

	// Dump all JavaScript to Client...
	$smarty->assign('scriptoutput', $scriptoutput);

	$smarty->display('file:'.$this->page_template);

?>