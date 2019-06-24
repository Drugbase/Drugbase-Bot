$(function () {
   $(document).on('click', ".sidebar-menu li a[href!='#']", function () {
       if ($(window).width() < 768) {
           $("[data-toggle=\"push-menu\"]").click();
       }
   });
});