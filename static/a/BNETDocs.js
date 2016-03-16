/**
 * BNETDocs: Phoenix
 * Copyright (C) 2003-2015 BNETDocs CC-BY-NC-SA 4.0
 * <https://dev.bnetdocs.org/legal>
 *
 * The site should **ALWAYS** work if JavaScript is disabled. This script is
 * intended to be an addon to enhance the experience, not a required feature.
 */

"use strict";

function BNETDocs() {

  var self = this;

  this.fHookExternalAnchors = function() {
    for (var id in document.links) {
      var link = document.links[id];
      if (link.rel && link.rel.indexOf('external') != 1) {
        link.onclick = function(e) {
          /*e.preventDefault();
          e.stopPropagation();*/
          if (this.dataset.popup !== undefined && this.dataset.popup == 1) {
            window.open(
              this.href, '', 'width=600,height=300,left=100,top=100'
            );
          } else {
            window.open(
              this.href, '_blank'
            );
          }
          return false;
        }
      }
    }
  };

  this.fSelectText = function(obj) {
    // copied from <http://goo.gl/dDuR8U>
    // adapted from Denis Sadowski (via StackOverflow.com)
    if (document.selection) {
      var range = document.body.createTextRange();
      range.moveToElementText(obj);
      range.select();
    } else if (window.getSelection) {
      var range = document.createRange();
      range.selectNode(obj);
      window.getSelection().addRange(range);
    }
  };

  window.onload = function() {
    self.fHookExternalAnchors();
  };

};

var bnetdocs = new BNETDocs();
