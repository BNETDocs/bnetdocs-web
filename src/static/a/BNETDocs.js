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

  this.dateToString = function(x) {
    var y = '';
    switch (x.getDay()) {
      case 0: y += 'Sun'; break;
      case 1: y += 'Mon'; break;
      case 2: y += 'Tue'; break;
      case 3: y += 'Wed'; break;
      case 4: y += 'Thu'; break;
      case 5: y += 'Fri'; break;
      case 6: y += 'Sat'; break;
    }
    y += ', ';
    switch (x.getMonth()) {
      case 0:  y += 'Jan'; break;
      case 1:  y += 'Feb'; break;
      case 2:  y += 'Mar'; break;
      case 3:  y += 'Apr'; break;
      case 4:  y += 'May'; break;
      case 5:  y += 'Jun'; break;
      case 6:  y += 'Jul'; break;
      case 7:  y += 'Aug'; break;
      case 8:  y += 'Sep'; break;
      case 9:  y += 'Oct'; break;
      case 10: y += 'Nov'; break;
      case 11: y += 'Dec'; break;
    }
    y += ' ' + x.getDate() + ' ' + x.getFullYear();
    y += ' at ' + x.toLocaleTimeString();
    return y;
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
