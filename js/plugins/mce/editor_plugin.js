//TODO: delete this file and folder

(function () {
  tinymce.PluginManager.add('amoforms', function (editor/*, url*/) {

    // Add select
    editor.addButton('amoforms_add_form', {
      type: 'listbox',
      text: "amoForm",
      classes: 'amoform_add_btn',
      values: editor.settings['amoforms']['forms'],
      onselect: function () {
        //insert key
        //editor.insertContent(this.value());
        tinyMCE.activeEditor.execCommand('mceInsertContent', false, '[amoforms id="' + this.value() + '"]');
        //reset selected value
        this.value(null);
      }
    });
  });
})();
