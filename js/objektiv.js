function objektiv()
{
 
 $('#objektiv-ciljevi').click(function(event)
    {
        var link = $('#objektiv-ciljevi');
        var tekst = $('#objektiv-ciljevi-tekst');
        if(tekst.hasClass('skriveno'))
        {
            link.removeClass('zatvoreno');
            tekst.removeClass('skriveno');
        }
        else        
        {
            link.addClass('zatvoreno');
            tekst.addClass('skriveno');    
        }            
    });
}