$(document).ready(function(){

        $.fn.dropdown = function()
        {
            return this.each(function() {

                            $(this).hover(function(){
                                    $(this).addClass("hover");
                                    $('> .dir',this).addClass("open");
                                    $('ul:first',this).css('visibility', 'visible');
                            },function(){
                                    $(this).removeClass("hover");
                                    $('.open',this).removeClass("open");
                                    $('ul:first',this).css('visibility', 'hidden');
                            });

                    });
        }

	if($("ul.dropdown").length)
        {
            $("ul.dropdown li").dropdown();
	}

});