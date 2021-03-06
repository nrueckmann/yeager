/**
* nlstreeext_dd.js v2.3
* To use with NlsTree Professional only.
* Copyright 2005-2006, addObject.com. All Rights Reserved
* Author Jack Hermanto, www.addobject.com
*/

/*global NlsDDSession object*/
var nlsddSession=null;

function NlsDDAction() {}
NlsDDAction.DD_INSERT="I";
NlsDDAction.DD_APPEND="A";

function NlsDDSession(sObj, sDt) {
  this.srcObj=sObj;
  this.srcData=sDt;
  this.destObj=null;
  this.destData=null;
  this.action=null;
  this.consume = function () {
    this.srcObj=null; this.srcData=null;this.destObj=null;this.destData=null;this.action=null;
  }
}

function I18N() {
  this.notAllowed="Not allowed!";
}

function NlsTreeDD(treeId) {
  this.tId = treeId;
  this.shiftToReorder=true;
  this.rtm={};

  var tr=nlsTree[treeId];
  tr.opt.evMouseUp=true;
  tr.opt.evMouseDown=true;
  tr.opt.evMouseOver=true;
  tr.opt.evMouseOut=true;

  //tr.opt.evMouseOver=false;
  //tr.opt.evMouseOut=false;

  tr.treeOnMouseUp = ddMouseUp;
  tr.treeOnMouseDown = ddMouseDown;
  tr.treeOnMouseOver = ddMouseOver;
  tr.treeOnMouseOut = ddMouseOut;
  tr.$treeMove = ddTreeMove;
  tr.$expdrMove=ddExpdrMove;
  tr.$expdrOut=ddExpdrOut;

  tr.ddHandler=this;

  this.tree = tr;

  this.startDrag=startDrag;
  this.endDrag=endDrag;

  this.ic=[new Image()];
  this.ic[0].src=$K.yg_cachedImages['blank'].src;

  this.i18n=new I18N();

  //public events
  this.onNodeDrag=onNodeDrag;
  this.onNodeDrop=onNodeDrop;

  this.canDrag=canDragNode;
  this.canDrop=canDropNode;

  this.setTm=function(id, f, d) {
    if(!this.rtm[id]) {this.rtm[id]=setTimeout(f, d);}
  }
  this.clearTm=function(id) {
    if(this.rtm[id]) {clearTimeout(this.rtm[id]);this.rtm[id]=null;}
  }

  return this;
}

