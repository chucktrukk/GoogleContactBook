$(document).ready(function () {
   
});


// contains event handlers
(function () {

    var filterEl = $("#filter"),
        userTweetEl = $(".tweet-user-list li");
   
    //filter followers as the user types in the search box
    filterEl.on("keyup", function () {
        var filter = $(this).val();
        userTweetEl.each(function () {
            if ($(this).text().search(new RegExp(filter, "i")) < 0) {
                $(this).slideUp();
            } else {
                $(this).slideDown();
                $(this).removeAttr("style");
                $(this).keyup();
            }
        });
        
    });

})();
