<?php

if(!function_exists('http_redirect')) {
	function http_redirect ($target) {
		ob_end_clean();
		session_write_close();
		ob_start();
		header("Location: $target");
		ob_end_flush();
		exit();
	}
}

?>