function startDrag(e) {

  // Check distance between first click and current position
  var distX = (Event.pointerX(e) - window.ddMouseDownCoords.x);
  var distY = (Event.pointerY(e) - window.ddMouseDownCoords.y);
  if (distX < 0) distX = -distX; if (distY < 0) distY = -distY;
  // Return if distance < 4px
  if ((distX<4) && (distY<4)) return;

  // Set Dragging-Style
  document.body.addClassName('drag');
  $K.yg_activeDragInfo.dragging = true;
  var ddPic = null;

  $K.log('DRAWING!! '+$K.yg_activeDragInfo.dropAllowed, $K.Log.DEBUG);

  // Check if we are dragging over sortables...
  if ( $('yg_ddGhost') ) {

	// Reset _originalParent
	$('yg_ddGhost')._originalParent = undefined;

    if ($K.yg_currentHover) {

      var coords = new Array( e.clientX, e.clientY );
	  target = e.target;

      if ( Object.isUndefined(e.target) )
        target = e.srcElement;

      if (e.target && e.target.hasClassName('noselection') && e.target.up('.mk_contentarea'))
        target = e.target.up('.mk_contentarea').down('ul');

      // Check if we are dragging on the background-UL
      if ( (target.nodeName == 'UL') && (target.hasClassName('page_contentarea')) ) {
      //if (target.nodeName == 'UL') {

        if ( $('placeHolder') )
          $('placeHolder').remove();

        if ( Sortable._marker )
          Sortable._marker.hide();

        var newPH = document.createElement('div');
        newPH.className = 'dropmarker';
        newPH.style.position = 'relative';
        newPH.style.top = '-1px';
        newPH.style.left = '0px';
        newPH.placeHolder = true;
        newPH.id = 'placeHolder';
        newPH.onmouseover = function() {
        	if ( $('placeHolder') ) {
        		$('placeHolder').hoverOverMarker = true;
        	}
        }
        newPH.onmouseout = function() {
        	if ( $('placeHolder') ) {
        		$('placeHolder').hoverOverMarker = false;
        	}
        }

		// For Contentblocks
		var dont_append = false;

		if (target.hasClassName('page_contentarea')) {
			$(newPH).setStyle({width: (target.getWidth()+5) + 'px', top: '5px'});
			// Only insert when there are less than 1 (old: 2) Elements in the Contentarea (UL)
			if (target.childElements().length>1) {
				dont_append = true;
			}
		}
		if (!dont_append) {
			target.appendChild(newPH);
		}

		$K.yg_activeDragInfo.target = target;
		$K.yg_activeDragInfo.position = 'into';

	} else {

	  (e.target) ? e.target.id : e.srcElement.id;

		if (Prototype.Browser.IE) {
			if (e.srcElement && (e.srcElement.nodeName != 'LI') && $(e.srcElement).up('li')) {
			  var target = $(e.srcElement).up('li');
			} else if (e.srcElement) {
			  var target = e.srcElement;
			} else {
			  var target = false;
			}
		} else {
			if (e.target && (e.target.nodeName != 'LI') && $(e.target).up('li')) {
			  var target = $(e.target).up('li');
			} else if (e.target) {
			  var target = e.target;
			} else {
			  var target = false;
			}
		}
		if ( target && (target.nodeName == 'LI') && !target.hasClassName('mk_cblock') && target.up('li.mk_cblock')) {
			target = target.up('li.mk_cblock');
		}
		if (!$K.yg_activeDragInfo.hoverOverTree) {
			if (target && (target.hasClassName('mk_cblock') || target.hasClassName('listitem'))) {
				$K.yg_drawNeedles( coords, target );
			}
		} else {
			if(!Sortable._marker) {
				_createSortableMarker();
			}
			Sortable._marker.hide();
		}

        if (e.target && e.target.hasClassName('selectionmarker') && (e.target.up().hasClassName('cntblockadd'))) $K.yg_drawTopborder(e.target.up(0));
		if (e.target && e.target.hasClassName('cntblockadd')) $K.yg_drawTopborder(e.target);

      }

    } else {
      if ($('placeHolder')) $('placeHolder').remove();
	  if (!$K.yg_activeDragInfo.dropAllowed) $K.yg_showNoDropMarker(true);
    }

	var isSameLevel = false;
	if ( $K.yg_currentHover && (this.tree.opt.nosamelevel=='true') && $K.yg_activeDragInfo.hoverOverTree) {
		var targetNode = this.tree.nLst[$K.yg_currentHover.id];
		var sourceNode = nlsddSession.srcData[0];
		if (targetNode && targetNode.pr && sourceNode.pr && NlsTree.isEquals(targetNode.pr, sourceNode.pr) ) {
			var isSameLevel = true;
			$K.yg_setDropAllowed(false);
		} else {
			$K.yg_setDropAllowed(true);
		}
	}

	if (nlsddSession.srcData[0].ic!=undefined) {
		var customIcon = nlsddSession.srcData[0].ic[0];
	} else {
		var customIcon = nlsddSession.srcObj.ico.clf;
	}

    if ($('yg_ddGhost').down('img').src != customIcon) {
        $('yg_ddGhost').down('img').src = customIcon;
    }

	if ($('yg_ddGhostTree')) {
        if ( $('yg_ddGhostTree').down('img') ) {
            if ($('yg_ddGhostTree').down('img').src != customIcon) {
				$('yg_ddGhostTree').down('img').src = customIcon;
            }
        }
	}

    if( $K.yg_activeDragInfo.dropAllowed) {

	  if ( Object.isUndefined(e.target) )
	  e.target = e.srcElement;

	  /* Change style (for pseudo-dropmarkers) */
	  if (nlsddSession.action!=NlsDDAction.DD_APPEND) {
		// Draw the needle
        if ( e.target.nodeName != 'DIV' ) {
          var target = $(e.target).up('div');
        } else {
          var target = e.target;
        }
		if (nlsddSession.srcObj) {
	        // Do not show needle when dragging Tags to another Tree
			if ( (!e.target.hasClassName('cntblockadd')) && (nlsddSession.srcObj.tId.startsWith('tags_')) ) {
				$K.yg_drawTopborder( null, true );
			} else if ($K.yg_activeDragInfo.hoverOverTree) {
				$K.yg_drawTopborder( target );
			}
		}
	  }

      if ($('yg_ddGhostTree')) {
        if ( $('yg_ddGhostTree').down('img') ) {
          if ($('yg_ddGhostTree').down('img').src != customIcon) {
            $K.yg_showNoDropMarker(false);
            // NODROP
          }
        }
      }

    } else {

	  /* Remove the needle */
	  $K.log( 'Remove needle!', $K.yg_activeDragInfo.overNeedle, $K.Log.DEBUG );
	  if (!$K.yg_activeDragInfo.overNeedle) {
		$K.yg_drawTopborder( null, true );
      }

      if (Sortable._marker) {
        if (!Sortable._marker.hoverOverMarker) {
          Sortable._marker.hide();
            if( $('placeHolder') ) {
              if ( !$('placeHolder').hoverOverMarker ) {
                $('placeHolder').remove();
              }
            }
			$K.yg_showNoDropMarker(true);
          }
        }
      }
    }

	var g=NlsGetElementById('yg_ddGhostTree');
	var w=window, d=document.body, de=document.documentElement;
	var scrOffX = w.scrollX||d.scrollLeft||de.scrollLeft;
	var scrOffY = w.scrollY||d.scrollTop||de.scrollTop;
	g.style.left = e.clientX+scrOffX+5+"px";
	g.style.top = e.clientY+scrOffY+5+"px";
	//g.style.zIndex=2500;

	// Disabled (we have own handler)
	if (g.style.display=="none") {
    	var d=nlsddSession.srcData;
    	for (var i=0; i<d.length; i++) {
      		var elm=NlsGetElementById(d[i].id).childNodes[0].childNodes[0].childNodes[0].childNodes[1];
      		var imgEl=null;

      		if (nlsddSession.srcObj.opt.icon) { imgEl=elm.childNodes[(elm.childNodes.length>1?1:0)]; }

      		var ddPic = imgEl.src;

      		if( imgEl && $('yg_ddGhostTree').down('img')) {
      		  ddPic = $('yg_ddGhostTree').down('img').src;
      		}
		}
		$('yg_ddGhostTree').down('img').src = ddPic;
		if (i==1) {
			$('yg_ddGhostTree').down('.node').innerHTML = d[0].capt;
		} else {
			$('yg_ddGhostTree').down('.node').innerHTML = "("+i+" "+$K.TXT('TXT_OBJECTS')+")";
		}
		$('yg_ddGhostTree').setStyle({ display: 'block' });
	}

	this.onNodeDrag(e);
}

