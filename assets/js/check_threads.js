var clippy = new Clipboard('.copy-button-rct');

clippy.on('success', function(e) {
    alert('Copied to clipboard!');
});

$(".thread span.badge").click(function () {
    if (!confirm("Manually override this thread check?")) {
        return false;
    }

    $(this).toggleClass('alert-danger')
        .toggleClass('alert-success')
        .find('i').toggleClass('fa-times').toggleClass('fa-check');
});


