/**
* nlstreeext_inc.js v1.0
* Copyright 2005-2006, addobject.com. All Rights Reserved
* Author Jack Hermanto, www.addobject.com
*/
NLSTREE.ajaxLoadChildNodes=function(id) {
  var nd=this.getNodeById(id);
  var req=NlsTree.AJAX.createRequest();
  var params = (nd.chUrl?nd.chUrl:this.chUrl);
  for (var element in this.treeParameters) {
	  params += '&'+element+'='+this.treeParameters[element];
  }
  params = params.toQueryParams();
  Object.extend(params, {
	  nid: id,
	  site: nd.yg_id.split('-')[1]
  })
  params = Object.toQueryString(params);
  //var url=$nlsAddParam((nd.chUrl?nd.chUrl:this.chUrl), 'nid='+id+'&site='+nd.yg_id.split('-')[1]);
  var url=(nd.chUrl?nd.chUrl:this.chUrl).split('?')[0] + '?' + params;
  
  var me=this;
  
  req.open("get", url, true);
  req.onreadystatechange=function() {
    if(req.readyState==4) {
      if(req.status==200 || req.status==304) {
        var de=req.responseXML.documentElement;
        if(!de||de.childNodes.length==0) { //if no submenu
        } else {
          me.removeChilds(id);
          me.addChildNodesXML(de, true);
          me.expandNode(id);
          
          if (me.selectAfterAjax) {
        	  me.selectNodeById( me.selectAfterAjax );
        	  me.selectAfterAjax = undefined;
          }
          
        }
        nd.loaded=2;
      }
    }
  };
  //animate icon here, change the icon or text.
  /*if(this.opt.icon) {
    var sElm=NlsGetElementById(nd.id);
    var ic=sElm.childNodes[0].childNodes[0].childNodes[0].childNodes[1];
    if (ic.childNodes.length==2) {ic=ic.childNodes[1];} else {ic=ic.childNodes[0];} 
    ic.src=nlsTreeIc[this.ico.lod].src;
  } else { */
  var oNd=$getAnchor(NlsGetElementById(nd.id)); 
  oNd.innerHTML=$K.TXT('TXT_LOADING')+"...";
  oNd.className = this.opt.stlprf + 'prnnode';
  nd.loaded=1; //loading
  req.send(null);
};

NLSTREE.ajaxLoadChildNodesList=function(ids) {

  if (ids.length == 0) {
  	return;
  }
  var id = ids[0];
  var nd=this.getNodeById(id);
  var req=NlsTree.AJAX.createRequest();
  var url=$nlsAddParam((nd.chUrl?nd.chUrl:this.chUrl), "nid="+id);
  var me=this;
  req.open("get", url, true);
  req.onreadystatechange=function() {
    if(req.readyState==4) {
      if(req.status==200 || req.status==304) {
        var de=req.responseXML.documentElement;
        nd.loaded=2;
        if(!de||de.childNodes.length==0) { //if no submenu
        } else {
          me.removeChilds(id);
          me.addChildNodesXML(de, true);
          me.expandNode(id);
          if (ids.length == 1) {
          	me.selectNodeById( id );
          }
        }
        ids.shift();
        me.ajaxLoadChildNodesList( ids );
      }
    }
  };
  //animate icon here, change the icon or text.
  /*if(this.opt.icon) {
    var sElm=NlsGetElementById(nd.id);
    var ic=sElm.childNodes[0].childNodes[0].childNodes[0].childNodes[1];
    if (ic.childNodes.length==2) {ic=ic.childNodes[1];} else {ic=ic.childNodes[0];} 
    ic.src=nlsTreeIc[this.ico.lod].src;
  } else { */
  var oNd=$getAnchor(NlsGetElementById(nd.id)); 
  oNd.innerHTML=$K.TXT('TXT_LOADING')+"...";
  oNd.className = this.opt.stlprf + 'prnnode';
  //};
  nd.loaded=1; //loading
  req.send(null);
};

NLSTREE.setServerLoad=function(id, url) {
  n=this.getNodeById(id);
  n.svrLoad=true;
  n.loaded=0;
  n.chUrl=url;
};

NlsNode.prototype.loaded=0;
NlsNode.prototype.chUrl=null;

/*utility*/
function $nlsAddParam(url, par) {
  var s=(url.indexOf("?")!=-1?"&":"?");
  return url+s+par;
};