function endDrag(e) {

  document.body.removeClassName('drag');

  /* Remove the needle */
  $K.yg_drawTopborder( null, true );

  //hide gesture
  var g=NlsGetElementById('yg_ddGhostTree');
  //g.innerHTML="";
  g.style.display="none";

  // Delete marker (if applicable)
  if (Sortable._marker) {
    Sortable._marker.hide();
    if( $('placeHolder') ) {
      if( !$('placeHolder').hoverOverMarker ) {
        $('placeHolder').remove();
      }
    }
    $('yg_ddGhost').down('img').src = $K.yg_cachedImages['blank'].src;
  }
  $K.yg_activeDragInfo.target = null;
  $K.yg_activeDragInfo.position = null;
  $K.yg_activeDragInfo.dragging = false;

  //disable all DD related events
  document.onmousemove=null;
  document.onmouseup=null;
  document.onmousedown=function() { return true;}
  document.onselectstart=function() { return true;}
  document.ondragstart=function() { return true;}

  this.clearTm("tmExp");
  if(this.tree.rtm.tmSc) {clearInterval(this.tree.rtm.tmSc); this.tree.rtm.tmSc=null;}

}

function getTargetId(t, e, id) {
  if (nls_isIE) {
    var nd=t.nLst[e.srcElement.parentElement.parentElement.parentElement.parentElement.parentElement.id];
    if (nd) { return nd.orgId; } else { return id; }
  } else {
    return id;
  }
}

