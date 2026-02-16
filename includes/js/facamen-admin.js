document.documentElement.classList.add("js-enabled");

document.addEventListener("DOMContentLoaded", function () {
  const iconPicker = document.getElementById("fa-icon-picker");
  const iconInput = document.getElementById("option_value_icon");
  const previewIcon = document.getElementById("fa-selected-icon");
  const defaultIcon =
    document.getElementById("fa-default-icon")?.className ||
    "fa-solid fa-hand-point-right";

  if (!iconPicker || !iconInput || !previewIcon) return;

  iconPicker.addEventListener("click", function (e) {
    const target = e.target.closest(".fa-icon");
    if (!target) return;

    // If clicking the already active one → deselect
    if (target.classList.contains("active")) {
      target.classList.remove("active");
      iconInput.value = ""; // clear hidden input
      previewIcon.className = defaultIcon; // revert to default icon
      return;
    }

    // Otherwise normal select
    iconPicker.querySelectorAll(".fa-icon").forEach((icon) => {
      icon.classList.remove("active");
    });

    target.classList.add("active");

    const selectedIcon = target.getAttribute("data-icon");
    iconInput.value = selectedIcon;
    previewIcon.className = selectedIcon;
  });
});

// Toggle JS
document.addEventListener("DOMContentLoaded", function () {
  const toggle = document.getElementById("toggle_icon_picker");
  const iconRow = document.getElementById("icon_picker_row");

  toggle.addEventListener("change", function () {
    iconRow.style.display = this.checked ? "" : "none";
  });
});

// Icon Selection for edit mode
document.addEventListener("DOMContentLoaded", function () {
  // Icon selection for Edit form
  const iconPicker = document.querySelectorAll("#edit-fa-icon-picker .fa-icon");
  const selectedIconInput = document.getElementById("edit_option_value_icon");
  const selectedIconDisplay = document.getElementById("edit-fa-selected-icon");

  iconPicker.forEach(function (icon) {
    icon.addEventListener("click", function () {
      iconPicker.forEach((i) => i.classList.remove("active"));
      icon.classList.add("active");

      const iconClass = icon.getAttribute("data-icon");
      selectedIconInput.value = iconClass;
      selectedIconDisplay.className = iconClass;
    });
  });
});

// Enable / Disable Button Options + related fields
document.addEventListener("DOMContentLoaded", function () {
  const toggleButtonOptions = document.getElementById("enable_button_fields");
  const buttonText = document.getElementById("buttontext");
  const buttonLink = document.getElementById("buttonlinkurl");
  //const openNewTab   = document.getElementById("enable_opennewtab");

  function toggleInputs() {
    const enabled = toggleButtonOptions.checked;

    // Enable / disable text and URL inputs
    buttonText.readOnly = !enabled;
    buttonLink.readOnly = !enabled;
  }

  toggleInputs(); // run on page load
  toggleButtonOptions.addEventListener("change", toggleInputs);
});

// Select2
document.addEventListener("DOMContentLoaded", function () {
  const $ = jQuery;

  const selector = $(".facamen-select2");

  if (!selector.length || typeof selector.select2 !== "function") return;

  selector.select2({
    width: "100%",
    placeholder: "Select role(s)",
    allowClear: false,
  });

  function styleSelect2Choices() {
    selector
      .next(".select2-container")
      .find(".select2-selection__choice")
      .each(function () {
        const $choice = $(this);
        const $remove = $choice.find(".select2-selection__choice__remove");

        const roleText = $choice.text().replace("×", "").trim();

        if (roleText === "Administrator") {
          $remove.remove();
          $choice.addClass("facamen-admin-choice");
        } else {
          $remove.appendTo($choice);
        }
      });
  }

  styleSelect2Choices();

  selector.on("change select2:select select2:unselect", function () {
    setTimeout(styleSelect2Choices, 50);
  });
});

// === Background Image Preview Update After Save ===
jQuery(document).ready(function ($) {
  $("form").on("submit", function () {
    const imageUrl = $(".imagelinkurl-input").val();
    const preview = $(".image-preview");

    if (imageUrl) {
      preview.attr("src", imageUrl).show();
    } else {
      preview.hide();
    }
  });
});

// === Hide preview when "Check to remove image" is clicked ===
jQuery(document).ready(function ($) {
  $("#check_to_remove_image").on("change", function () {
    const preview = $(".image-preview");
    const input = $(".imagelinkurl-input");

    if (this.checked) {
      preview.fadeOut(200);
      input.val("");
    } else {
      preview.fadeIn(200);
    }
  });
});
