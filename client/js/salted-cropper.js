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
                        thisSrcRaw      =   thisForm.attr('action').split('/');

                        thisSrcRaw.pop();
                        thisSrc         =   thisSrcRaw.join('/') + '/edit',
                        doInit          =   function(me) {
                            var image           =   me.find('img')[0],
                                ratio           =   parseFloat(thisForm.find('input[name="CropperRatio"]').val()),
                                minWidth        =   me.attr('data-min-width'),
                                minHeight       =   me.attr('data-min-height') ? me.attr('data-min-height') : 0,
                                name            =   me.attr('data-name'),
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
                        };

                    if (parseInt($(this).find('img').attr('width')) > 0) {
                        doInit(thisCropper);
                    } else {
                        $(this).find('img').on('load', function(e)
                        {
                            var width   =   $(this).width(),
                                height  =   $(this).height(),
                                ratio   =   width > 666 ? (666 / width) : 1,
                                calc_width  =   width * ratio;
                                calc_height =   height * ratio;

                            $(this).attr('width', width);
                            $(this).attr('height', height);
                            $(this).parents('.salted-cropper:eq(0)').width(calc_width);
                            $(this).parents('.salted-cropper:eq(0)').height(calc_height);
                            $(this).parents('.salted-cropper:eq(0)').data('min-width', calc_width);
                            $(this).parents('.salted-cropper:eq(0)').data('min-height', calc_height);

                            doInit(thisCropper);
                        });

                        $(this).removeAttr('style');
                        $(this).find('img').removeAttr('width').removeAttr('height');
                    }
                }
            }
        });
    });
}(jQuery));
