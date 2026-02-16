// Upload facilities image from WordPress
jQuery(document).ready(function ($) {
  $("#upload_facilities_image").on("click", function (e) {
    e.preventDefault();

    // Create the media frame.
    var frame = wp.media({
      title: "Select or Upload an Image",
      button: {
        text: "Use this image",
      },
      multiple: false, // Set to true to allow multiple files
    });

    // When an image is selected, run a callback.
    frame.on("select", function () {
      var attachment = frame.state().get("selection").first().toJSON();
      $("#facilities_imageLinkURL").val(attachment.url);
    });

    // Finally, open the modal
    frame.open();
  });
});

// Upload amenities image from WordPress
jQuery(document).ready(function ($) {
  $("#upload_amenities_image").on("click", function (e) {
    e.preventDefault();

    // Create the media frame.
    var frame = wp.media({
      title: "Select or Upload an Image",
      button: {
        text: "Use this image",
      },
      multiple: false, // Set to true to allow multiple files
    });

    // When an image is selected, run a callback.
    frame.on("select", function () {
      var attachment = frame.state().get("selection").first().toJSON();
      $("#amenities_imageLinkURL").val(attachment.url);
    });

    // Finally, open the modal
    frame.open();
  });
});

// Upload Cateogries Image Link URL image from WordPress
jQuery(document).ready(function ($) {
  $("#upload_imagelinkurl").on("click", function (e) {
    e.preventDefault();

    // Create the media frame.
    var frame = wp.media({
      title: "Select or Upload an Image",
      button: {
        text: "Use this image",
      },
      multiple: false, // Set to true to allow multiple files
    });

    // When an image is selected, run a callback.
    frame.on("select", function () {
      var attachment = frame.state().get("selection").first().toJSON();
      $("#imagelinkurl").val(attachment.url);
    });

    // Finally, open the modal
    frame.open();
  });
});

// Auto preview after uploading with media uploader
/* jQuery(document).ready(function($) {
    $('.upload-image-button').on('click', function(e) {
        e.preventDefault();

        const button = $(this);
        const input = button.siblings('.imagelinkurl-input');
        const preview = button.closest('td').find('.image-preview');

        const custom_uploader = wp.media({
            title: 'Select Image',
            button: {
                text: 'Use this image'
            },
            multiple: false
        }).on('select', function () {
            const attachment = custom_uploader.state().get('selection').first().toJSON();
            input.val(attachment.url);
            if (preview.length) {
                preview.attr('src', attachment.url).show();
            }
        }).open();
    });
}); */

jQuery(document).ready(function ($) {
  $(".upload-image-button").on("click", function (e) {
    e.preventDefault();

    const custom_uploader = wp.media({
      title: "Select Image",
      button: { text: "Use this image" },
      multiple: false,
    });

    custom_uploader.on("select", function () {
      // FORCE text into the button
      $(".media-button-select").prop("disabled", false).text("Use this image");

      // (Your existing logic…)
      const attachment = custom_uploader
        .state()
        .get("selection")
        .first()
        .toJSON();
      const button = $(e.currentTarget);
      const input = button.siblings(".imagelinkurl-input");
      const preview = button.closest("td").find(".image-preview");

      input.val(attachment.url);
      if (preview.length) {
        preview.attr("src", attachment.url).show();
      }
    });

    custom_uploader.open();

    // Force the text when modal opens
    $(document).on("DOMNodeInserted", function () {
      $(".media-button-select").text("Use this image");
    });
  });
});
