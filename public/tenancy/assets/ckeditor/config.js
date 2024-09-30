/**
 * @license Copyright (c) 2003-2021, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */
var placeholders =  ['customers.name','customers.address','customers.pincode','customers.country_name','customers.state_name','customers.city_name','companies.name','companies.company_name','companies.address','companies.pincode','companies.country_name','companies.state_name','companies.city_name','estimates.estimate_no','estimates.estimate_date'];
// $.ajax({
//     url: 'getvalues.php',
//     async: false,
//     dataType: 'json',
//     success: function(data) {
//         placeholders =  data;
//     }
// });

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
    /*config.toolbar = [
        // { name: 'document', groups: [ 'mode', 'document', 'doctools' ], items: [ 'Source', '-', 'Save', 'NewPage', 'Preview', 'Print', '-', 'Templates' ] },
        // { name: 'clipboard', groups: [ 'clipboard', 'undo' ], items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
        // { name: 'editing', groups: [ 'find', 'selection', 'spellchecker' ], items: [ 'Find', 'Replace', '-', 'SelectAll', '-', 'Scayt' ] },
        // { name: 'forms', items: [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField' ] },
        '/',
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting', 'RemoveFormat' ] },
        { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language' ] },
        { name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
        // { name: 'insert', items: [ 'Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe' ] },
        { name: 'insert', items: [ 'Image', 'Flash', 'Table' ] },
        '/',
        { name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
        { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
        { name: 'tools', items: [ 'Maximize', 'ShowBlocks' ] },
        { name: 'others', items: [ '-' ] },
        { name: 'about', items: [ 'About' ] }
    ];*/
    config.toolbarGroups = [
        // { name: 'clipboard',   groups: [ 'undo' ]}, //'clipboard',
        // { name: 'editing',  groups: [ 'find', 'selection', 'spellchecker' ] },
        { name: 'links' },
        { name: 'insert', groups: [ 'Image', 'Flash', 'Table' ] },
        // { name: 'insert' },
        // { name: 'forms' },
        { name: 'tools' },
        { name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
        { name: 'others' },
        // '/',
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
        { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
        { name: 'styles' },

        { name: 'colors' },
        // { name: 'about' },
        { name: 'holders', groups: ['placeholder_select']}
    ];
    config.removeButtons = 'Underline,Subscript,Superscript';

    // Set the most common block elements.
    config.format_tags = 'p;h1;h2;h3;pre';

    // Simplify the dialog windows.
    // config.removeDialogTabs = 'image:advanced;link:advanced';

    // Placeholders
    config.placeholder_select = {
        // placeholders: placeholders,
        format: '${%placeholder%}'
    };

    config.extraPlugins = 'richcombo,placeholder_select,sourcedialog,popup';
    // config.placeholder_select= {
    //     placeholders: ['First','last']
    // };
};
CKEDITOR.on( 'dialogDefinition', function( ev ) {
    // Take the dialog name and its definition from the event data.
    var dialogName = ev.data.name;
    var dialogDefinition = ev.data.definition;
    // Check if the definition is from the dialog we're
    // interested in (the "Table" dialog).
    if ( dialogName == 'table' ) {
        // Get a reference to the "Table Info" tab.
        var infoTab = dialogDefinition.getContents( 'info' );
        txtWidth = infoTab.get( 'txtWidth' );
        txtWidth['default'] = '100%';
        var cellSpacing = infoTab.get('txtCellSpace');
        cellSpacing['default'] = "0";
        var cellPadding = infoTab.get('txtCellPad');
        cellPadding['default'] = "1";
        var border = infoTab.get('txtBorder');
        border['default'] = "1";
    }
});
