/**
 * Created by user on 12/12/16.
 */
"use strict";

var SN = function (social) { // social == 2
    var that = this;
    //console.log('init vk social');
    // that.load = function (url, callback, id) {
    //     var s = document.createElement('script');
    //     s.type = 'text/javascript';
    //     s.src = url;
    //     var script = document.getElementsByTagName('script')[1];
    //     script.parentNode.insertBefore(s, script);
    //     if (typeof id !== 'undefined') { script.setAttribute("id", id); }
    //     if (typeof callback === 'function') {
    //         s.addEventListener('load', function () {
    //             callback();
    //         }, false);
    //     }
    // };

    // that.load('//vk.com/js/api/xd_connection.js?2', function () {
    //     that.showInviteBox = function () {
    //         VK.callMethod('showInviteBox');
    //     };
    //
    //     VK.init({apiId: 	6360136, onlyWidgets: true});
    //
    //     // VK.Widgets.Like("vk_like", {type: "button"});
    //     // VK.Widgets.Subscribe("vk_subscribe", {}, -38679323);
    // });

    that.flash = function(){
        return document.getElementById("selo_game");
    };

    that.vk_init = function() {
        console.log('MainSelo_vk:: try vk init');
        VK.init(function() {
            console.log('MainSelo_vk:: on init success');
            that.flash().onInit();
        }, function() {
            console.log('MainSelo_vk:: on init error');
            that.flash().onError({txt: 'onInitError'});
        }, '5.73');
    };

    that.vk_api = function(e){
        console.log('MainSelo_vk:: try vk_api method: ' + e.method);
        e.params.test_mode = 1;
        VK.api(e.method, e.params, function (d){
            console.log('MainSelo_vk:: response data for method: ' + e.method);
            console.log(d);
            if (d.error) {
                that.flash().apiCallback({message: 'error', error: d.error, key: e.key, method: e.method});
            } else {
                that.flash().apiCallback({message: 'success', result: d, key: e.key, method: e.method});
            }
        });
    };
    
    that.callMethod = function(m,p) {
        VK.callMethod.apply(null, [m].concat(p));
    };

};