function ddMouseUp(e, id) {

  var nd=this.getNodeById(getTargetId(this, e, id));

  if($('yg_ddGhost')) {
    if ($('yg_ddGhost').style.display != 'none') {
      nlsddSession = new NlsDDSession(this, nd);
      nlsddSession.destObj=this;
      nlsddSession.destData=nd;
      this.ddHandler.endDrag(e);
      this.ddHandler.onNodeDrop(e);
      nlsddSession = null;
    }
  }
  if (!nlsddSession) return false;
  if (!this.ddHandler.canDrop(nd.orgId)) return false;
  nlsddSession.destObj=this;
  nlsddSession.destData=nd;
  this.ddHandler.endDrag(e);
  this.ddHandler.onNodeDrop(e);
  //nlsddSession=null;
}

function ddMouseDown(e, id) {

  // Save first mouseDown Coordinates (for later measuring of distance)
  window.ddMouseDownCoords = { x: Event.pointerX(e), y: Event.pointerY(e) };

  if (this.$editing) return;
  var ddHd = this.ddHandler, cNd=this.getNodeById(id), intId=cNd.id, sNd=this.selNd;
  NlsTree._blockEdit=false;
  if (this.opt.multiSel) {
    if (!this.isSelected(id) && !e.ctrlKey && !e.metaKey) {
      if(!e.shiftKey || !sNd || !NlsTree.isEquals(cNd.pr, sNd.pr)) {
        NlsTree._blockEdit=true; this.selectNode(intId);
      }
    }
  } else {
    if(this.selNd==null || this.selNd.id!=intId) { this.selectNode(intId); NlsTree._blockEdit=true;}
  }
  if(!this.isSelected(id)) return;
  var nd=this.getSelNodes();
  if (ddHd.canDrag(id)) {
    nlsddSession=new NlsDDSession(this, nd);

    document.onmousemove=function(ev) {ddHd.startDrag((ev?ev:event));}
    document.onmouseup=function(ev) {
    	nlsddSession.action=null;
    	ddHd.endDrag((ev?ev:event));
    }
    document.onselectstart=function() { return false;}
    document.onmousedown=function() { return false;}
    document.ondragstart=function() { return false;}
  }
}

