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
  
  this.fAjax = function(href, callback, appendAjaxToUrl) {
    if (typeof appendAjaxToUrl == "undefined") appendAjaxToUrl = true
    var xhr, url;
    if (window.XMLHttpRequest) {
      xhr = new XMLHttpRequest();
    } else {
      xhr = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xhr.onreadystatechange = function() {
      callback(this);
    }
    url = href;
    if (appendAjaxToUrl) {
      var url_hashpos = url.indexOf("#");
      var url_rand    = self.fGenerateId(4); // Fixes caching issues with Ajax.
      if (url.indexOf("?") != -1) {
        if (url_hashpos != -1)
          url = url.substring(0, url_hashpos) + "&ajax=" + url_rand + url.substring(url_hashpos);
        else
          url += "&ajax=" + url_rand;
      } else {
        if (url_hashpos != -1)
          url = url.substring(0, url_hashpos) + "?ajax=" + url_rand + url.substring(url_hashpos);
        else
          url += "?ajax=" + url_rand;
      }
    }
    xhr.open("GET", url, true);
    xhr.send();
  }
  
  /**
   * Source: http://stackoverflow.com/questions/1349404/generate-a-string-of-5-random-characters-in-javascript
   **/
  this.fGenerateId = function(length) {
    var text = "";
    var mask = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    if (typeof length == "undefined" || length == null || length < 1)
      length = 1;
    for (var i = 0; i < length; i++)
      text += mask.charAt(Math.floor(Math.random() * mask.length));
    return text;
  }
  
  /**
   * Source: http://jsfiddle.net/W75mP/
   **/
  this.fGetPageHeight = function() {
    var D = document;
    return Math.max(
      D.body.scrollHeight, D.documentElement.scrollHeight,
      D.body.offsetHeight, D.documentElement.offsetHeight,
      D.body.clientHeight, D.documentElement.clientHeight
    );
  }
  
  /**
   * Source: http://jsfiddle.net/W75mP/
   **/
  this.fGetScrollXY = function() {
    var scrOfX = 0, scrOfY = 0;
    if( typeof( window.pageYOffset ) == 'number' ) {
      //Netscape compliant
      scrOfY = window.pageYOffset;
      scrOfX = window.pageXOffset;
    } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
      //DOM compliant
      scrOfY = document.body.scrollTop;
      scrOfX = document.body.scrollLeft;
    } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
      //IE6 standards compliant mode
      scrOfY = document.documentElement.scrollTop;
      scrOfX = document.documentElement.scrollLeft;
    }
    return [ scrOfX, scrOfY ];
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
    var news_back = document.getElementsByClassName('news_back');
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
    }
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
    console.log("[Ajax] Loading page: " + href);
    self.fAjax(href, function(res){
      if (res.readyState == 4) {
        try {
          if (res.status != 200) throw new Error;
          
          var title_element   = document.getElementsByTagName('title')[0];
          var content_element = document.getElementById('content');
          var pageContent     = res.response;
          var pageTitle       = res.getResponseHeader('X-Page-Title');
          var pageExtraStyle  = res.getResponseHeader('X-Page-Extra-Style');
          
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
    });
  }
  
  this.fPageLoadBottomAjax = function() {
    var current_page = window.location.pathname;
    if (current_page == "/" || current_page == "/news") {
      var last_news_articles = document.getElementsByTagName("article");
      var last_news_id       = null;
      for (var i = 0; i < last_news_articles.length; ++i) {
        var j = last_news_articles[i].id;
        if (j.substring(0, 1) == "n") {
          var k = parseInt(j.substring(1));
          if ((last_news_id == null || k < last_news_id) && k != 0) {
            last_news_id = k;
          }
        }
      }
      var range = [
        last_news_id - 6,
        last_news_id - 1,
        "descending",
      ];
      while (range[1] < 1) {
        ++range[1];
      }
      if (range[0] < 1) {
        console.log("[Lazy Load] All Articles Loaded.");
        return;
      }
      console.log("[Lazy Load] Retrieving news articles " + range[0]
        + " through " + range[1] + " in " + range[2]
        + " order based on last id of " + last_news_id + ".");
      var url = "/news"
        + "?start=" + encodeURIComponent(range[0])
        + "&count=" + encodeURIComponent(range[1] - range[0])
        + "&order=" + encodeURIComponent(range[2]);
      self.fAjax(url, function(res){
        if (res.readyState == 4) {
          try {
            if (res.status != 200) throw new Error;
            
            var content_element  = document.getElementById('content');
            var extraPageContent = res.response;
            
            content_element.innerHTML += extraPageContent;
            
            self.fHookExternalAnchors();
            
          } catch (e) {
            console.log("[Lazy Load] Failed to load more news articles.");
            console.log(e);
          }
        }
      });
    }
  }
  
  window.onload = function() {
    var title_element   = document.getElementsByTagName('title')[0];
    var content_element = document.getElementById('content');
    history.replaceState({
      'title': title_element.innerHTML,
      'extraStyle': self.fGetExtraStyle(),
      'content': content_element.innerHTML
    });
    self.fOverrideNavigationAnchors();
    self.fHookExternalAnchors();
  }
  
  window.onpopstate = function(event) {
    var title_element   = document.getElementsByTagName('title')[0];
    var content_element = document.getElementById('content');
    
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
  
  /**
   * Source: http://jsfiddle.net/W75mP/
   **/
  document.addEventListener("scroll", function (event) {
    if (self.fGetPageHeight() == self.fGetScrollXY()[1] + window.innerHeight) {
      self.fPageLoadBottomAjax();
    }
  });
  
}

oBNETDocs = new BNETDocs();