// General onMouseEnter/onMouseLeave implementation (only for non-IE browsers)
if (!Prototype.Browser.IE) {
	Element.addMethods({
		/**
		* Simulates onmousenter for non IE browsers.
		* @type Element
		* @param { Element } [element] The element which will be observed
		* @param { Function } [observer] The function which will be called when
		* the event is fired
		* @addon Element
		* @function
		*/
	    onmouseenter: function(element,observer) {
	        element = $(element);
	        element.observe('mouseover',function(evt,currentTarget) {
	            var relatedTarget = $(evt.relatedTarget || evt.fromElement);
	            if( relatedTarget!=currentTarget && relatedTarget.childOf(currentTarget)==false ) {
	                observer();
	            } 
	        }.bindAsEventListener({},element));
	        return element;
	    },
		/**
		* Simulates onmousleave for non IE browsers.
		* @type Element
		* @param { Element } [element] The element which will be observed
		* @param { Function } [observer] The function which will be called when
		* the event is fired
		* @addon Element
		* @function
		*/
	    onmouseleave: function(element,observer) {
	        element = $(element);
	        element.observe('mouseout',function(evt,currentTarget) {
	            var relatedTarget = $(evt.relatedTarget || evt.toElement);
	            if( relatedTarget!=currentTarget && relatedTarget.childOf(currentTarget)==false ) {
	                observer();
	            } 
	        }.bindAsEventListener({},element));
	        return element;
	    }
	});
}


// Extension to Ajax allowing for classes of requests of which only one (the latest) is ever active at a time
// - stops queues of now-redundant requests building up / allows you to supercede one request with another easily.
// just pass in onlyLatestOfClass: 'classname' in the options of the request

Ajax.currentRequests = {};

Ajax.Responders.register({
	onCreate: function(request) {
		if (request.options.onlyLatestOfClass && Ajax.currentRequests[request.options.onlyLatestOfClass]) {
			// if a request of this class is already in progress, attempt to abort it before launching this new request
			$K.log( 'Trying to cancel current Request... ('+request.options.onlyLatestOfClass+')', $K.Log.DEBUG );
			try {
				Ajax.currentRequests[request.options.onlyLatestOfClass].transport.aborted = true;
				Ajax.currentRequests[request.options.onlyLatestOfClass].abort();
				Ajax.activeRequestCount--;
			} catch(e) {}
		}
		// keep note of this request object so we can cancel it if superceded
		Ajax.currentRequests[request.options.onlyLatestOfClass] = request;
	},
	onComplete: function(request) {
		if (request.options.onlyLatestOfClass) {
			// remove the request from our cache once completed so it can be garbage collected
			Ajax.currentRequests[request.options.onlyLatestOfClass] = null;
		}
	}
});

Ajax.Request.prototype.abort = function() {
    this.transport.onreadystatechange = Prototype.emptyFunction;
    this.transport.abort();
	// prevent different behaviour in Safari
	if (!(/Konqueror|Safari|KHTML/.test(navigator.userAgent))) { Ajax.activeRequestCount--; }
};



// Extension for IFrames
// 		(-> http://groups.google.com/group/prototype-core/browse_thread/thread/3202de9de5cbf682?pli=1)
// 		(-> http://www.ruby-forum.com/topic/145701)
Element.addMethods('iframe', {
	document: function(element) {
		element = $(element);
		var retElement;
		if (element.contentWindow) {
			retElement = element.contentWindow.document;
		} else if (element.contentDocument) {
			retElement = element.contentDocument;
		} else {
			retElement = null;
		}

		/*
		if (!element.contentWindow.Prototype) {
			// Get current prototype.js (& prototype_patch.js) script path
			var scriptElementSrc = new Array();
			// document.firstChild.nextSibling.firstChild
			$(document.body).up().down('head').select('script').each(function(scriptItem){
				if ( scriptItem.src.endsWith('prototype.js') || scriptItem.src.endsWith('xprototype_patch.js') ) {
					scriptElementSrc.push(scriptItem.src);
				}
			});
			
			$K.log(scriptElementSrc, $K.Log.INFO);
			
			// Load prototype.js (& prototype_patch.js) into IFrame
			scriptElementSrc.each(function(scriptElementSrcItem){
				var dirty = true;
				if (!dirty) {
					var prototypeScriptElement = retElement.createElement('script');
					prototypeScriptElement.type = 'text/javascript';
					prototypeScriptElement.src = scriptElementSrcItem;
					retElement.firstChild.nextSibling.firstChild.appendChild( prototypeScriptElement );
					$K.log( prototypeScriptElement, $K.Log.INFO );
				} else {
					

					$K.log( 'H', retElement.firstChild.nextSibling, $K.Log.INFO );

					$K.log( 'Y', retElement.firstChild.nextSibling.firstChild.nextSibling, $K.Log.INFO );
					
					//<body id="tinymce" class="mceContentBody" spellcheck="false" dir="ltr">
					
					//retElement.body.innerHTML += '<script type="text/javascript" src="'+scriptElementSrcItem+'"><\/script>';
					
					//var oldDocument = '<body id="tinymce" class="mceContentBody" spellcheck="false" dir="ltr">' + retElement.firstChild.nextSibling.firstChild.nextSibling.innerHTML + '</body>';
					var oldDocument = retElement.firstChild.nextSibling.innerHTML;
					
					$K.log( oldDocument, $K.Log.INFO );

					retElement.write('<script type="text/javascript" src="'+scriptElementSrcItem+'"><\/script>');
					retElement.write( oldDocument );
					
				}
			});
		}
		*/
		
		// Load prototype.js into the IFrame
		/*
		var t = this, d = t.doc;
		if (!u)
			u = '';

		each(u.split(','), function(u) {
			if (t.files[u])
				return;
			t.files[u] = true;
			t.add(t.select('head')[0], 'link', {rel : 'stylesheet', href : tinymce._addVer(u)});
		});
		*/
		return retElement;
	},
	$: function(element, frameElement) {
		element = $(element);
		if (typeof element.document == 'function') {
			var frameDocument = element.document();
		} else {
			var frameDocument = element.document;
		}
		if (arguments.length > 2) {
			for (var i = 1, frameElements = [], length = arguments.length; i < length; i++)
				frameElements.push(element.$(arguments[i]));
			return frameElements;
		}
		if (Object.isString(frameElement))
			frameElement = frameDocument.getElementById(frameElement);
		return frameElement || element;
	}
});
