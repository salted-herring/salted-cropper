(function($){
	$.entwine('ss', function($) {
		$('.salted-cropper').entwine({
			onmatch: function(e) {
				if ($(this).find('img').length > 0) {

					var thisCropper = $(this),
						image = $(this).find('img')[0],
						ratio = $(this).attr('data-cropper-ratio'),
						minWidth = $(this).attr('data-min-width'),
						minHeight = $(this).attr('data-min-height'),
						source = $(this).attr('data-source'),
						name = $(this).attr('data-name'),
						thisViewer = $('#salted-cropped-' + name),
						lbl = '<label style="display: inline-block; margin-top: 2em; margin-left: 90px;" for="cropper_for_'+name+'"> Cropping image</label>',
						cb = '<input id="cropper_for_'+name+'" class="salted-cropper-switch" name="cropper_bindings[]" type="checkbox" value="' + (source + ':' + name) + '" />';

					lbl = $(lbl);
					cb = $(cb);

					lbl.prepend(cb);

					$('#Form_EditForm_'+source+'_Holder li.ss-uploadfield-item').append(lbl);
					//$(this).append('<input type="hidden" name="cropper_bindings[]" value="' + (source + ':' + name) + '" />');
					$(this).append('<input type="hidden" name="' + (name + '_salted_container_x') + '" />');
					$(this).append('<input type="hidden" name="' + (name + '_salted_container_y') + '" />');
					$(this).append('<input type="hidden" name="' + (name + '_salted_container_width') + '" />');
					$(this).append('<input type="hidden" name="' + (name + '_salted_container_height') + '" />');
					$(this).append('<input type="hidden" name="' + (name + '_salted_cropper_x') + '" />');
					$(this).append('<input type="hidden" name="' + (name + '_salted_cropper_y') + '" />');
					$(this).append('<input type="hidden" name="' + (name + '_salted_cropper_width') + '" />');
					$(this).append('<input type="hidden" name="' + (name + '_salted_cropper_height') + '" />');

					cb.change(function(e) {
						if ($(this).prop('checked')) {
							thisCropper.show();
							thisViewer.hide();
						} else{
							thisCropper.hide();
							thisViewer.show();
						}

                        $('.salted-cropper-switch').each(function(index, element) {
                            if ($(this).prop('checked')) {
								$('input[name="SaltedCropperScheduled"]').prop('checked', true);
								return false;
							}
							$('input[name="SaltedCropperScheduled"]').prop('checked', false);
                        });
                    });

					var cropper = new Cropper(image, {
						viewMode: 3,
						aspectRatio: ratio,
						zoomable: false,
						minContainerWidth: minWidth,
						minContainerHeight: minHeight,
						crop: function(e) {
							var x = Math.round(cropper.getCanvasData().left * -1),
								y = Math.round(cropper.getCanvasData().top * -1),
								w = Math.round(cropper.getCanvasData().width),
								h = Math.round(cropper.getCanvasData().height),
								cx = Math.round(cropper.getCropBoxData().left),
								cy = Math.round(cropper.getCropBoxData().top),
								cw = Math.round(cropper.getCropBoxData().width),
								ch = Math.round(cropper.getCropBoxData().height);

							$('input[name="'+ (name + '_') +'salted_container_x"]').val(x);
							$('input[name="'+ (name + '_') +'salted_container_y"]').val(y);
							$('input[name="'+ (name + '_') +'salted_container_width"]').val(w);
							$('input[name="'+ (name + '_') +'salted_container_height"]').val(h);

							$('input[name="'+ (name + '_') +'salted_cropper_x"]').val(cx);
							$('input[name="'+ (name + '_') +'salted_cropper_y"]').val(cy);
							$('input[name="'+ (name + '_') +'salted_cropper_width"]').val(cw);
							$('input[name="'+ (name + '_') +'salted_cropper_height"]').val(ch);

						}
					});
					$('#Form_EditForm_SaltedCropperScheduled_Holder').hide();
					$(this).hide();
				}
			}
		});
	});

}(jQuery));
