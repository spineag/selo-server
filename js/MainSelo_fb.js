"use strict";

var SN = function (social) { // social == 4
    var that = this;
    var accessT = '';
    var uSocialId = '';
    var browserName = '0';
    var versionBrowser = '0';
    var OS = '0';
    var dataBrowser = [ { string: navigator.userAgent, subString: "Chrome", identity: "Chrome" },
                        { string: navigator.userAgent, subString: "OmniWeb", versionSearch: "OmniWeb/", identity: "OmniWeb" },
                        { string: navigator.vendor, subString: "Apple", identity: "Safari", versionSearch: "Version" },
                        { prop: window.opera, identity: "Opera", versionSearch: "Version" },
                        { string: navigator.vendor, subString: "iCab", identity: "iCab" },
                        { string: navigator.vendor, subString: "KDE",  identity: "Konqueror" },
                        { string: navigator.userAgent, subString: "Firefox", identity: "Firefox" },
                        { string: navigator.vendor, subString: "Camino", identity: "Camino" },
                        { string: navigator.userAgent, subString: "Netscape", identity: "Netscape" }, /* For Newer Netscapes (6+) */
                        { string: navigator.userAgent, subString: "MSIE", identity: "Internet Explorer", versionSearch: "MSIE" },
                        { string: navigator.userAgent, subString: "Gecko", identity: "Mozilla", versionSearch: "rv" },
                        { string: navigator.userAgent, subString: "Mozilla", identity: "Netscape", versionSearch: "Mozilla"} ];  /* For Older Netscapes (4-) */
    var dataOS = [  { string: navigator.platform, subString: "Win", identity: "Windows" },
                    { string: navigator.platform, subString: "Mac", identity: "Mac" },
                    { string: navigator.userAgent, subString: "iPhone", identity: "iPhone/iPod" },
                    { string: navigator.platform, subString: "Linux", identity: "Linux" } ];

    console.log('init fb social');

    window.fbAsyncInit = function() {
        FB.init({
            appId      : '105089583507105',
            xfbml      : true,
            cookie     : true,
            status     : true,
            version    : 'v3.0'
        });
        FB.AppEvents.logPageView();
        FB.login(function(response) {
            console.log(response);
            if (response.authResponse) {
                accessT = response.authResponse.accessToken;
                uSocialId = response.authResponse.userID;
                try {
                    console.log('userSocialId: ' + uSocialId);
                    SeloNinjaFB.getVersion(uSocialId);
                } catch(err) {
                    console.log('after init FB:: error with getVersion: ' + err);
                }
                try {
                    that.findBrowser();
                } catch(err) {
                    console.log('findBrowser error: ' + err);
                }
            } else {
                console.log('not auth');
            }
        // }, {scope:'user_friends', return_scopes: true});
        });
    };

    (function(d, s, id){
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {return;}
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    that.flash = function(){
        return document.getElementById("selo_game");
    };

    that.findBrowser = function() {
        browserName = that.searchString(dataBrowser) || "An unknown browser";
        versionBrowser = that.searchVersion(navigator.userAgent) || that.searchVersion(navigator.appVersion) || "an unknown version";
        OS = that.searchString(dataOS) || "an unknown OS";
        // console.log('browser: ' + browserName);
        // console.log('browser version: ' + versionBrowser);
        // console.log('OS: ' + OS);
    };

    that.searchString = function (data) {
        for (var i=0;i<data.length;i++) {
            var dataString = data[i].string;
            var dataProp = data[i].prop;
            this.versionSearchString = data[i].versionSearch || data[i].identity;
            if (dataString) {
                if (dataString.indexOf(data[i].subString) != -1)
                    return data[i].identity;
            } else if (dataProp)
                return data[i].identity;
        }
    };

    that.searchVersion = function(dataString) {
        var index = dataString.indexOf(this.versionSearchString);
        if (index == -1) return;
        return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
    };

    that.getProfile = function(userSocialId) {
        FB.api("/me",
            {access_token: accessT},
            function (response) {
                console.log('/me response:');
                console.log(response);
                if (response && !response.error) {
                    userSocialId = response.id;
                    var u = {};
                    FB.api("/" + userSocialId,
                        {access_token: accessT},
                        {fields: 'last_name,first_name,gender,birthday,picture.width(100).height(100),locale,timezone'},
                        function (response) {
                            console.log('getProfile (/userSocialId) callback:');
                            console.log(response);
                            if (response && !response.error) {
                                u.first_name = response.first_name;
                                u.last_name = response.last_name;
                                u.gender = response.gender;
                                u.birthday = response.birthday;
                                u.locale = response.locale;
                                u.picture = response.picture.data.url;
                                u.id = userSocialId;
                                if (response.timezone) {
                                    var t = Number(response.timezone);
                                    if (t < -12) t = t + 24;
                                    if (t > 12) t = t - 24;
                                    u.timezone = t;
                                } else {
                                    u.timezone = 0;
                                }
                                if (u.locale == 'ru_RU' || u.locale == 'be_BY' || u.locale == 'uk_UA') {
                                    SeloNinjaFB.setLanguage(1);
                                } else {
                                    SeloNinjaFB.setLanguage(2);
                                }
                                try {
                                    that.flash().getProfileHandler(u);
                                } catch (err) {
                                    console.log('getProfileHandler error: ' + err)
                                }
                                console.log('locale: ' + response.locale);
                            }
                        }
                    );
                }
            }
        );
    };

    that.getAllFriends = function(userSocialId) {
        FB.api("/" + userSocialId + "/friends",
            {fields: 'id,last_name,first_name,picture.width(100).height(100)'},
            function (response) {
                console.log('getAllFriends response: ', response);
                if (response) {
                    try {
                        that.flash().getAllFriendsHandler(response);
                    } catch (err) {
                        console.log('getAllFriendsHandler error: ' + err)
                    }
                }
            }
        );
    };

    that.getTempUsersInfoById = function(uids) {
        var ids = uids.join();
        FB.api("/ids=" + ids,
            {fields: 'id,last_name,first_name,picture.width(100).height(100)'},
            function (response) {
                if (response) {
                    try {
                        that.flash().getTempUsersInfoByIdHandler(response);
                    } catch (err) {
                        console.log('getTempUsersInfoById error: ' + err);
                        console.log(response);
                    }
                }
            }
        );
    };

    that.getAppUsers = function(userSocialId) {
        FB.api("/105089583507105",
            {"fields": "context.fields(friends_using_app)"},
            function (response) {
                console.log('getAppFriends response: ', response);
                if (response) {
                    try {
                        that.flash().getAppUsersHandler(response);
                    } catch (err) {
                        console.log('getAppUsersHandler error: ' + err);
                        console.log(response);
                    }
                }
            }
        );
    };

    that.getFriendsByIds = function(uids) {
        var ids = uids.join();
        FB.api('/?ids='+ids,
            {fields: 'id,last_name,first_name,picture.width(100).height(100)'},
            function (response) {
                if (response && !response.error) {
                    try {
                        that.flash().getFriendsByIdsHandler(response);
                    } catch (err) {
                        console.log('getFriendsByIdsHandler error: ' + err);
                        console.log(response);
                    }
                }
            }
        );
    };

    that.showInviteWindowAll = function(lang) {
        FB.ui({method: 'apprequests',
            message: "Let's play together!",
            filters: ["app_non_users"]
        }, function(response){
            console.log(response);
        });
    };

    that.showInviteWindowViral = function() {
        FB.ui({method: 'apprequests',
            message: "Let's play together!",
            filters: ["app_non_users"],
            max_recipients: 20
        }, function(response){
            console.log(response);
            if (response.to) {
                that.flash().onViralInvite(response.to);
            } else {
                that.flash().onViralInvite([]);
            }
        });
    };

    that.makeWallPost = function(url){
        // FB.api('me/feed',
        //     'post',
        //     {   message: '',
        //         picture :url,
        //         description : message,
        //         name: 'WoollyWorld',
        //         link: 'https://apps.facebook.com/105089583507105/'
        //     }, function(response) {
        //         console.log(response);
        //         if (response && !response.error) {
        //             try {
        //                 that.flash().wallPostSave();
        //             } catch (err) {
        //                 console.log('wallPostSave error: ' + err);
        //                 console.log(response);
        //             }
        //         } else {
        //             try {
        //                 that.flash().wallPostCancel();
        //             } catch (err) {
        //                 console.log('wallPostCancel error: ' + err);
        //                 console.log(response);
        //             }
        //         }
        //     }
        // );
        FB.ui({
            method: 'share',
            href: url
        }, function(response){
            if (response && !response.error) {
                try {
                    that.flash().wallPostSave();
                } catch (err) {
                    console.log('wallPostSave error: ' + err);
                    console.log(response);
                }
            } else {
                try {
                    that.flash().wallPostCancel();
                } catch (err) {
                    console.log('wallPostCancel error2: ' + err);
                    console.log(response);
                }
            }
        });
    };

    that.isInGroup = function(groupId, userId) {
        that.flash().isInGroupCallback(1);
        // FB.api(     ---> better use groupId/members?limit=400 and check all users
        //     "/" + userId + "/groups",
        //     function (response) {
        //         var status = 0;
        //         if (response && !response.error) {
        //             status = 1;
        //         }
        //         that.flash().isInGroupCallback(status);
        //     }
        // );

    };

    that.makePayment = function(packStr, userSocialId) {
        if (packStr == 'item7' || packStr == 'item8' || packStr == 'item9' || packStr == 'item10' || packStr == 'item11' || packStr == 'item12') var product = "https://505.ninja/selo-project/php/api-v1-0/payment/fb/" + packStr + "a.html";
        else var product = "https://505.ninja/selo-project/php/api-v1-0/payment/fb/" + packStr + ".html";
        var requestID = String(userSocialId) + 'z' + String(Date.now());
        console.log('payment product: ' + product);
        SeloNinjaFB.saveTransaction(userSocialId, packStr, requestID, browserName, versionBrowser, OS);
        FB.ui({
            method: 'pay',
            action: 'purchaseitem',
            product: product,
            request_id: requestID
        }, function (response) {
            console.log('Payment completed', response);
            if (response.status) {
                if (response.status == 'completed') {
                    that.flash().successPayment();
                    SeloNinjaFB.finishTransaction(requestID, 'complete');
                } else if (response.status == 'initiated') {
                    console.log('payment initiated status');
                } else if (response.status == 'failed') {
                    that.flash().failPayment();
                    SeloNinjaFB.finishTransaction(requestID, 'failed');
                } else {
                    console.log('response.status: ' + response.status);
                    that.flash().failPayment();
                    SeloNinjaFB.finishTransaction(requestID, response.status);
                }
            } else if (response.error_code) {
                that.flash().failPayment();
                SeloNinjaFB.finishTransaction(requestID, response.error_code + ': ' +response.error_message);
            } else {
                that.flash().failPayment();
                SeloNinjaFB.finishTransaction(requestID, 'cancel');
            }
        });
    }
};


