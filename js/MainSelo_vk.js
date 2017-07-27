/**
 * Created by user on 12/12/16.
 */
"use strict";

var SN = function (social) { // social == 2
    var that = this;
    console.log('init vk social');
    that.load = function (url, callback, id) {
        var s = document.createElement('script');
        s.type = 'text/javascript';
        s.src = url;
        var script = document.getElementsByTagName('script')[1];
        script.parentNode.insertBefore(s, script);
        if (typeof id !== 'undefined') { script.setAttribute("id", id); }
        if (typeof callback === 'function') {
            s.addEventListener('load', function () {
                callback();
            }, false);
        }
    };

    that.load('//vk.com/js/api/xd_connection.js?2', function () {
        that.showInviteBox = function () {
            VK.callMethod('showInviteBox');
        };

        VK.init({apiId: 5448769, onlyWidgets: true});

        // VK.Widgets.Like("vk_like", {type: "button"});
        // VK.Widgets.Subscribe("vk_subscribe", {}, -38679323);
    });

    that.flash = function(){
        return document.getElementById("farm_game");
    };


};



