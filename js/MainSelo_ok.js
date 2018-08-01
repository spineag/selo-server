"use strict";

var SN = function (social) { // social == 3
    var that = this;

    console.log('init ok social');
    var rParams = FAPI.Util.getRequestParameters();
    FAPI.init(rParams["api_server"], rParams["apiconnection"],
        function() {
            console.log("Инициализация прошла успешно");
        }, function(error) {
            console.log("Ошибка инициализации");
        });

    that.flash = function(){
        return document.getElementById("selo_game");
    };

    // that.getPageInfo = function(){
    //     FAPI.UI.getPageInfo();
    // };
    // that.setPageHeight = function(w, h) {
    //     //FAPI.UI.setWindowSize(w, h + 40); https://apiok.ru/dev/sdk/js/ui.setWindowSize  - not work
    //     console.log('try set Height: ' + h);
    //     //$('.game_body').height(h);
    //     //$('#selo_game').height(h-40);
    // };

    that.getProfile = function(userSocialId, params) {
        var fields = params.join();
        console.log('OK: try get user profile');
        FAPI.Client.call({"method":"users.getCurrentUser", "fields":fields}, that.getProfileCallback);
        // FAPI.Client.call({"method":"users.getInfo", "uids":[userSocialId], "fields":params}, that.API_callback);
    };
    that.getProfileCallback = function(result, data) {
        console.log('getProfileCallback result: ' + result);
        that.flash().getProfileHandler(data);
    };

    that.getAllFriends = function(userSocialId) {
        console.log('OK: try get getAllFriends');
        FAPI.Client.call({"method":"friends.get", "uid":userSocialId}, that.getAllFriendsCallback);
    };
    that.getAllFriendsCallback = function(result, data) {
        console.log('getAllFriendsCallback result: ' + result);
        that.flash().getAllFriendsHandler(data);
    };

    that.getUsersInfo = function(uids, params) {
        var ids = uids.join();
        var fields = params.join();
        console.log('OK: try get getUsersInfo');
        FAPI.Client.call({"method":"users.getInfo", "uids":ids, "fields":fields}, that.getUsersInfoCallback);
    };
    that.getUsersInfoCallback = function(result, data) {
        console.log('getUsersInfoCallback result: ' + result);
        that.flash().getUsersInfoHandler(data);
    };

    that.getTempUsersInfoById = function(uids, params) {
        var ids = uids.join();
        var fields = params.join();
        console.log('OK: try get getTempUsersInfoById');
        FAPI.Client.call({"method":"users.getInfo", "uids":ids, "fields":fields}, that.getTempUsersInfoByIdCallback);
    };
    that.getTempUsersInfoByIdCallback = function(result, data) {
        console.log('getTempUsersInfoByIdCallback result: ' + result);
        that.flash().getTempUsersInfoByIdHandler(data);
    };

    that.getFriendsByIds = function(uids, params) {
        var ids = uids.join();
        var fields = params.join();
        console.log('OK: try get getFriendsByIds');
        FAPI.Client.call({"method":"users.getInfo", "uids":ids, "fields":fields}, that.getFriendsByIdsCallback);
    };
    that.getFriendsByIdsCallback = function(result, data) {
        console.log('getFriendsByIdsCallback result: ' + result);
        that.flash().getFriendsByIdsHandler(data);
    };

    that.getAppUsers = function(userSocialId) {
        console.log('OK: try get getAppUsers');
        FAPI.Client.call({"method":"friends.getAppUsers"}, that.getAppUsersCallback);
    };
    that.getAppUsersCallback = function(result, data) {
        console.log('getAppUsersCallback result: ' + result);
        console.log('getAppUsersCallback data: ' + data);
        that.flash().getAppUsersHandler(data);
    };

    that.showInviteWindowAll = function(userSocialId) {
        console.log('OK: try get showInviteWindowAll');
        FAPI.UI.showInvite("Приглашаю посетить игру Вязаный мир.");
    };

    that.showPayment = function(txt, txt2, id, price) {
        console.log('OK: try get showPayment for id: ' + id + '  and price: ' + price);
        FAPI.UI.showPayment(txt, txt2, id, price, null, null, "ok", "true");
    };

    that.isInGroup = function(groupId, userId) {
        console.log('OK: try isInGroup');
        FAPI.Client.call({"method":"group.getUserGroupsByIds", "group_id":groupId, "uids":userId}, that.isInGroupCallback);
    };

    that.isInGroupCallback = function(result, data) {
        console.log('isInGroupCallback result: ' + result);
        console.log('isInGroupCallback data: ' + data);
        that.flash().isInGroupCallback(data);
    };

    that.makeWallPost = function(uid, message, url){
        console.log('OK: try get makeWallPost');
        FAPI.UI.postMediatopic({
            "media":[
                {
                    "type": "text",
                    "text": 'Вязаный мир'
                },
                // {
                //     "type": "link",
                //     "url": "https://apiok.ru"
                // }
                {
                    "type": "app",
                    "text": message,
                    "images": [
                        {
                            "url": url,
                            "mark": "",
                            "title": "Вязаный мир"
                        }
                    ]
                    // },
                    // {
                    //     "type": "app-ref",
                    //     "appId": '1248696832'
                }
            ]
        }, false);
    };
};

function API_callback(method, result, data) {
    console.log("Method "+method+" finished with result "+result+", "+data);
    if (method == "showConfirmation" && result == "ok") {
        //FAPI.Client.call(feedPostingObject, function(status, data, error) {
        //    console.log(status + "   " + data + " " + error["error_msg"]);
        //}, data);
    } else if (method == "showPayment" && result == "ok") {
        document.getElementById("selo_game").onPaymentCallback(result);
    } else if (method == 'postMediatopic') {
        if (result == 'ok') {
            document.getElementById("selo_game").wallPostSave();
        } else {
            document.getElementById("selo_game").wallPostCancel();
        }
    } else if (method == 'getPageInfo') {
        var d = JSON.parse(data);
        var h = parseInt(d.clientHeight);
        // sn.setPageHeight(parseInt(d.clientWidth), h);
        SeloNinjaOK.initOK(h-40);
    } else {
        console.log('API_callback data:');
        console.log(data);
    }
}

//{"clientWidth":1766,"clientHeight":821,"scrollLeft":0,"scrollTop":0,"offsetLeft":0,"offsetTop":76,"innerHeight":821,"innerWidth":1781}
//h: 649
//{"clientWidth":1319,"clientHeight":715,"scrollLeft":0,"scrollTop":0,"offsetLeft":0,"offsetTop":76,"innerHeight":715,"innerWidth":1334}
//init h: 677
//h: 649
//{"clientWidth":1319,"clientHeight":946,"scrollLeft":0,"scrollTop":0,"offsetLeft":0,"offsetTop":76,"innerHeight":946,"innerWidth":1334}