var SeloNinja = {
    user_sid: false,
    swf: {},
    version: -1,
    channel: 2,
    language: 1,

    init: function () {
        var url = document.location.toString().split('?');
        var flashvars = {
            data: (url[1] ? '&' + url[1] : ''),
            protocol: (document.location.protocol == 'https:') ? 'https' : 'http',
            channel: this.channel,
            gacid: this.getUserGAcid()
        };

        var params = {
            allowFullscreen: "true",
            allowFullScreenInteractive: "true",
            allowScriptAccess: "always",
            wmode: "direct",
            flashvars: flashvars
        };
        var attributes = {
            id: "selo_game",
            name: "selo_game"
        };
        if (this.version == -1) {
            var st = "channelId=2";
            var arrStr = params.flashvars.data.split("&");
            var t = '0';
            for (var i = 0; i < arrStr.length; i++) {
                t = arrStr[i];
                if (t.indexOf('viewer_id=') != -1) { t = t.substring(10); st = st + "&userSocialId=" + t;  break;  }
            }
            $.ajax({
                type: 'post',
                url: '../php/api-v1-0/getVersionClient.php',
                data: st,
                response: 'text',
                success: function (v) {
                    this.version = v;
                    if (t !=0) console.log('userSocialID: ' + t);
                    console.log('current version: ' + v);
                    if (this.version == '0') {
                        $('#gameContainer').html('<div id="flash_container">' +
                            '<div id="404">' +
                            '<img src="https://505.ninja/selo-project/images/404/window404.png" alt="На ремонте" />' +
                            '</div>' +
                            '</div>');
                    } else swfobject.embedSWF('client/selo' + this.version + '.swf', 'flash_container', '100%', 640, '13.0', null, flashvars, params, attributes, this.callbackFn);
                },
                errrep: true,
                error: function (num) {
                    alert('error get client version');
                }
            });
        } else {
            swfobject.embedSWF('client/selo' + this.version + '.swf', 'flash_container', '100%', 640, '13.0', null, flashvars, params, attributes, this.callbackFn);
        }
    },

    callbackFn: function (e) {
        if (!e.success) {
            console.log('bad with load swf');
            $('#loader').css('display', 'none');
            $('#no_player').css('display', 'block');
        }
        else {
            document.getElementById("selo_game").style.display = "block";
        }
    },

    reload: function () {
        $('#gameContainer').html('<div id="flash_container">' +
            '<div id="loader">' +
            '<img src="/images/ajax-loader.gif" />' +
            '</div>' +
            '<div id="no_player">' +
            '<a target="_blank" href="http://www.adobe.com/go/getflashplayer">' +
            '<img src="https://505.ninja/selo-project/images/up_flash.jpg" alt="Get Adobe Flash player" />' +
            '</a>' +
            '</div>' +
            '</div>');
        this.init();
    },

    getUserGAcid: function () {
        var match = document.cookie.match('(?:^|;)\\s*_ga=([^;]*)');
        var raw = (match) ? decodeURIComponent(match[1]) : null;
        if (raw) {
            match = raw.match(/(\d+\.\d+)$/);
        } else return 'unknown';
        var gacid = (match) ? match[1] : null;
        if (gacid) {
            return gacid;
        } else return 'unknown';
    },

    getUserGAcidForAS: function () {
        var gacid = this.getUserGAcid();
        var flash =	document.getElementById("selo_game");
        flash.sendGAcidToAS(gacid);
    }
};
