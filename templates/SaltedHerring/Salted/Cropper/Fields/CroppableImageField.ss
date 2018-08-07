<input $AttributesHTML class="salted-cropped-image" style="display: none;" />
<% if $CroppedImage %>
<div class="salted-croppable">
<% if $CroppedImage.Thumbnail %>
<% with $CroppedImage.Thumbnail %>
    <img src="$URL?timestamp=$Top.timestamp" width="$Width" height="$Height" />
<% end_with %>
<% else %>
    <p>&lt;Click 'Edit' to upload an image for cropping&gt;</p>
<% end_if %>
</div>
<button href='#' class='croppable-image-field-button btn btn-primary'><%t CroppableImageable.EDIT 'Edit' %></button>
<button href='#' data-id="$Value" class='croppable-image-field-remove-button btn btn-danger'><%t CroppableImageable.REMOVE 'Remove' %></button>
<% else %>
<button href='#' class='croppable-image-field-button btn btn-primary font-icon-plus-circled'><%t CroppableImageable.ADDIMAGE 'Add Image' %></button>
<% end_if %>
<div class="salted-croppable-dialog"></div>
