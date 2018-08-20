jQuery.entwine("saltedcroppable", function($) {

    $("input.croppableimage").entwine({
        Loading: null,
        Dialog: null,
        URL: null,
        onmatch: function() {
            var self = this;
            this.setDialog(self.siblings('.salted-croppable-dialog:first'));

            var form = this.parents('form');
                formUrl = form.attr('action'),
                formUrlParts = formUrl.split('?'),
                formUrl = formUrlParts[0],
                url = encodeURI(formUrl) + 'field/' + this.attr('name') + '/CroppableImageFormHTML';

            if (self.val().length){
                url = url + '?SaltedCroppableImageID=' + self.val();
            }else{
                url = url + '?SaltedCroppableImageID=0';
            }

            if(typeof formUrlParts[1] !== 'undefined') {
                url = url + '&' + formUrlParts[1];
            }

            this.setURL(url);

            // configure the dialog
            var windowHeight = $(window).height();

            this.getDialog().data("field", this).dialog({
                autoOpen: false,
                width: $(window).width()    * 80 / 100,
                height: $(window).height() * 80 / 100,
                modal: true,
                title: this.data('dialog-title'),
                position: { my: "center", at: "center", of: window }
            });

            // submit button loading state while form is submitting
            this.getDialog().on("click", "button", function() {
                $(this).addClass("loading ui-state-disabled");
            });

            // handle dialog form submission
            this.getDialog().on("submit", "form", function() {

                var dlg = self.getDialog().dialog(),
                    options = {};

                options.success = function(response) {
                    if($(response).is(".field")) {
                        self.getDialog().empty().dialog("close");
                        self.parents('.field:first').replaceWith(response);
                        form.addClass('changed');
                    } else {
                        self.getDialog().html(response);
                    }
                }

                $(this).ajaxSubmit(options);

                return false;
            });
        },

        onunmatch: function () {
            var self = this;
            $('.salted-croppable-dialog.ui-dialog-content').filter(function(){
                return self[0] == $(this).data("field")[0];
            }).remove();
        },

        showDialog: function(url) {

            var dlg = this.getDialog();

            dlg.empty().dialog("open").parent().addClass("loading");

            dlg.load(this.getURL(), function(){
                dlg.parent().removeClass("loading");
            });
        }
    });

    $('div.ss-upload .ss-uploadfield-item-remove, .ss-uploadfield-item-delete').entwine({
        onclick: function(e) {
            var field = this.closest('div.ss-upload'),
                fileupload = field.data('fileupload'),
                item = this.closest('.ss-uploadfield-item'), msg = '';

            if(this.is('.ss-uploadfield-item-delete')) {
                if(confirm(ss.i18n._t('UploadField.ConfirmDelete'))) {
                    this.parents('fieldset:eq(0)').find('.salted-cropper').hide();
                    if (fileupload) {
                        fileupload._trigger('destroy', e, {
                            context: item,
                            url: this.data('href'),
                            type: 'get',
                            dataType: fileupload.options.dataType
                        });
                    }
                }
            } else {
                // Removed files will be applied to object on save
                this.parents('fieldset:eq(0)').find('.salted-cropper').hide();
                if (fileupload) {
                    fileupload._trigger('destroy', e, {context: item});
                }
            }

            e.preventDefault(); // Avoid a form submit
            return false;
        }
    });

    $(".croppable-image-field-button").entwine({
        onclick: function() {
            this.siblings('input.croppableimage').showDialog();
            return false;
        },
    });

    $(".croppable-image-field-remove-button").entwine({
        onclick: function() {
            var self    =   this;
                id      =   self.data('id');
            // url = url + '?SaltedCroppableImageID=' + self.val();
            if (confirm('You are going to delete this image. Are you sure')) {
                var form = this.parents('form');
                var formUrl = form.attr('action'),
                    formUrlParts = formUrl.split('?'),
                    formUrl = formUrlParts[0],
                    url = encodeURI(formUrl) + 'field/' + this.parents('.form__field-holder:first').find('input:first').prop('name') + '/doRemoveCroppableImage?id=' + id;

                if(typeof formUrlParts[1] !== 'undefined') {
                    url = url + '&' + formUrlParts[1];
                }
                var holder = this.parents('.field:first');
                this.parents('.middleColumn:first').html("<img src='framework/images/network-save.gif' />");
                holder.load(url, function() {
                     form.addClass('changed');
                });
            }

            return false;
        }
    });

    $('.salted-croppable-dialog .uploadfield__upload-button').entwine(
    {
        onmatch :   function(e)
                    {
                        var muppet  =   $(this).parents('.form__field-holder:eq(0)').find('input.entwine-uploadfield'),
                            real    =   $('.dz-input-' + muppet.data('schema').name);

                        real.insertAfter(muppet);
                    }
    });
});
