/**
 * BNETDocs - The JavaScript framework for the BNETDocs site.
 *
 * The site should **ALWAYS** work if JavaScript is disabled.
 **/

function BNETDocs() {
  
  var self = this;
  
  this.fOverrideNavigationAnchors = function() {
    var sidebar_left = document.getElementById('sidebar_left');
    for (var id in sidebar_left.children) {
      var tag = sidebar_left.children[id];
      if (tag.tagName == 'a') {
        tag.onclick = function() {
          self.fPageLoadAjax(this.href);
          return false;
        }
      }
    }
  }
  
  this.fPageLoadAjax = function(href) {
    var xhr, url;
    if (window.XMLHttpRequest) {
      xhr = new XMLHttpRequest();
    } else {
      xhr = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xhr.onreadystatechange = function() {
      if (this.readyState == 4) {
        try {
          if (this.status != 200) throw new Error;
          var content_element = document.getElementById('content');
          var pageTitle       = this.getResponseHeader('X-Page-Title');
          var pageExtraStyle  = this.getResponseHeader('X-Page-Extra-Style');
          link_elements = document.getElementsByTagName('link');
          link_found = false;
          for (var id in link_elements) {
            if (link_elements[id].href == pageExtraStyle) {
              link_found = true;
              break;
            }
          }
          if (!link_found)
            document.getElementsByTagName('head')[0].innerHTML
              += '<link rel="stylesheet" href="'
               + pageExtraStyle
               + '" type="text/css" media="all" />';
          content_element.innerHTML = this.response;
          history.pushState(content_element, pageTitle, href);
        } catch (e) {
          window.location = href;
        }
      }
    }
    url = href;
    if (url.indexOf("?") != -1) {
      url += "&ajax";
    } else {
      url += "?ajax";
    }
    xhr.open("GET", url, true);
    xhr.send();
  }
  
  window.onload = function() {
    history.replaceState(document.getElementById('content'));
    self.fOverrideNavigationAnchors();
  }
  
  window.onpopstate = function(event) {
    if (typeof event.state == 'string') {
      document.getElementById('content') = event.state;
    }
  }
  
}

oBNETDocs = new BNETDocs();