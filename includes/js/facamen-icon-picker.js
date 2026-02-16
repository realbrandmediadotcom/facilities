document.addEventListener('DOMContentLoaded', function () {
    const iconPicker = document.getElementById('fa-icon-picker');
    const selectedIcon = document.getElementById('fa-selected-icon');
    const optionIconInput = document.getElementById('option_value_icon');
    const toggle = document.getElementById('toggle_icon_picker');
    const iconPickerRow = document.getElementById('icon_picker_row');

    if (toggle && iconPickerRow) {
        toggle.addEventListener('change', function () {
            iconPickerRow.style.display = this.checked ? 'table-row' : 'none';
        });
    }

    if (iconPicker && selectedIcon && optionIconInput) {
        iconPicker.addEventListener('click', function (e) {
            const target = e.target.closest('.fa-icon');
            if (!target) return;

            const iconClass = target.getAttribute('data-icon');

            // Update hidden input
            optionIconInput.value = iconClass;

            // Update preview
            selectedIcon.className = iconClass;

            // Remove 'active' from all icons
            document.querySelectorAll('.fa-icon').forEach(icon => icon.classList.remove('active'));

            // Set 'active' on clicked icon
            target.classList.add('active');
        });
    }
});