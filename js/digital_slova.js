function digital(crsf, filter, prazanFilter, jezik)
{
    this.filter = filter;
    var self = this;
    
    var filterLink = $('#filter-link');
    
    filterLink.click(function(event)
    {
        var filterProzor = $('#filter-zbirke');
        if(filterProzor.hasClass('skriveno'))
        {
            filterLink.removeClass('zatvoreno');
            filterProzor.removeClass('skriveno');
        }
        else        
        {
            filterLink.addClass('zatvoreno');
            filterProzor.addClass('skriveno');    
        }            
    });
    
    //link iz stabla
    $('.zbirka').click(function(event)
    {
        if( ! prazanFilter)
        {
            event.preventDefault();
            
            var forma = $('#digital-form');
            //unosim stare vrednosti u forumu, korisnik mora da klikne na 
            //"postavi filter" da bi vrednosti iz forme imale efekat
            forma.find('#df_naslov').val(self.filter.naslov);
            forma.find('#df_autor').val(self.filter.autor);
            forma.find('#df_poglavlje').val(self.filter.poglavlje);
            forma.find('#df_godinaOd').val(self.filter.godinaOd);
            forma.find('#df_godinaDo').val(self.filter.godinaDo);
            
            //forma.find() //TODO vrsta gradje
            
            var href = $(this).attr('href');
            
            var get = forma.serialize();
            var nastavak = '/?';
            if(href.search('\\?') != -1)
                nastavak = '&'
            window.location.replace(href + nastavak + get);
        }        
    });
    
    /*
    var form = $("#pretraga-proizvoda-form").serialize();
    window.location.replace("/pijaca/proizvodi?"+form + "&jezik='.$jezik.'");
     */
}