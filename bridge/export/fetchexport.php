<?php

	$extensionMgr = new ExtensionMgr();

	$extensionId = $this->request->parameters['extensionId'];
	$fileName = $this->request->parameters['fileName'];
	$mimeType = $this->request->parameters['mimeType'];
	$extensionInfo = $extensionMgr->get( $extensionId );

	if ($extensionInfo['CODE']) {
		$extension = $extensionMgr->getExtension($extensionInfo['CODE']);
		if ($extension) {
			$extension->fetchFile($fileName, $mimeType, $this->approot.$this->extensiondir);
		}
	}

?>