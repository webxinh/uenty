CKEDITOR.dialog.add('imagebrowseDialog', function (editor) {

    // $('body').css({'overflow':'hidden'});

    return {
        title: 'Chọn ảnh',
        minWidth: 885,
        minHeight: 450,
        contents: [
            {
                id: 'tab-products',
                label: 'Sản phẩm trong thư viện',
                elements: [
                    {
                        type: 'text',
                        id: 'selected-product-image',
                        style: 'display: none',
                        className: 'selected-product-image'
                    },
                    {
                        type: 'text',
                        id: 'selected-thumb-type-product-image',
                        style: 'display: none',
                        className: 'selected-thumb-type-product-image'
                    },
                    {
                        type: 'html',
                        html: '<a href="javascript: void(0);" class="cke-product-images"></a>'
                    }
                ]
            },
            // {
            //     id: 'tab-media-files',
            //     label: 'File',
            //     elements: [
            //         {
            //             type: 'text',
            //             id: 'selected-media-file',
            //             style: 'display: none',
            //             className: 'selected-media-file'
            //         },
            //         {
            //             type: 'text',
            //             id: 'selected-thumb-type-media-file',
            //             style: 'display: none',
            //             className: 'selected-thumb-type-media-file'
            //         },
            //         {
            //             type: 'html',
            //             id: 'media-files',
            //             html: '<div href="javascript: void(0);" class="cke-all-images"></div>'
            //         }
            //     ]
            // },
            {
                id: 'tab-image-url',
                label: "Ảnh từ đường dẫn",
                elements: [
                    {
                        type: 'text',
                        id: 'image-url',
                        label: "Đường dẫn ảnh"
                    }
                ]
            },
            // {
            //     id: 'tab-image-upload',
            //     label: "Tải lên",
            //     elements: [
            //         {
            //             type: 'file',
            //             id: 'image-file'
            //         },
            //         {
            //             type: 'select',
            //             id: 'thumb-type',
            //             className: 'thumb-type',
            //             label: 'Kích thước ảnh',
            //             labelStyle: 'font-weight: bold; margin-top: 15px;',
            //             items: [
            //                 ['Original', 'original'],
            //                 ['Pico (16x16)', 'pico'],
            //                 ['Icon (32x32)', 'icon'],
            //                 ['Thumb (50x50)', 'thumb'],
            //                 ['Small (100x100)', 'small'],
            //                 ['Compact (160x160)', 'compact'],
            //                 ['Medium (240x240)', 'medium'],
            //                 ['Large (480x480)', 'large'],
            //                 ['Grande (600x600)', 'grande'],
            //                 ['1024x1024 (1024x1024)', '1024x1024'],
            //                 ['2048x2048 (2048x2048)', '2048x2048'],
            //             ],
            //             'default': 'original'
            //         }
            //     ]
            // }
        ],
        onShow: function () { 
            // var classname = $(this._.element).attr('class');
            // classname = '.'+classname.replace(/ /g, ".");
            // classname = classname.substring(0, (classname.length - 1));
            $('body>.cke_reset_all.cke_editor').show();
            var url = 'http://'+window.location.hostname
            url = url+'/ad/image/ga'            
            $.ajax({
            url: url,
                success: function (data) {                
                    data = gmcode(data);
                    $(".cke-product-images").html(data);
                }
            });
        },
        onHide: function () {
            // var classname = $(this._.element).attr('class');
            // classname = '.'+classname.replace(/ /g, ".");
            // classname = classname.substring(0, (classname.length - 1));
            // $(classname).remove();

            $('.cke_dialog_title').html('Chọn ảnh');            
            $('body>.cke_reset_all.cke_editor').remove();             
        },
        onOk: function () {
            var dialog = this;
            var CurrObj = CKEDITOR.dialog.getCurrent();
            var currTab = CurrObj.definition.dialog._.currentTabId;

            var image = editor.document.createElement('img');

            if (currTab == "tab-media-files") {
                // var listSrc = this.getValueOf('tab-media-files', 'selected-media-file');
                
                // var thumbType = this.getValueOf('tab-media-files', 'selected-thumb-type-media-file');
                // if (thumbType == "") {
                //     thumbType = "original";
                // }

                // var sources = listSrc.split(",");
                // for (var i = 0; i < sources.length; i++) {
                //     var src = changeSrcByThumbType(sources[i], thumbType);
                //     if (src != "") {
                //         var img = editor.document.createElement('img');
                //         img.setAttribute('src', src);
                //         editor.insertElement(img);
                //     }
                // }

            } else if (currTab == "tab-products") {
                var listSrc = this.getValueOf('tab-products', 'selected-product-image');
                
                var sources = listSrc.split(",");
                for (var i = 0; i < sources.length; i++) {                    
                    var src = sources[i];                    
                    if (src != "") {
                        src = src.replace('//','/')
                        var url = 'http://'+window.location.hostname                       
                        src = url + src

                        var img = editor.document.createElement('img');
                        img.setAttribute('src', src);
                        editor.insertElement(img);

                        var br = editor.document.createElement('br');                        
                        editor.insertElement(br);                                              
                    }
                }
                $(".selected-product-image input").val('');

            } else if (currTab == "tab-image-url") {
                var src = this.getValueOf('tab-image-url', 'image-url');
                if (src != "") {
                    image.setAttribute('src', src);
                    editor.insertElement(image);
                }
            } else if (currTab == "tab-image-upload") {
                // var thumbType = this.getValueOf('tab-image-upload', 'thumb-type');
                // var fileElement = this.getContentElement('tab-image-upload', 'image-file').getInputElement().$;

                // if (fileElement.files.length > 0) {
                //     var file = fileElement.files[0];
                //     if (file.size > 1048576) {
                //         alert("Kích thước file tối đa được upload là 1MB.");
                //         return false;
                //     }
                //     if (!file.name.toLowerCase().match(/\.(jpg|jpeg|png|gif)$/)) {
                //         alert("File upload không đúng định dạng.");
                //         return false;
                //     }

                //     var formData = new FormData();
                //     formData.append("mediaImages", file);
                //     $.ajax({
                //         type: "POST",
                //         url: '/admin/settings/files/upload',
                //         data: formData,
                //         contentType: false,
                //         processData: false,
                //         success: function (response) {
                //             if (Object.prototype.toString.call(response) === '[object Array]' && response.length > 0) {
                //                 image.setAttribute('src', changeSrcByThumbType(response[0].src, thumbType));
                //                 editor.insertElement(image);
                //                 CKEDITOR.dialog.getCurrent().hide();
                //             }
                //             else if (response.error) {
                //                 alert(response.error);
                //             }
                //             else {
                //                 alert("Đã có lỗi xảy ra. Tải lên thất bại.")
                //             }
                //         },
                //         error: function (error) {
                //             alert("Đã có lỗi xảy ra. Tải lên thất bại.")
                //         }
                //     });
                // }

                return false;
            }
            // alert('đóng ok');
        } //end ok
    };
});