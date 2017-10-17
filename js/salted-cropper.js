(function($)
{
    $.entwine('ss', function($)
    {
        $('.salted-cropper').entwine(
        {
            onmatch: function(e)
            {
                var parentWindow        =   $(parent.document);
                if ($(this).find('img').length > 0) {

                    var thisCropper     =   $(this),
                        thisForm        =   $(this).parents('form:eq(0)'),
                        thisBody        =   $(this).parents('body:eq(0)'),
                        thisSrcRaw      =   thisForm.attr('action').split('/');

                        thisSrcRaw.pop();
                        thisSrc         =   thisSrcRaw.join('/') + '/edit';

                    var thisFrame       =   parentWindow.find('iframe[src="' + thisSrc + '"]'),
                        thisBtnEdit     =   thisFrame.parents('li.ss-uploadfield-item:eq(0)').find('button.ss-uploadfield-item-edit'),
                        image           =   $(this).find('img')[0],
                        ratio           =   $(this).attr('data-cropper-ratio'),
                        minWidth        =   $(this).attr('data-min-width'),
                        minHeight       =   $(this).attr('data-min-height'),
                        name            =   $(this).attr('data-name'),
                        cords           =   {
                                                left    :   parseInt(thisForm.find('input[name="CropperX"]').val()),
                                                top     :   parseInt(thisForm.find('input[name="CropperY"]').val()),
                                                width   :   parseInt(thisForm.find('input[name="CropperWidth"]').val()),
                                                height  :   parseInt(thisForm.find('input[name="CropperHeight"]').val())
                                            },
                        cropper         =   new Cropper(image,
                                            {
                                                viewMode: 3,
                                                aspectRatio: ratio ? ratio : NaN,
                                                zoomable: false,
                                                minContainerWidth: minWidth,
                                                minContainerHeight: minHeight,
                                                crop: function(e)
                                                {
                                                    var x = Math.round(cropper.getCanvasData().left * -1),
                                                        y = Math.round(cropper.getCanvasData().top * -1),
                                                        w = Math.round(cropper.getCanvasData().width),
                                                        h = Math.round(cropper.getCanvasData().height),
                                                        cx = Math.round(cropper.getCropBoxData().left),
                                                        cy = Math.round(cropper.getCropBoxData().top),
                                                        cw = Math.round(cropper.getCropBoxData().width),
                                                        ch = Math.round(cropper.getCropBoxData().height);

                                                    thisForm.find('input[name="ContainerX"]').val(x);
                                                    thisForm.find('input[name="ContainerY"]').val(y);
                                                    thisForm.find('input[name="ContainerWidth"]').val(w);
                                                    thisForm.find('input[name="ContainerHeight"]').val(h);

                                                    thisForm.find('input[name="CropperX"]').val(cx);
                                                    thisForm.find('input[name="CropperY"]').val(cy);
                                                    thisForm.find('input[name="CropperWidth"]').val(cw);
                                                    thisForm.find('input[name="CropperHeight"]').val(ch);
                                                },
                                                ready: function()
                                                {
                                                    cropper.setCropBoxData(cords);
                                                }
                                            });

                    thisFrame.addClass('floating-editor');
                    thisBody.addClass('floating-editor-body').removeClass('cms');
                    thisFrame.parent().addClass('floating-editor-tray');
                    $('#Form_EditForm_action_closeCropper').click(function(e)
                    {
                        e.preventDefault();
                        thisBtnEdit.trigger('click');
                    });

                    thisBtnEdit.unbind('click').click(function(e)
                    {
                        e.preventDefault();
                        e.stopImmediatePropagation();

                        var thisFrame       =   $(parent.document).find('iframe[src="' + thisSrc + '"]');

                        if (thisFrame.parent().hasClass('hide')) {
                            thisFrame.parent().removeClass('hide');
                        } else {
                            thisFrame.parent().addClass('hide');
                        }
                    });
                }
            }
        });
    });
}(jQuery));
