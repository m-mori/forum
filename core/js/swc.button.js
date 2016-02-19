// いいねボタン JavaScript

var SwcButton = {
    // api url
    url: "/api/point.php",
    // TODO: timeout(ms)
    timeout: 30000,
    
    init: function (btn) {
        // カウント取得
        var cnt = $(btn).attr("data-cnt");
        var kind = $(this).attr("data-kind");
        var cid = $(this).attr("data-cid");
        if (!cnt) { 
            if (kind && cid) {
                // カウント（data-cnt）設定なしの場合
                // 初期表示処理呼び出し
                // 初期表示フラグ, 種別, 記事IDを設定
                var data = new Object();
                data["init"] = 1;
                data["kind"] = kind;
                data["cid"] = cid;
                $.ajax({
                    type: 'POST',
                    url: SwcButton.url,
                    dataType: 'json',
                    cache: false,
                    timeout: SwcButton.timeout, 
                    data: data,
                    beforeSend: function (xhr) {
                        // 非表示にする
                        SwcButton.changeBtnDisplay(btn, false);
                    },
                    complete: function (xhr, textStatus) {
                        // 表示
                        SwcButton.changeBtnDisplay(btn, 1);
                    },
                    success: function (data, textStatus, xhr) {
                        var ret = data["result"];
                        var liked = data["liked"];
                        var cnt = data["cnt"];
                        if (ret == 1 && liked == 1) {
                            // いいね済みの場合
                            SwcButton.changeBtnCount(btn, cnt);
                            SwcButton.changeBtnState(btn, 1);
                        } else {
                            // それ以外はデフォルト表示
                            // クリックイベント設定
                            SwcButton.registerEvent(btn);
                        }
                    },
                    error: function (xhr, textStatus, errorThrown) {
                        // 通信エラー時
                        alert("サーバとの通信に失敗しました");
                        SwcButton.changeBtnState(btnObj, false);
                    }
                });
            }
            
        } else {
            // カウント設定済みの場合
            // カウント設定
            SwcButton.changeBtnCount(btn, cnt);
            // いいね済みフラグ
            var liked = $(btn).attr("data-liked");
            // ボタン状態を設定
            SwcButton.changeBtnState(btn, liked);
        }
    },
    
    registerEvent: function (btn) {    
        // Add click handlers to all of the controls.
        $(btn).on("click", function (e) {
            e.preventDefault();
            // 被いいね対象ID
            var uids = $(this).attr("data-uids");
            var kind = $(this).attr("data-kind");
            var cid = $(this).attr("data-cid");
            var conid = $(this).attr("data-conid");
            var btnObj = this;
            if (uids && kind && cid) {
                var data = new Object();
                data["uids"] = uids;
                data["kind"] = kind;
                data["cid"] = cid;
                data["conid"] = conid;
                $.ajax({
                    type: 'POST',
                    url: SwcButton.url,
                    dataType: 'json',
                    cache: false,
                    timeout: SwcButton.timeout,  
                    data: data,
                    beforeSend: function (xhr) {
                        // 二重押し対策しておく
                        SwcButton.changeBtnState(btnObj, 1);
                    },
                    complete: function (xhr, textStatus) {
                    },
                    success: function (data, textStatus, xhr) {
                        // 成功時
                        var ret = data["result"];
                        if (ret == 1) {
                            // カウント反映
                            SwcButton.changeBtnCount(btnObj, data['cnt']);
                            
                        } else if (ret==0) {
                            // NG
                            var msg = data["error_msg"];
                            if (msg) {
                                alert(msg);
                            }
                            SwcButton.changeBtnState(btnObj, false);
                        } else if (ret==9) {
                            // 未ログイン ログイン画面へ遷移
                            var loginUrl = data["login_url"];
                            window.location.href=loginUrl;
                        }
                    },
                    error: function (xhr, textStatus, errorThrown) {
                        // 通信エラー時
                        alert("サーバとの通信に失敗しました");
                        SwcButton.changeBtnState(btnObj, false);
                    }
                });
            }
        });
    },
    
    /**
     * いいねカウント更新
     * @param {type} btnObj
     * @param {type} cnt
     * @returns {undefined}
     */
    changeBtnCount: function (btnObj, cnt){
        if (cnt) {
            $(btnObj).next('div').children('.arrow_box').text(cnt);
        }
    },
    
    /**
     * ボタン状態を変更
     * @param {type} btnObj いいねボタン
     * @param {type} flg いいね済みフラグ
     * @returns {undefined}
     */
    changeBtnState: function (btnObj, flg) {
        if (flg) {
            // liked-button
            $(btnObj).attr("class", "liked-button swc-like-btn");
            // イベント解除
            $(btnObj).off("click");
        } else {
            $(btnObj).attr("class", "general-button swc-like-btn");
            // イベントバインド
            SwcButton.registerEvent(btnObj);
        }
    },
    
    changeBtnDisplay: function (btnObj, flg) {
        if (flg) {
            // 表示
            $(btnObj).css("display", "block");
        } else {
            // 非表示
            $(btnObj).css("display", "none");
        }
    }
    
};
$(function () {
    $("div.swc-like-btn").each(function(idx, elm) {
        SwcButton.init(elm);
    });
});