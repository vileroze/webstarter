jQuery(document).ready(function ($) {
  // for taxonomy logo starts
  var mediaUploader;
  $("#taxonomy-image-upload-button").click(function (e) {
    e.preventDefault();
    if (mediaUploader) {
      mediaUploader.open();
      return;
    }
    mediaUploader = wp.media.frames.file_frame = wp.media({
      title: "Choose Image",
      button: {
        text: "Choose Image",
      },
      multiple: false,
    });
    mediaUploader.on("select", function () {
      var attachment = mediaUploader.state().get("selection").first().toJSON();
      $("#taxonomy-image-id").val(attachment.id);
      $("#taxonomy-image-wrapper").html(
        '<img src="' + attachment.url + '" style="max-width:100%;"/>'
      );
    });
    mediaUploader.open();
  });

  $("#taxonomy-image-remove-button").click(function () {
    $("#taxonomy-image-id").val("");
    $("#taxonomy-image-wrapper").html("");
  });

  // for taxonomy logo ends

  // product logo and audion starts
  function wp_media_uploader(button, inputId, isImage) {
    var frame = wp.media({
      title: button.data("title"),
      button: { text: button.data("button") },
      multiple: false,
    });

    frame.on("select", function () {
      var attachment = frame.state().get("selection").first().toJSON();
      $(inputId).val(attachment.id); // Save the ID instead of the URL

      var description = "";

      if (isImage) {
        description =
          '<img src="' +
          attachment.url +
          '" style="max-width: 150px; height: auto;" />';
      } else if (attachment.type === "audio") {
        description = '<audio controls src="' + attachment.url + '"></audio>';
      } else {
        description =
          '<a href="' + attachment.url + '">' + attachment.url + "</a>";
      }

      $(inputId).siblings("p.description").html(description);
    });

    frame.open();
  }

  $("#upload_pronounce_audio").click(function (e) {
    e.preventDefault();
    wp_media_uploader($(this), "#pronounce_audio_url", false);
  });

  $("#remove_pronounce_audio").click(function (e) {
    e.preventDefault();
    $("#pronounce_audio_url").val("");
    $(this).siblings("p.description").html('<?php _e("No file selected"); ?>');
  });

  $("#upload_logo_image").click(function (e) {
    e.preventDefault();
    wp_media_uploader($(this), "#logo_image_url", true);
  });

  $("#remove_logo_image").click(function (e) {
    e.preventDefault();
    $("#logo_image_url").val("");
    $(this).siblings("p.description").html('<?php _e("No image selected"); ?>');
  });

  // product logo and audion ends

  // for displaying error msg when sale price is greater than regular price
  $(".domainSalePrice input").on("keyup", function () {
    regularPrice = parseFloat($(".domainRegularPrice input").val());
    salePrice = parseFloat($(".domainSalePrice input").val());
    if (isNaN(regularPrice) || salePrice > regularPrice) {
      $(".wstr-error-msg").show();
      $(".wstr-error-msg").text(
        "Please enter the value less than regular price"
      );
    } else {
      $(".wstr-error-msg").hide();
    }
  });

  // for displaying error message if rating is greater that 5.
  $(".domainSeo input").on("keyup", function () {
    if ($(this).val() > 5) {
      $(".wstr-error-msg").show();
      $(".wstr-error-msg").text("Rating cannot be greater than 5.");
    } else {
      $(".wstr-error-msg").hide();
    }
  });

  // for order customer name starts
  $("#customerName").select2({
    language: {
      inputTooShort: function () {
        return "Please enter 3 or more character."; // Customize this message
      },
    },
    minimumInputLength: 3, // This defines when the message will appear
    placeholder: "Guest",
  });

  $("#customerName").on("select2:open", function () {
    // Get the search input field within the select2 dropdown
    let searchField = $(".select2-search__field");

    // Attach an event listener to capture input value as the user types
    searchField.on("input", function () {
      let searchValue = $(this).val();
      if (searchValue.length >= 3) {
        $.ajax({
          url: cpmAjax.ajax_url,
          method: "POST",
          data: {
            action: "search_users",
            search: searchValue, // Send the search value to the server
          },
          success: function (data) {
            let results = $.map(data, function (user) {
              return {
                id: user.id,
                text: user.username + " (" + user.email + ")",
              };
            });

            // Clear the Select2 dropdown
            let $select = $("#customerName");
            $select.find("option").remove(); // Clear existing options

            // Add new options to the Select2
            $.each(results, function (index, item) {
              let newOption = new Option(item.text, item.id, false, false);
              $select.append(newOption);
            });

            // Reinitialize Select2 to show new options
            $select.trigger("change");
          },
          error: function (jqXHR, textStatus, errorThrown) {
            console.error("AJAX request failed: ", textStatus, errorThrown);
          },
        });
      }
    });
  });
  // for order customer name ends

  // for adding domain on order create starts
  $("#domainId").select2({
    language: {
      inputTooShort: function () {
        return "Please enter 3 or more character."; // Customize this message
      },
    },
    minimumInputLength: 3, // This defines when the message will appear
    placeholder: "Domain",
  });
  $("#domainId").on("select2:open", function () {
    // Get the search input field within the select2 dropdown
    let searchField = $(".select2-search__field");

    // Attach an event listener to capture input value as the domain
    searchField.on("input", function () {
      let searchValue = $(this).val();
      if (searchValue.length >= 3) {
        $.ajax({
          url: cpmAjax.ajax_url,
          method: "POST",
          data: {
            action: "get_domains_list",
            search: searchValue, // Send the search value to the server
          },
          success: function (data) {
            let results = $.map(data, function (doamin) {
              return {
                id: doamin.id,
                text: doamin.name + " (" + doamin.id + ")",
              };
            });

            // Clear the Select2 dropdown
            let $select = $("#domainId");
            $select.find("option").remove(); // Clear existing options

            // Add new options to the Select2
            $.each(results, function (index, item) {
              let newOption = new Option(item.text, item.id, false, false);
              $select.append(newOption);
            });

            // Reinitialize Select2 to show new options
            $select.trigger("change");
          },
          error: function (jqXHR, textStatus, errorThrown) {
            console.error("AJAX request failed: ", textStatus, errorThrown);
          },
        });
      }
    });
  });

  $(".addDomain").click(function () {
    var domainId = $("#domainId").find(":selected").val();
    var orderId = this.id;
    console.log(orderId);
    if (domainId) {
      $.ajax({
        url: cpmAjax.ajax_url,
        method: "POST",
        data: {
          action: "get_domain_details",
          domain_id: domainId, // Send the search value to the server
          order_id: orderId,
        },
        success: function (data) {
          // if (Array.isArray(data) && data.length > 0) {
          // var domain = data[0]; // Get the first item from the array

          var domainDetail =
            '<div class="domainDetail" data-id="' +
            data.id +
            '">' +
            "<p>Domain Name: " +
            data.name +
            "</p>" +
            '<input type="hidden" name="domain_ids[]" value="' +
            data.id +
            '">' +
            "</div>";

          var domainDetails =
            "<tr class='domainDetail' data-id='" +
            data.id +
            "'>" +
            "<td><img src='" +
            data.image +
            "' style='max-width: 50px;'></td>" +
            "<td>" +
            data.name +
            "</td>" +
            "<td>" +
            data.amount +
            "</td>" +
            "<td class='deleteOrderItem'> " +
            '<a href="javascript:void(0);" id="' +
            data.id +
            '">' +
            '<i class="fa fa-times" aria-hidden="true"></i></a></td>' +
            "<input type='hidden' name='domain_ids[]' value='" +
            data.id +
            "'>" +
            " <input type='hidden' class='orderId' value='" +
            data.order_id +
            "'>" +
            "</tr>";

          // Append the domain details to the .domainDetails div
          // $(".domainDetails").append(domainDetail);
          $(".domainDetails table tbody").append(domainDetails);

          // Update subtotal and total
          $(".orderSubtotal input").val(data.subtotal);
          $(".orderTotal input").val(data.total);
          // }
        },
        error: function (jqXHR, textStatus, errorThrown) {
          console.error("AJAX request failed: ", textStatus, errorThrown);
        },
      });
    }
  });

  // for adding domain on order create ends

  // for removing domains on order create page starts
  $(document).on("click", ".deleteOrderItem a", function () {
    var domainId = this.id;
    var orderId = $(".orderId").val();
    if (!orderId) {
      $("tr[data-id='" + domainId + "']").remove();
    }
    // Confirm deletion
    if (confirm("Are you sure you want to remove this domain?")) {
      if (domainId) {
        $.ajax({
          url: cpmAjax.ajax_url,
          method: "POST",
          data: {
            action: "remove_domain_from_order",
            domain_id: domainId, // Send the search value to the server
            order_id: orderId,
          },
          success: function (data) {
            var domain_id = data.data.id;
            $("tr[data-id='" + domain_id + "']").remove();

            // Update subtotal and total
            console.log(data.data.subtotal);
            console.log(data.data.total);
            $(".orderSubtotal input").val(data.data.subtotal);
            $(".orderTotal input").val(data.data.total);
          },
          error: function (jqXHR, textStatus, errorThrown) {
            console.error("AJAX request failed: ", textStatus, errorThrown);
          },
        });
      }
    }
  });
  // for removing domains on order create page starts

  // for adding order notes starts

  $(".addOrderNotesButton").on("click", function () {
    var orderId = this.id;
    var orderNoteType = $("#orderNoteType").find(":selected").val();
    var orderNote = $("#orderNote").val();

    if (orderId && orderNote) {
      $.ajax({
        url: cpmAjax.ajax_url,
        method: "POST",
        data: {
          action: "add_domain_order_notes",
          order_note_type: orderNoteType,
          order_note: orderNote,
          order_id: orderId,
        },
        success: function (data) {
          if (data.success) {
            // Append the note to the list of notes
            $(".orderNotesMain").prepend(
              "<div>" +
                "<p>" +
                data.data.note +
                "</p>" +
                "<em>" +
                data.data.note_date +
                "</em>" +
                " <a href='javascript:void(0);' class='deleteNoteButton' data-note-id='" +
                data.data.id +
                "'>Delete</a>" +
                "</div>"
            );
            $("#orderNote").val(""); // Clear the textarea
          } else {
            console.log(data.data);
          }
        },
        error: function (jqXHR, textStatus, errorThrown) {
          // console.error("AJAX request failed: ", textStatus, errorThrown);
        },
      });
    } else if (!orderId) {
    }
  });
  // for adding order notes ends

  // for deleting order notes starts
  // Delete Note
  $(".orderNotesMain").on("click", ".deleteNoteButton", function () {
    var noteId = $(this).data("note-id");

    $.ajax({
      url: cpmAjax.ajax_url,
      method: "POST",
      data: {
        action: "delete_domain_order_note",
        note_id: noteId,
      },
      success: function (data) {
        if (data.success) {
          // Remove the note element from the DOM
          $("a[data-note-id='" + noteId + "']")
            .closest("div")
            .remove();
        } else {
          console.log(data.data);
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.error("AJAX request failed: ", textStatus, errorThrown);
      },
    });
  });
  // for deleting order notes ends
  $("#currencyList").select2({
    placeholder: "Select currencies",
    allowClear: true
});
});


function openCity(cityName) {
  var i;
  var x = document.getElementsByClassName("wstr-menu");
  for (i = 0; i < x.length; i++) {
    x[i].style.display = "none";
  }
  document.getElementById(cityName).style.display = "block";
}