function ddMouseOver(e, id) {

  if ( !$K.yg_activeDragInfo.dragging ) return;

  // Check & save target-tree properties
  if (this.tId.endsWith('_tree')) {
      var target_tree = $(this.tId).up(1);
  } else {
      var target_tree = $( this.tId );
  }

  // IF list -> tree
  if ( $('yg_ddGhost') && $('yg_ddGhost').srcReference ) {
    var yg_ddGhost = $('yg_ddGhost');

    if (yg_ddGhost.srcReference.element.parentNode) {

      if (yg_ddGhost._originalParent!=undefined) {
        var source_tree = Sortable.sortables[yg_ddGhost._originalParent.id];
      }

    }
  }

  if ( source_tree && target_tree.accepts ) {
    var treeNode = $(this.tId+id);
    var treeNodeLink = treeNode.down('a');
    if ( (target_tree.accepts.split(',').indexOf(source_tree.objectType)!=-1) &&
         /* (!treeNodeLink.hasClassName('nowrite')) && */
         !treeNodeLink.hasClassName('nodrop') &&
         (!((source_tree.objectType=='file') && id.endsWith('root_1'))) ) {
      $K.yg_setDropAllowed(true);
      $K.yg_activeDragInfo.target = target_tree;
      $K.yg_activeDragInfo.hoverOverTree = true;
      $K.yg_activeDragInfo.overTreeNode = true;
    } else {
      $K.yg_setDropAllowed(false);
      $K.yg_activeDragInfo.dropAllowed = false;
      $K.yg_activeDragInfo.overTreeNode = false;
    }
  }

  if (Sortable._marker) Sortable._marker.hide();

  if ($('placeHolder')) $('placeHolder').remove();

  if ((nlsddSession==null) || (document.onmousemove==null)) return;

  var ddHd = this.ddHandler;
  var trgId = getTargetId(this, e, id);

  // IF tree -> tree
  // Check source-tree properties
  if (nlsddSession.srcObj.tId.endsWith('_tree')) {
      var source_tree = $( nlsddSession.srcObj.tId ).up(1);
  } else {
      var source_tree = $( nlsddSession.srcObj.tId );
  }

  $K.log( 'TARGET: IsType: ' + target_tree.yg_type + ', ' + 'Accepting: ' + target_tree.accepts, $K.Log.DEBUG );
  $K.log( 'SOURCE: IsType: ' + source_tree.yg_type + ', ' + 'Accepting: ' + source_tree.accepts, $K.Log.DEBUG );

  var treeNode = $(this.tId+id);
  var treeNodeLink = treeNode.down('a');

  if (target_tree.accepts && (target_tree.accepts.split(',').indexOf(source_tree.yg_type)!=-1 ) && !treeNodeLink.hasClassName('nodrop')) {
    nlsddSession.destObj=this;
    $K.yg_setDropAllowed(true);
    $K.yg_activeDragInfo.overTreeNode = true;
    $K.yg_activeDragInfo.treeNodeLink = treeNodeLink;
  } else {
  	$K.log( '_NOT_ ACCEPTED!!!', $K.Log.WARN );
    $K.yg_setDropAllowed(false);
    $K.yg_activeDragInfo.dropAllowed = false;
    $K.yg_activeDragInfo.overTreeNode = true;
    $K.yg_activeDragInfo.treeNodeLink = treeNodeLink;
  }

  $K.yg_activeDragInfo.hoverOverTree = true;

  if (!ddHd.canDrop(trgId)) {
    var g=NlsGetElementById('yg_ddGhostTree');
    g.style.display="block";
    $K.yg_clearPlaceHolder();
  }

  //start counting
  var me=this;
  ddHd.setTm("tmExp", function() {me.expandNode(id)}, 1000);
};

function ddExpdrMove(e, id) {
  if (nlsddSession!=null && document.onmousemove!=null) {} else {return;}
  var me=this; this.ddHandler.setTm("tmExp", function() {me.expandNode(id)}, 1000);
};
function ddExpdrOut(e, id) { this.ddHandler.clearTm("tmExp"); };

function ddMouseOut(e, id) {

  $K.yg_activeDragInfo.hoverOverTree = false;
  $K.yg_activeDragInfo.dropAllowed = false;
  $K.yg_activeDragInfo.overTreeNode = false;

  if (nlsddSession!=null && document.onmousemove!=null) {} else {return;}
  var g=NlsGetElementById('yg_ddGhostTree');
  g.style.display="none";

  var ddHd = this.ddHandler;
  ddHd.clearTm("tmExp");
};

function ddTreeMove(e) {
  if (nlsddSession!=null && document.onmousemove!=null) {} else {return;}

  if(!this.rtm.tDom) this.rtm.tDom=NlsGetElementById(this.tId);
  var tD=this.rtm.tDom;
  if(!this.rtm.tH){this.rtm.tH=tD.offsetHeight;}
  var p=$getPos(tD);

  var d=document, de=d.documentElement;
  var scY=de.scrollTop||d.body.scrollTop||window.scrollY||0;

  if(e.clientY+scY-p.y>this.rtm.tH-30) {
    if(!this.rtm.tmSc)this.rtm.tmSc=setInterval(function(){$scrollTree(tD, 20)}, 100);
  } else
  if(e.clientY+scY-p.y<30) {
    if(!this.rtm.tmSc)this.rtm.tmSc=setInterval(function(){$scrollTree(tD, -20)}, 100);
  } else {clearInterval(this.rtm.tmSc); this.rtm.tmSc=null;}

};

function $scrollTree(tDom, v) {
  tDom.scrollTop=parseInt(tDom.scrollTop,10)+v;
}

function $getPos(o) {
  var t=o, x=0, y=0;
  while(t) {x+=t.offsetLeft;y+=t.offsetTop;t=t.offsetParent;}
  return {"x":x, "y":y};
};

//=========================================
//NlsTree standard implementation for
//drag and drop
//=========================================

