function skociNa(h)
{
    var url = location.href;               
    location.href = "#"+h;      
    if(history.replaceState)
        history.replaceState(null,null,url);
}

function digital(csrf, urlStranice, prazanFilter, jezik, uOkviruStranice, idOdeljak)
{
    var self = this;   
    self.uOkviruStranice = uOkviruStranice; // ako je true znaci da radi u okviru stranice, a ako je false onda u okviru dijaloga
    
    
    var fokusirajPolje = function()
    {
        var forma = getAktivnaForma();
        var txtPolje = forma.find('#df_ftsKomplet');
        if(txtPolje.size() === 0)//ako je nula onda se radi o detaljnoj pretrazi
        {            
            txtPolje = forma.find('#df_naslov');
            var svaPolja = forma.find('.kolona .grupa input[type="text"]');
            var nadjen = false;
            for(var i=0; i<svaPolja.size();i++)
            {
                if(svaPolja.get(i).value !== '')
                {                    
                    txtPolje = $('#'+svaPolja.get(i).id);
                    nadjen = true;
                    break;
                }
            }
            if( ! nadjen)
            {
                var selects = forma.find('select');
                for(i=0;i<selects.size();i++)
                {            
                    if(selects.get(i).selectedIndex > 0)
                    {
                        txtPolje = $('#'+selects.get(i).id);      
                        break;
                    }
                }
            }
        }
        //fokus i kursor
        if(txtPolje.size() === 1)
        {
            txtPolje.focus();   
            var tmpStr = txtPolje.val();
            txtPolje.val('');
            txtPolje.val(tmpStr); 
        }
     };
    
    var prikaziAktivniTab = function(prikazi)
    {
        var ruka = $('#tabs_1 .ruka');
        var tabBody = $('#tabs_1 .ionTabs__body');
        if(prikazi)
        {
            ruka.removeClass('zatvoreno');
            tabBody.removeClass('skriveno');
            
            fokusirajPolje();
        }
        else        
        {
            ruka.addClass('zatvoreno');
            tabBody.addClass('skriveno');    
        }         
        if (typeof(Storage) !== "undefined")
        {
            localStorage.setItem("prikazi_aktivni_tab", prikazi);
        }

    };
    
    var ruka = $('#tabs_1 .ruka');        
    ruka.click(function(event)
    {        
        var tabBody = $('#tabs_1 .ionTabs__body');
        var prikazi = tabBody.hasClass('skriveno');
        prikaziAktivniTab(prikazi);                  
    });
    
    var  isPrazanFilter = function(forma)
    {
        var inputs = forma.find('input[type="text"]');
        var i = 0;
        for(;i<inputs.size();i++)
        {            
            if(inputs.get(i).value !== '')
                return false;
        }     
        var selects = forma.find('select');
        for(i=0;i<selects.size();i++)
        {            
            if(selects.get(i).selectedIndex > 0)
                return false;
        }
        return true;
    };
       
    var primeniFilter = function(forma, event, th, izbaci)
    {                      
        event.preventDefault();   
        if(izbaci !== null)
            forma.find('#df_'+izbaci).val('');
        var href = $(th).attr('href');            
        var get = '';
        var nastavak = '';
        if( ! isPrazanFilter(forma))
        {
            get = forma.serialize();
            nastavak = '/?';
            if(href.search('\\?') !== -1)
                nastavak = '&';
        }
        window.location.replace(href + nastavak + get);
    };
    
    var getAktivnaForma = function()
    {
        if($('div#tabs_1 li.ionTabs__tab.ionTabs__tab_state_active').attr('data-target') === 'komplet')
            return $('#digital-form');
        return $('#digital-form1');        
    };
    
    //link iz stabla, putanja ispod naziva zbirke
    $('.zbirka, .putanja a').click(function(event)
    {
        var forma = getFormaAktivnihTagova();
        primeniFilter(forma, event, this, null);
    });          
    
    var getFormaAktivnihTagova = function()
    {        
        //ako postoje vec navedeni tagovi onda ne gledam koji je tab aktivan
        if($('.ukloni-filter-tag').size() > 0)
        {
            if( $('#filter-tekst ul li').get(0).id === 'filter_ftsKomplet')
                return $('#digital-form');
            return $('#digital-form1');
        }
        else //forma iz aktivnog taba
            return getAktivnaForma();
    };    
    
    $('.ukloni-filter-tag').click(function(event)
    {  
        var forma = getFormaAktivnihTagova();
        var id = $(this).parent().get(0).id;
        var izbaci = id.split('_').pop();
        primeniFilter(forma, event, this, izbaci);
    });
    
    var getParameterByName = function( name,href )
    {
      name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
      var regexS = "[\\?&]"+name+"=([^&#]*)";
      var regex = new RegExp( regexS );
      var results = regex.exec( href );
      if( results == null )
        return "";
      else
        return decodeURIComponent(results[1].replace(/\+/g, " "));
    };
    
    $('#portlet_'+idOdeljak+' .portlet-content a').click(function(event)
    {        
        if(uOkviruStranice)
            return;
        event.preventDefault();
        var href = $(this).attr('href');
        var tag = getParameterByName('df[kljucneReci]', href);
        var forma = $('#digital-form1');
        forma.find('#df_kljucneReci').val(tag);
        var indeks = href.search('\\?');
        href = href.substring(0, indeks);
        $(this).attr('href', href);
        var prikazi = getPrikaziTab();
        $('#Button__pretraga__detalji').trigger( "click" );
        prikaziAktivniTab(prikazi);        
        primeniFilter(forma, event, this, null);
    });
    
 /*   $('#dugmad1 input, #dugme-trazi-komplet input').click(function(event)
    {
        var forma = getAktivnaForma();
        if(isPrazanFilter(forma))
            event.preventDefault();
    });*/
    
    
    
    //dijalog za pretragu stranica
    $('#zatvori').click(function(event)
    {
        $('#pretraga').dialog("close");
    });
   
   //Salje zahtev na adresu urlStranice i dobijeni rezultat dodaje u dijalog
    var trazi = function(dijalog)
    {        
        $('#polje-upit-td').addClass('animacija');
        var forma = dijalog.find('#pretraga-form').serialize();
        forma.YII_CSRF_TOKEN = csrf;
        $.get(urlStranice,
            forma,
            function(data) 
            {
                //dijalog.find('#lista-rezultat').html(data);  
                
                $('#lista-rezultat').remove();
                dijalog.append(data);
                $('#polje-upit-td').removeClass('animacija');
            });        
    };
    
    var getPrikaziTab = function()
    {
        var prikazi = true;
        if (typeof(Storage) !== "undefined")       
            prikazi =  ! (localStorage.getItem("prikazi_aktivni_tab") === 'false');        
        return prikazi;
    };
    
    $('#pretraga input,#pretraga select').keypress(function(event) 
    {
        if(event.keyCode === 13)
        {
            event.preventDefault();
            var dijalog = $('#pretraga');
            trazi(dijalog);
        }
        //return event.keyCode != 13; 
    });    
    
    self.inicijalizuj = function(dijalog)
    {
        dijalog.find('#dugme-trazi').click(function(event)
        {
            event.preventDefault();
            trazi(dijalog);
        });        
    };

    var prikaziDijalog = function(idKnjige)
    {
        var dijalog = $('#pretraga');               
        dijalog.find('#PretragaStranicaForm_idKnjiga').val(idKnjige);
        var naslov = $('#knjiga_' + idKnjige).find('#a-naslov_'+idKnjige).text();
        var autor = $('#knjiga_' + idKnjige).find('.autor-knjige-data').text();
        if(global_upit)
        {
            dijalog.find('#PretragaStranicaForm_operator_1').prop('checked',true);
        }
        dijalog.find('#PretragaStranicaForm_ftsUpit').val(global_upit); 
        dijalog.dialog({title: naslov + ' - ' + autor});
             
        self.inicijalizuj(dijalog);
        
        $('#pretraga').dialog("open");
        trazi(dijalog);        
    };

    $('.pretraga-stranica').click(function(event)
    {        
        event.preventDefault();          
        var idKnjige = this.id.split('_').pop();
        prikaziDijalog(idKnjige);

    });
    
    $('.pretraga-deo-stranice').click(function(event)
    {
        event.preventDefault();
        skociNa('a-pretraga');
        $('#PretragaStranicaForm_ftsUpit').focus();        
    });
    
    $('#PretragaStranicaForm_ftsUpit').focusin(function(){$('#upit table td#polje-upit-td').css({'border':'1px solid #3079ED'});});
    $('#PretragaStranicaForm_ftsUpit').focusout(function(){$('#upit table td#polje-upit-td').removeAttr("style");});    
    
    $('#df_ftsKomplet').focusin(function(){$('#upit-komplet table td#polje-upit-td-komplet').css({'border':'1px solid #3079ED'});});
    $('#df_ftsKomplet').focusout(function(){$('#upit-komplet table td#polje-upit-td-komplet').removeAttr("style");});    
    
    $('#tabs_1 .ionTabs__tab').click(function(event)
    {
        prikaziAktivniTab(true);
    });
    
    if(uOkviruStranice)
    {
        var dijalog = $('#pretraga');
        self.inicijalizuj(dijalog);
        dijalog.on('click', '.pager li', function()
        {
            skociNa('a-pretraga');
        });
    }
    else
        prikaziAktivniTab(getPrikaziTab());
}