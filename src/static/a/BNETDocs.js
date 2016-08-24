/**
 *  BNETDocs, the Battle.net(TM) protocol documentation and discussion website
 *  Copyright (C) 2008-2016  Carl Bennett
 *  This file is part of BNETDocs.
 *
 *  BNETDocs is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  BNETDocs is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with BNETDocs.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  The site should **ALWAYS** work if JavaScript is disabled. This script is
 *  intended to be an addon to enhance the experience, not a required feature.
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
        };
      }
    }
  };

  this.fHookNavigationMenu = function() {
    var mobile_nav = document.getElementById("mobile-nav");
    mobile_nav.onclick = function(e) {
      var nav = document.getElementsByTagName("nav")[0];
      if (nav.style.display != "block") {
        nav.style.display = "block";
      } else {
        nav.style.display = "";
      }
    };
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
    self.fHookNavigationMenu();
  };

};

var bnetdocs = new BNETDocs();
