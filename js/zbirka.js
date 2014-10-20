function zbirka(url, csrf, setuj)
{    
    $('.zbirka').on("click",
    function(event)
    {
        event.preventDefault();
        var id = this.id.split('_').pop();
        id = parseInt(id);        
        event.preventDefault();
        $.post(
            url,
            {"id_zbirka":id, "YII_CSRF_TOKEN":csrf},
            function(data)
            {
                $('#lista-zbirki').html(data);
                var roditelj = $('.selektovana-zbirka').attr('id').split('_').pop();                
                
                $(setuj).val(roditelj);
                //alert(setuj);
                //$('#KnjigaDeo_id_zbirka').val(roditelj);
                //var selektovana_tekst = $('se')
                $('#labela_roditelj').text($('.selektovana-zbirka').text());
                zbirka(url, csrf, setuj);
            }
        );
    });


}