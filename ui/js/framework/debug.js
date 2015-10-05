/** 
 * @fileoverview Provides functionality to enable/disable logging
 * @version 1.0
 */

Koala.mapLoggerFunctions = function() {
	var chkLoglevel = function( param ) {
		// Check last entry of array for Loglevel
		var lastParam = param[param.length-1];
		if (lastParam) {
			var isLoglevel;
			for (var item in $K.Log) {
				if (lastParam == $K.Log[item]) isLoglevel = true;
			}
			if (isLoglevel && (lastParam <= $K.loglevel))
				return true;
		}
		return false;
	};
	if ( $K.devMode && window.console && window.console.firebug ) {
		// firebug 1.8
		Koala.debug			= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.debug.apply(this, arguments); } }catch(ex){} };
		Koala.log			= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.log.apply(this, arguments); } }catch(ex){} };
		Koala.info			= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.info.apply(this, arguments); } }catch(ex){} };
		Koala.warn			= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.warn.apply(this, arguments); } }catch(ex){} };
		Koala.error			= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.error.apply(this, arguments); } }catch(ex){} };
		Koala.assert		= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.assert.apply(this, arguments); } }catch(ex){} };
		Koala.dir			= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.dir.apply(this, arguments); } }catch(ex){} };
		Koala.dirxml		= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.dirxml.apply(this, arguments); } }catch(ex){} };
		Koala.trace			= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.trace.apply(this, arguments); } }catch(ex){} };
		Koala.group			= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.group.apply(this, arguments); } }catch(ex){} };
		Koala.groupEnd		= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.groupEnd.apply(this, arguments); } }catch(ex){} };
		Koala.time			= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.time.apply(this, arguments); } }catch(ex){} };
		Koala.timeEnd		= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.timeEnd.apply(this, arguments); } }catch(ex){} };
		Koala.profile		= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.profile.apply(this, arguments); } }catch(ex){} };
		Koala.profileEnd	= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.profileEnd.apply(this, arguments); } }catch(ex){} };
		Koala.count			= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.count.apply(this, arguments); } }catch(ex){} };
	} else if ( $K.devMode && (!(window.console && window.console.firebug && (firebug && firebug.d.console)) || (window.console && !window.console.firebug)) ) {
		// firebug lite
		Koala.debug			= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.debug.apply(this, arguments); } }catch(ex){} };
		Koala.log			= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.log.apply(this, arguments); } }catch(ex){} };
		Koala.info			= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.info.apply(this, arguments); } }catch(ex){} };
		Koala.warn			= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.warn.apply(this, arguments); } }catch(ex){} };
		Koala.error			= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.error.apply(this, arguments); } }catch(ex){} };
		Koala.assert		= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.assert.apply(this, arguments); } }catch(ex){} };
		Koala.dir			= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.dir.apply(this, arguments); } }catch(ex){} };
		Koala.dirxml		= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.dirxml.apply(this, arguments); } }catch(ex){} };
		Koala.trace			= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.trace.apply(this, arguments); } }catch(ex){} };
		Koala.group			= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.group.apply(this, arguments); } }catch(ex){} };
		Koala.groupEnd		= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.groupEnd.apply(this, arguments); } }catch(ex){} };
		Koala.time			= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.time.apply(this, arguments); } }catch(ex){} };
		Koala.timeEnd		= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.timeEnd.apply(this, arguments); } }catch(ex){} };
		Koala.profile		= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.profile.apply(this, arguments); } }catch(ex){} };
		Koala.profileEnd	= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.profileEnd.apply(this, arguments); } }catch(ex){} };
		Koala.count			= function() { try { if (chkLoglevel(arguments)) { Array.prototype.pop.call(arguments); window.console.count.apply(this, arguments); } }catch(ex){} };
	} else {
		Koala.debug			= function() { };
		Koala.log			= function() { };
		Koala.info			= function() { };
		Koala.warn			= function() { };
		Koala.error			= function() { };
		Koala.assert		= function() { };
		Koala.dir			= function() { };
		Koala.dirxml		= function() { };
		Koala.trace			= function() { };
		Koala.group			= function() { };
		Koala.groupEnd		= function() { };
		Koala.time			= function() { };
		Koala.timeEnd		= function() { };
		Koala.profile		= function() { };
		Koala.profileEnd	= function() { };
		Koala.count			= function() { };
	}
	
}

if (!$K.devMode) {
	// prevent all js errors
	if (!(window.console)) {
		console = new Object();
		console.log = function() {
			return false;
		}
		console.warn = function() {
			return false;
		}
	}
	window.onerror = function(msg, url, line) {
		$K.error('Javascript Error: ' + msg + ' -- Location: ' + url + ' -- Line: ' + line, $K.Log.INFO);
		return true;
	}
}

Koala.mapLoggerFunctions();