function onNodeDrag(e) {

  if(this.shiftToReorder) {
    nlsddSession.action=(e.shiftKey?NlsDDAction.DD_INSERT:NlsDDAction.DD_APPEND);
	if (e.shiftKey) {
		if ($K.yg_activeDragInfo.dropAllowed) {
			// Draw the needle
	        if ( e.target.nodeName != 'DIV' ) {
	          var target = $(e.target).up('div');
	        } else {
	          var target = e.target;
	        }
			$K.yg_drawTopborder( null, true );
			//insert marker for inserting?;
			//$K.yg_drawTopborder( target );
		}
	}
  } else {
    nlsddSession.action=(e.shiftKey?NlsDDAction.DD_APPEND:NlsDDAction.DD_INSERT);
	if (e.shiftKey) {
		/* Remove the needle */
		$K.yg_drawTopborder( null, true );
	}
  }
}

//custom drop function
//you can override this function to perform your custom operation
function onNodeDrop(e) {
  //process
  if (!nlsddSession) return;
  var sData, dData, sObj, dObj;
  with (nlsddSession) {
    if(!action) return;
    sData=srcData; sObj=srcObj;
    dData=destData; dObj=destObj;
  }
  if (sObj.tId==dObj.tId) { //drag drop in a tree
    switch (nlsddSession.action) {
      case NlsDDAction.DD_INSERT:
        sObj.moveChild(sData, dData, 2); break;
      case NlsDDAction.DD_APPEND:
        sObj.moveChild(sData, dData, 1); break;
    }
  } else { // drag drop between tree
    switch (nlsddSession.action) {
      case NlsDDAction.DD_INSERT:
        for (i=0;i<sData.length;i++) {
          with (sData[i]) {
            var nNd=dObj.addBefore(null, dData.orgId, capt, url, (ic?ic.join(","):ic), exp, chk, xtra, title);
            if (fc) duplicateNode(fc, nNd);
          }
        }
        dObj.reloadNode(dData.pr.orgId);
        break;
      case NlsDDAction.DD_APPEND:
        for (i=0;i<sData.length;i++) {
          with (sData[i]) {
            var nNd=dObj.append(null, dData.orgId, capt, url, (ic?ic.join(","):ic), exp, chk, xtra, title);
            if (fc) duplicateNode(fc, nNd);
          }
        }
        dObj.reloadNode(nNd.orgId);
        dObj.expandNode(dData.orgId);
        break;
    }
  }
}

function duplicateNode(n, newNd) {
  do {
    var cN=nlsddSession.destObj.add(null, newNd.orgId, n.capt, n.url,  (n.ic?n.ic.join(","):n.ic), n.exp, n.chk, n.xtra, n.title);
    if (n.fc) { duplicateNode(n.fc, cN); }
    n=n.nx;
  } while (n);
}

function canDragNode(id) {
  if (this.tree.opt.multiSel) {
    var sNds=this.tree.getSelNodes();
    for (var i=0; i<sNds.length; i++) {
      if (sNds[i].allowDrag==false) return false;
    }
  } else {
    if (this.tree.getNodeById(id).allowDrag==false) return false;
  }
  return true;
}

function canDropNode(id) {
  var dest=this.tree.getNodeById(id);
  var src=nlsddSession.srcData;
  if (!nlsddSession.srcObj) return;
  var inTree = (nlsddSession.srcObj.tId==this.tree.tId);
  //if (dest.allowDrop==false) return false;
  if (!nlsddSession) return false;
  if(!nlsddSession.action) return false;
  if (!src || !dest || src.length==0) return false;
  if (inTree) {
    if (this.tree.isSelected(dest.orgId)) return false;
    var tmp=dest;
    while(tmp.pr) { if (this.tree.isSelected(tmp.orgId)) return false; tmp=tmp.pr; }
    switch (nlsddSession.action) {
      case NlsDDAction.DD_INSERT:
        if (dest.equals(this.tree.rt)) return false;
        for (var i=0; i<src.length; i++) { if (src[i].nx && dest.equals(src[i].nx)) return false; }
        break;
      case NlsDDAction.DD_APPEND:
        if (this.tree.isSelected(this.tree.rt.orgId)) return false;
        for (var i=0; i<src.length; i++) { if (dest.equals(src[i].pr)) return false; }
        break;
    }
  } else {
    switch (nlsddSession.action) {
      case NlsDDAction.DD_INSERT: if (dest.equals(this.tree.rt)) return false;
      case NlsDDAction.DD_APPEND: break;
    }
  }
  return true;
};

