/**
 *  BNETDocs, the documentation and discussion website for Blizzard protocols
 *  Copyright (C) 2003-2020  "Arta", Don Cullen "Kyro", Carl Bennett, others
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

  this.dateToString = function(date) {    
    let options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', time: 'long' };
    return date.toLocaleTimeString(undefined, options);
  };

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

  this.fTimeToLocale = function() {
    var timestamps = document.getElementsByTagName('time');
    for (var id in timestamps) {
      if (timestamps[id].attributes === undefined ||
        timestamps[id].attributes.datetime === undefined
      ) continue;
      var d = new Date(timestamps[id].attributes.datetime.value);
      timestamps[id].innerText = self.dateToString(d);
    }
  };

  this.isNumeric = function(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
  }

  window.onload = function() {
    self.fHookExternalAnchors();
    self.fHookNavigationMenu();
    self.fTimeToLocale();
  };

};

var bnetdocs = new BNETDocs();
