$(function () {
    // 最大画像ファイル件数
    var max_image_len = 4;

    function initUploadArea(postId) {

        var $post = $("#" + postId);
        var $attachments = $post.find('.attachments-edit ul');

        if (!$attachments.length)
            return;

        chkImgCounts($post);

        var uploader = new qq.FineUploaderBasic({
            debug: true,
            button: $post.find('.attachments-button')[0],
            request: {
                endpoint: ET.webPath + '/?p=attachment/upload',
                params: {
                    postId: postId == "reply" ? (ETConversation.id ? "c" + ETConversation.id : "c0") : postId
                }
            },
            paste: {
                defaultName: 'pasted_image',
                targetElement: $post.find('textarea[name="content"]')[0]
            },
            callbacks: {
                onSubmit: function (id, fileName) {
                    $attachments.append('<li id="file-' + postId + '-' + id + '"></li>');
                    $attachments.parent().addClass('has-attachments');
                },
                onUpload: function (id, fileName) {
                    // 現在の画像件数
                    var imgCnt = getImgCounts($post);
                    // 添付可能件数
                    var cnt = max_image_len - (imgCnt+1);
                    if (cnt >= 0) {
                        // 4件まで登録可
                        $('#file-' + postId + '-' + id).addClass('attachment-uploading')
                                .html('Initializing “' + fileName + '”...');
                        // 画像件数カウントアップ
                        $post.find('div.attachments-edit .imgCount').val(imgCnt+1).trigger("change");
                    } else {
                        // 件数超過時 アップロード処理キャンセル
                        this.cancel(id);
                        alertMaxFilesMsg();
                    }
                    
                },
                onProgress: function (id, fileName, loaded, total) {
                    if (loaded < total) {
                        progress = Math.round(loaded / total * 100) + '% of ' + Math.round(total / 1024) + ' kB';
                        $('#file-' + postId + '-' + id).html('Uploading “' + fileName + '” ' + progress);
                    } else {
                        $('#file-' + postId + '-' + id).html('Saving “' + fileName + '”...');
                    }
                },
                onComplete: function (id, fileName, responseJSON) {
                    if (responseJSON.success) {
                        $('#file-' + postId + '-' + id).removeClass('attachment-uploading')
                                .html('<a href="#" class="control-delete" title=' + T("Delete") + ' data-id="' + responseJSON.attachmentId + '"><i class="icon-remove"></i></a> <strong>' + fileName + '</strong> <span class="attachment-controls"><a href="#" class="control-embed" title="' + T("Embed in post") + '" data-id="' + responseJSON.attachmentId + '"><i class="icon-external-link"></i></a></span>');
                        // テキスト貼付け
                        ETConversation.insertText($post.find("textarea"), "[attachment:" + responseJSON.attachmentId + "]\n");
                    
                    } else {
                        $('#file-' + postId + '-' + id).remove();
                        ETMessages.showMessage('Error uploading "' + fileName + '": ' + responseJSON.error, {className: "warning dismissable", id: "attachmentUploadError"});
                    }
                }
            }
        });

        var dragAndDropModule = new qq.DragAndDrop({
            dropZoneElements: [$post.find(".dropZone")[0]],
            classes: {
                dropActive: "dropZoneActive"
            },
            hideDropZonesBeforeEnter: true,
            callbacks: {
                processingDroppedFiles: function () {
                    //TODO: display some sort of a "processing" or spinner graphic
                },
                processingDroppedFilesComplete: function (files) {
                    //TODO: hide spinner/processing graphic
                    // ドラッグ＆ドロップ時のファイル件数チェック
                    var imgCnt = getImgCounts($post);
                    var cnt = max_image_len - imgCnt;
                    if (cnt > 0) {
                        // 添付可能件数が1以上
                        if (files.length > cnt) {
                            // 超過する場合
                            // 上から順に範囲内数のみアップロードする
                            files = files.slice(0, cnt);
                        }
                        uploader.addFiles(files); //this submits the dropped files to Fine Uploader
                    } else {
                        alertMaxFilesMsg();
                    }
                }
            }
        });

        $post.on("click", ".attachments-edit .control-delete", function (e) {
            e.preventDefault();
            $.ETAjax({
                url: "attachment/remove/" + $(this).data("id")
            });
            $(this).parent().remove();
            if (!$attachments.find('li').length) {
                $attachments.parent().removeClass('has-attachments');
            }
            var imgCnt = getImgCounts($post);
            if (imgCnt && imgCnt > 0) {
                $post.find('div.attachments-edit .imgCount').val(Number(imgCnt) - 1).trigger("change");
            }

        });

        $post.on("click", ".attachments-edit .control-embed", function (e) {
            e.preventDefault();
            ETConversation.insertText($post.find("textarea"), "[attachment:" + $(this).data("id") + "]");
        });

        $post.find('div.attachments-edit .imgCount').on("change", function () {
            var cnt = $(this).val();
            displayAttachmentBtn($post, Number(cnt));
        });

        // 初期表示時
        var imgCnt = getImgCounts($post);
        displayAttachmentBtn($post, imgCnt);
    }

    function alertMaxFilesMsg() {
        alert("画像ファイルは4件まで登録可能です。");
    }
    
    function displayAttachmentBtn($post, cnt) {
        if (cnt < max_image_len) {
            $post.find('.attachments-button').css("display", "block");
        } else {
            // max件数超過の場合 ボタン非表示
            $post.find('.attachments-button').css("display", "none");
        }
    }
    
    /**
     * $post JQオブジェクトの画像ファイル数を取得
     * @param {type} $post
     * @returns {unresolved}
     */
    function getImgCounts($post) {
        var cnt = $post.find('div.attachments-edit .imgCount').val();
        return Number(cnt);
    }
    
    /**
     * $post JQオブジェクトの画像ファイル数チェック処理
     *  画像ファイルボタンのクリック時に、画像ファイル数をチェックし、
     *  max値（4件）未満の場合、true を返す
     * @param {type} $post
     * @returns {undefined}
     */
    function chkImgCounts($post) {
        $post.find('.attachments-button').on("click", function () {
            var imgCnt = getImgCounts($post);
            if (imgCnt < max_image_len) {
                return true;
            } else {
                return false;
            }
        });
    }

    initUploadArea("reply");

    var updateEditPost = ETConversation.updateEditPost;
    ETConversation.updateEditPost = function (postId, html) {
        updateEditPost(postId, html);
        initUploadArea("p" + postId);
    };

    var resetReply = ETConversation.resetReply;
    ETConversation.resetReply = function () {
        resetReply();
        $("#reply .attachments-edit ul").html("");
        $('#reply div.attachments-edit .imgCount').val(0);
    };

    var cancelEditPost = ETConversation.cancelEditPost;
    ETConversation.cancelEditPost = function (postId) {
        cancelEditPost(postId);
        $.ETAjax({
            url: "attachment/removeSession/" + postId
        });
    };

});
