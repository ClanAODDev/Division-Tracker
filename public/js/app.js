!function e(t,r,n){function i(u,c){if(!r[u]){if(!t[u]){var a="function"==typeof require&&require;if(!c&&a)return a(u,!0);if(o)return o(u,!0);var s=new Error("Cannot find module '"+u+"'");throw s.code="MODULE_NOT_FOUND",s}var l=r[u]={exports:{}};t[u][0].call(l.exports,function(e){var r=t[u][1][e];return i(r?r:e)},l,l.exports,e,t,r,n)}return r[u].exports}for(var o="function"==typeof require&&require,u=0;u<n.length;u++)i(n[u]);return i}({1:[function(e,t,r){"use strict";var n=n||{};!function(e){n={Setup:function(){n.SearchMembers(),n.AnimateCounter(),n.SearchCollection()},SearchMembers:function(){this.TriggerFilter(document.getElementById("member-search"),this.GetSearchResults,1e3)},TriggerFilter:function(t,r,n){var i=null;e("#member-search").length&&(t.onkeypress=function(){i&&window.clearTimeout(i),i=window.setTimeout(function(){i=null,r()},n)},t=null)},GetSearchResults:function(){if(e("#member-search").val()){var t=e("input#member-search").val();e.ajax({url:"/search/members/"+t,type:"GET",success:function(t){e("#member-search-results").html(t)}})}},AnimateCounter:function(){e(".count-animated").each(function(){var t=e(this);e({Counter:0}).animate({Counter:t.text()},{duration:3e3,easing:"easeOutQuart",step:function(){t.hasClass("percentage")?t.text(n.FormatNumber(Math.ceil(this.Counter)+"%")):t.text(n.FormatNumber(Math.ceil(this.Counter)))}})})},FormatNumber:function(e){return e.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g,"$1,")},SearchCollection:function(){e("#search-collection").keyup(function(){var t=e(this).val(),r=new RegExp("^"+t,"i"),n=".collection .collection-item";e(n).each(function(){var t=r.test(e(this).text());e(this).toggle(t)})})}}}(jQuery),n.Setup()},{}]},{},[1]);