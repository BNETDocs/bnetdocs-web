/**
 * BNETDocs - The JavaScript framework for the BNETDocs site.
 *
 * The site should **ALWAYS** work if JavaScript is disabled.
 **/

function BNETDocs() {
  
  var self = this;
  
  this.fOverrideNavigationAnchors = function() {
    var sidebar_left = document.getElementById('sidebar_left');
    for (id in sidebar_left.children) {
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
      var hardLoad = false;
      if (xhr.readyState == 4 && xhr.status == 200) {
        try {
          document.getElementById('content').innerHTML = xhr.response;
        } catch (e) {
          hardLoad = true;
        }
      } else {
        hardLoad = true;
      }
      if (hardLoad) {
        window.location = href;
      }
    }
    url = href;
    if (url.indexOf("?") != -1) {
      url += "&ajax=1";
    } else {
      url += "?ajax=1";
    }
    xhr.open("GET", url, true);
    xhr.send();
  }
  
  window.onload = function() {
    self.fOverrideNavigationAnchors();
  }
  
}

oBNETDocs = new BNETDocs();