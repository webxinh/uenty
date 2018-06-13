CKEDITOR.plugins.add('imagebrowse',
    {
	icons: 'imagebrowse',
	init: function (editor) {
	    editor.addCommand('imagebrowse', new CKEDITOR.dialogCommand('imagebrowseDialog'));
            editor.ui.addButton('ImageBrowse',
                {
                    label: 'Chèn ảnh',
                    command: 'imagebrowse',
                    toolbar: 'insert'
                });
            CKEDITOR.dialog.add( 'imagebrowseDialog', this.path + 'dialogs/imagebrowse.js?123' );
        }
    });