//=========================================
//NlsTree extension for drag and drop
//=========================================

NlsTree._allowEdit=false;

NLSTREE.ddHandler=null;

NLSTREE.unloadChild = function(src) {
  var pr = src.pr;
  if (pr.lc.equals(src)) pr.lc=src.pv;
  if (pr.fc.equals(src)) pr.fc=src.nx;
  if (src.pv!=null) src.pv.nx=src.nx;
  if (src.nx!=null) src.nx.pv=src.pv;
  src.nx=null;src.pv=null;src.pr=null;
  if (this.selNd) { this.selNd=null; this.selElm=null; }
  if (this.opt.multiSel) { this.msRemove(src.orgId); }
}

//move a node
//type: 1 append child 2: insert before, 3: insert after
NLSTREE.moveChild = function (src, dest, type) {
  //validation
  if (!src || !dest || src.length==0) return;
  if (this.isSelected(dest.orgId)) return;
  var tmp=dest;
  while(tmp.pr) { if (this.isSelected(tmp.orgId)) return; tmp=tmp.pr; }

  switch (type) {
    case 1:
      if (this.isSelected(this.rt.orgId)) return;
      for (var i=0; i<src.length; i++) { if (dest.equals(src[i].pr)) false; }

      for (var i=0; i<src.length; i++) {
        /*unreference source node*/
        var srcPr=src[i].pr;
        this.unloadChild(src[i]);
        this.reloadNode(srcPr.orgId);

        /*add to new parent*/
        src[i].pr=dest;
        if (dest.lc==null) {dest.fc=src[i];dest.lc=src[i];} else {
          var t=dest.fc;
          if (this.opt.sort!="no") {
            do { if (this.opt.sort=="asc" ? this.compareNode(t, src[i]) : this.compareNode(src[i], t)) break; t = t.nx;
            } while (t!=null);
            if (t!=null) { if (t.pv==null) { t.pv=src[i]; dest.fc=src[i]; } else { src[i].pv=t.pv; t.pv.nx=src[i]; t.pv=src[i]; } src[i].nx=t; }
          }
          if (this.opt.sort=="no" || t==null) { src[i].pv = dest.lc; dest.lc.nx = src[i]; dest.lc = src[i]; }
        }
      }
      this.reloadNode(dest.orgId);
      this.expandNode(dest.orgId);
      break;
    case 2: /*before*/
    case 3: /*after*/
      var sCh="pv"; var dCh="nx"; var ch="lc";
      if (type==2) {sCh="nx"; dCh="pv"; ch="fc";}
      if (dest.equals(this.rt)) return;
      for (var i=0; i<src.length; i++) { if (src[i][sCh] && dest.equals(src[i][sCh])) return; }

      /*unreference source node*/
      for (var i=0; i<src.length; i++) {
        var srcPr=src[i].pr;
        this.unloadChild(src[i]);
        this.reloadNode(srcPr.orgId);

        src[i].pr=dest.pr;
        if (dest[dCh]==null) { dest[dCh]=src[i]; dest.pr[ch]=src[i]; } else { src[i][dCh]=dest[dCh]; dest[dCh][sCh]=src[i]; dest[dCh]=src[i]; } src[i][sCh]=dest;
      }
      this.reloadNode(dest.pr.orgId);
      break;

  }
}

function nls_setNodeDnD(t, id, prop, v, incSub) {
  if (incSub==true) {
    t.loopTree(t.getNodeById(id), function(nd) {nd[prop]=v;});
  } else {
    t.getNodeById(id)[prop]=v;
  }
}

NLSTREE.setDrag=function(id, v, incSub) {
  nls_setNodeDnD(this, id, "allowDrag", v, incSub);
};

NLSTREE.setDrop=function(id, v, incSub) {
  nls_setNodeDnD(this, id, "allowDrop", v, incSub);
};

NlsNode.prototype.allowDrag=true;
NlsNode.prototype.allowDrop=true;
