/**
 * BNETDocs - The JavaScript framework for the BNETDocs site.
 *
 * The site should **ALWAYS** work if JavaScript is disabled.
 **/

function BNETDocs() {
  
  var self = this;
  
  /**
   * endsWith Source: http://stackoverflow.com/questions/280634/endswith-in-javascript
   **/
  if (typeof String.prototype.endsWith !== 'function') {
    String.prototype.endsWith = function(suffix) {
      return this.indexOf(suffix, this.length - suffix.length) !== -1;
    };
  }
  
  this.fHookExternalAnchors = function() {
    for (var id in document.links) {
      var link = document.links[id];
      if (link.rel && link.rel.indexOf('external') != 1) {
        link.onclick = function(e) {
          window.open(this.href, '', 'width=600,height=300,left=100,top=100');
          return false;
        }
      }
    }
  }
  
  this.fOverrideNavigationAnchors = function() {
    var sidebar_left = document.getElementById('sidebar_left');
    for (var id in sidebar_left.children) {
      var tag = sidebar_left.children[id];
      if (tag.tagName == 'a') {
        tag.onclick = function(e) {
          if (e.which == 2 || e.metaKey || e.ctrlKey) return true;
          self.fPageLoadAjax(this.href);
          return false;
        }
      }
    }
    /*var news_back = document.getElementsByClassName('news_back');
    for (var id in news_back) {
      var tag = news_back[id];
      for (var anchor_id in tag.children) {
        var anchor = tag.children[anchor_id];
        if (anchor.tagName == 'a') {
          anchor.onclick = function() {
            self.fPageLoadAjax(this.href);
            return false;
          }
        }
      }
    }
    var news_items = document.getElementsByClassName('news_item');
    for (var id in news_items) {
      var tag = news_items[id];
      for (var anchor_id in tag.children) {
        var anchor = tag.children[anchor_id];
        if (anchor.tagName == 'a') {
          anchor.onclick = function() {
            self.fPageLoadAjax(this.href);
            return false;
          }
        }
      }
    }*/
  }
  
  this.fGetExtraStyle = function() {
    var link_elements = document.getElementsByTagName('link');
    var link_found = false;
    for (var id in link_elements) {
      if (link_elements[id].href.endsWith('.css') && link_elements[id].href != '/main.css') {
        link_found = true;
        break;
      }
    }
    if (!link_found) return '';
    return link_elements[id].href;
  }
  
  this.fSetExtraStyle = function(styleHref) {
    var link_elements = document.getElementsByTagName('link');
    var link_found = false;
    for (var id in link_elements) {
      if (link_elements[id].href == styleHref) {
        link_found = true;
        break;
      }
    }
    if (!link_found)
      document.getElementsByTagName('head')[0].innerHTML
        += '<link rel="stylesheet" href="'
         + styleHref
         + '" type="text/css" media="all" />';
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
          
          var title_element   = document.getElementsByTagName('title')[0];
          var content_element = document.getElementById('content');
          var pageContent     = this.response;
          var pageTitle       = this.getResponseHeader('X-Page-Title');
          var pageExtraStyle  = this.getResponseHeader('X-Page-Extra-Style');
          
          title_element.innerHTML = pageTitle;
          self.fSetExtraStyle(pageExtraStyle);
          content_element.innerHTML = pageContent;
          
          self.fHookExternalAnchors();
          
          history.pushState({
            'title': pageTitle,
            'extraStyle': pageExtraStyle,
            'content': pageContent
          }, pageTitle, href);
          
        } catch (e) {
          window.location = href;
        }
      }
    }
    url = href;
    var url_hashpos = url.indexOf("#");
    if (url.indexOf("?") != -1) {
      if (url_hashpos != -1)
        url = url.substring(0, url_hashpos) + "&ajax" + url.substring(url_hashpos);
      else
        url += "&ajax";
    } else {
      if (url_hashpos != -1)
        url = url.substring(0, url_hashpos) + "?ajax" + url.substring(url_hashpos);
      else
        url += "?ajax";
    }
    xhr.open("GET", url, true);
    xhr.send();
  }
  
  window.onload = function() {
    self.fOverrideNavigationAnchors();
    self.fHookExternalAnchors();
  }
  
  window.onpopstate = function(event) {
    var title_element       = document.getElementsByTagName('title')[0];
    var content_element     = document.getElementById('content');
    
    if (!event.state) {
      history.replaceState({
        'title': title_element.innerHTML,
        'extraStyle': self.fGetExtraStyle(),
        'content': content_element.innerHTML
      });
    } else {
      title_element.innerHTML = event.state.title;
      self.fSetExtraStyle(event.state.extraStyle);
      content_element.innerHTML = event.state.content;
      
      self.fHookExternalAnchors();
    }
  }
  
}

oBNETDocs = new BNETDocs();