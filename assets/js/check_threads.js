var client = new ZeroClipboard($('.copy-button-rct'));
client.on("ready", function(readyEvent) {
    client.on("aftercopy", function(event) {
        alert("Copied text to clipboard");
    });
});
//
// $(".thread span.badge").click(function () {
//     if (!confirm("Manually override this thread check?")) {
//         return false;
//     }
//
//     $(this).toggleClass('alert-danger')
//         .toggleClass('alert-success')
//         .find('i').toggleClass('fa-times').toggleClass('fa-check');
// });
//
//
