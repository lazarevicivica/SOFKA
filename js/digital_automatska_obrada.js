function automatskaObrada(urlStranice, csrf)
{
    var self = this;
    
    this.greskaObrade = function(json)
    {
        if('greskaObrade' in json)
            return json.greskaObrade;
        return false;
    };
    
    this.greskaCobiss = function(json)
    {
        if('greskaCobiss' in json)
            return json.greskaCobiss;
        return false;
    };

    this.prikaziGresku = function(greska)
    {
        alert(greska);
    };

    this.podaciOSlikama = function(json)
    {
        if('meta' in json)
        {
            var meta = json.meta;
            var json_desc = JSON.stringify(meta);
            $('#KnjigaDeo_json_desc').val(json_desc);
        }        
        if('korice' in json)
            $('#KnjigaDeo_url_slike').val(json.korice);
        if('tekstPutanja' in json)
            $('#KnjigaDeo_tekst_putanja').val(json.tekstPutanja);                    
    };
    
    this.cobissPodaci = function(json)
    {
        if( ! 'cobiss' in json)
            return;
        var cobiss = json.cobiss;
        if('autor' in cobiss)
            $('#KnjigaDeo_autor').val(cobiss.autor);
        if('naslov' in cobiss)
            $('#Knjiga_naslov').val(cobiss.naslov);
        if('izdavanje_i_proizvodnja' in cobiss)
            $('#KnjigaDeo_izdanje').val(cobiss.izdavanje_i_proizvodnja);
        if('predmetne_odrednice' in cobiss)
            $('#tagovi_').val(cobiss.predmetne_odrednice);
        if('cobiss_sr_id' in cobiss)
            $('#KnjigaDeo_cobiss').val(cobiss.cobiss_sr_id);
        if('godina' in cobiss)
            $('#KnjigaDeo_godina').val(cobiss.godina);
    };

    this.prikaziAnimaciju = function()
    {
        $('#auto-obrada-inv-br').addClass('animacija');
        $('#pokreni-obradu').addClass('sakrij');        
    };
    
    this.sakrijAnimaciju = function()
    {
        $('#auto-obrada-inv-br').removeClass('animacija');
        $('#pokreni-obradu').removeClass('sakrij');
    };
    
    $('#pokreni-obradu').click(function(event)
    {
        event.preventDefault();
        self.prikaziAnimaciju();
        var invBr = $('#auto-obrada-inv-br').val();
        if( ! invBr)
            return;
        $('#KnjigaDeo_inv_br').val(invBr);
        urlStranice += '?invBr='+invBr+'&YII_CSRF_TOKEN='+csrf;
        $.getJSON(urlStranice,
            function(json) 
            {
                var greskaObrade = self.greskaObrade(json);
                if( ! greskaObrade)
                    self.podaciOSlikama(json);
                else
                    self.prikaziGresku(greskaObrade);
                var greskaCobiss = self.greskaCobiss(json);
                if( ! greskaCobiss)
                    self.cobissPodaci(json);
                else
                    self.prikaziGresku(greskaCobiss);                
                self.sakrijAnimaciju();
                alert('Крај обраде');                
            }
        );
    });
}