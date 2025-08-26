document.addEventListener('click', function (e) {
    if (e.target.classList.contains('copy-shortcode')) {
        e.preventDefault();
        const shortcode = e.target.getAttribute('data-shortcode');
        navigator.clipboard.writeText(shortcode).then(() => {
            e.target.textContent = swnData.translations.copied;
            setTimeout(() => {
                e.target.textContent = swnData.translations.copy_shortcode;
            }, 2000);
        });
    }
});


jQuery(document).ready(function ($) {
    function toggleFields() {
        var type = $('#type').val();
        $('.type-field').hide();
        $('.field-' + type).show();
    }
    $('#type').on('change', toggleFields);
    toggleFields();
});



document.addEventListener("DOMContentLoaded", function () {
    const tabs = document.querySelectorAll(".nav-tab");
    const contents = document.querySelectorAll(".swn-tab-content");

    tabs.forEach(tab => {
        tab.addEventListener("click", function (e) {
            e.preventDefault();
            tabs.forEach(t => t.classList.remove("nav-tab-active"));
            contents.forEach(c => c.style.display = "none");
            tab.classList.add("nav-tab-active");
            document.querySelector(tab.getAttribute("href")).style.display = "block";
        });
    });
});