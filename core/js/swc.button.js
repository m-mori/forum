// いいねボタン JavaScript

var SwcButton = {
    init: function () {
        // Add click handlers to all of the controls.
        $("div.swc-like-btn").on("click", function (e) {
            e.preventDefault();
            // 被いいね対象ID
            var uids = $(this).attr("data-uids");
            var kind = $(this).attr("data-kind");
            var cid = $(this).attr("data-cid");
            var btnObj = this;
            if (uids && kind && cid) {
                var data = new Object();
                data["uids"] = uids;
                data["kind"] = kind;
                data["cid"] = cid;
                $.ajax({
                    type: 'POST',
                    url: "/api/point.php",
                    dataType: 'json',
                    cache: false,
                    timeout: 30000,     // TODO: 
                    data: data,
                    beforeSend: function (xhr) {
                        SwcButton.changeBtnState(btnObj, 1);
                    },
                    complete: function (xhr, textStatus) {
                    },
                    success: function (data, textStatus, xhr) {
                        // 成功時
                        var ret = data["result"];
                        if (ret == 1) {
                            // OK
                            alert("結果 OK");
                            
                        } else if (ret==0) {
                            // NG
                            var msg = data["error_msg"];
                            if (msg) {
                                alert(msg);
                            }
                            SwcButton.changeBtnState(btnObj, false);
                        } else if (ret==9) {
                            // 未ログイン ログイン画面へ遷移
                            var url = data["login_url"];
                            window.location.href=url;
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
    
    changeBtnState: function (btnObj, flg) {
        if (flg) {
            // liked-button
            $(btnObj).attr("class", "liked-button swc-like-btn");
            $(btnObj).off("click");
        } else {
            $(btnObj).attr("class", "general-button swc-like-btn");
            // イベントバインド
            
        }
    },
    
};
$(function () {
    SwcButton.init();
});