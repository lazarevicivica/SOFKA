var otpremac_fajlova = null;
var dijalog;
var opisEditor = null;
var glavniTekstEditor = null;
var uvodEditor = null;
var klasaModela = '';
function getBrojZaPonovo()
{
    return Math.round(new Date().getTime() / 1000);
}

function initAzuriranjeObjaveJS(klasa, sirina, visina, kvalitet, urlSkripte, csrf, slikaOtpremljeno,
    slikaCekanje, slikaStaro, slikaStaroBrisanje, slikaOtpremljenoBrisanje, nedefinisano )
{
    klasaModela = klasa;
    initCKEGlavnitekstIUvod();
    initOtpremanjeFajlova(sirina, visina, kvalitet, urlSkripte, csrf, slikaOtpremljeno, slikaCekanje, slikaStaro, slikaStaroBrisanje, slikaOtpremljenoBrisanje, nedefinisano );
}


function initCKEGlavnitekstIUvod()
{
    if(glavniTekstEditor === null)
    {
        glavniTekstEditor = CKEDITOR.replace(klasaModela + '_tekst_sirov',
        {
            toolbar:
            [
                ['Source'],['Print'],
                ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
                ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
                ['NumberedList','BulletedList','-','Outdent','Indent', '-','TextColor','BGColor'],
                ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
                ['Link','Unlink','Anchor'],
                ['Image', 'Table'],
                ['Format'],
                ['Font','FontSize'],['Maximize', 'ShowBlocks']
            ],
            language:'sr',
            height: '235px'
        }
      );  
    }
    if(uvodEditor === null)
    {
        uvodEditor = CKEDITOR.replace(klasaModela + '_uvod',
        {
            toolbar:
            [
                ['Source'],
                ['Undo','Redo'],
                ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
                ['Link','Unlink','Anchor'],
            ],
            language:'sr',
            height: '200px'
        }
      );
    }
}

function initOtpremanjeFajlova(sirina, visina, kvalitet, urlSkripte, csrf, slikaOtpremljeno,
    slikaCekanje, slikaStaro, slikaStaroBrisanje, slikaOtpremljenoBrisanje, nedefinisano )
{

//Brise prethodni input. Bez sledecih linija init() sa svakim novim pozivom dodaje novi input,
//koji zatim pravi problem jer se i za njega otvara dijalog za izbor fajlova.
    if(otpremac_fajlova !== null)
    {
       var izbor = document.getElementById('izbor');
       plupload.removeEvent(izbor, 'click', otpremac_fajlova.id);
    }
    $(".plupload").remove(".html5"); 
//kraj
    otpremac_fajlova = new plupload.Uploader(
    {
        runtimes: 'html5,html4',
        container: 'uploader',
        unique_names: true,
        browse_button: 'izbor',
        filters : [{title : 'Slike', extensions : 'jpg,jpeg,gif,png'}],
        resize : {width : sirina, height : visina, quality : kvalitet},
        //flash_swf_url : 'js/plupload/plupload.flash.swf',
        multiple_queues: false,
        multipart: true,
        multipart_params:
        {
          '_http_accept': 'application/javascript',
          'YII_CSRF_TOKEN' : csrf
        },
        url: urlSkripte
      });

    otpremac_fajlova.init();

    if(otpremac_fajlova.runtime == 'html4')
        $('#stari-browser').css('display', 'block');
    $('#dugme-sacuvaj').removeClass('onemoguceno');
    $('#dugme-sacuvaj').removeAttr('disabled');
    $('#otkazi').css('display', 'none');
    $('#otpremanje').css('display', 'none');
    $('#progressbar').css('display', 'none');

    /***************************************************
    *
    *       initKlikNaOtpremanje   === Slanje
    *
    *********************************************************/
    otpremac_fajlova.initKlikNaOtpremanje = function()
    {
        var otpremanje = $('#otpremanje');
        otpremanje.unbind('click');
        otpremanje.click(function()
        {
            $('input:file').attr('disabled', 'disabled');
            otpremanje.attr('disabled', 'disabled');
            var dugmeSacuvaj = $('#dugme-sacuvaj');
            dugmeSacuvaj.attr('disabled', 'disabled');
            dugmeSacuvaj.addClass('onemoguceno');
            $('#otkazi').css('display', 'block');
            $('#progressbar').css('display', 'block');
            $('#progres-labela').css('display', 'block');
            $('#progressbar').progressbar({value:0});
        });
    };

    otpremac_fajlova.serijalizuj = function()
    {
        var fajlovi = Array();
        var tabela = $('#galerija').find('#uploader').find('#filelist-kontejner').find('#filelist');

        //ako se brise makar i jedan fajl, svi fajlovi se azuriraju zbog redosleda
        var statusi = tabela.find('.status');
        var zaBrisanje = tabela.find('.brisanje');
        if(zaBrisanje.size() > 0)        
            statusi.addClass('azuriranje');        
        var trFajlovi = tabela.find('.slika-na-serveru');
        for( var i=0, redosled=0, br=trFajlovi.length; i<br; i++, redosled++)
        {
            var fajl = new Object();
          //id fajla
            fajl.id = this.dajIdDeo('fajl_', trFajlovi[i].id);
            var tr = $(trFajlovi[i]);
          //odredjivanje statusa fajla(nov ili star) i komande koju treba izvrsiti na serveru
            var tdStatus = tr.children('#status_'+fajl.id);
            if( tdStatus.hasClass('greska'))
            {
                redosled--;
                continue;
            }
            if(tdStatus.hasClass('status-staro'))
                fajl.status = 'staro';
            else if(tdStatus.hasClass('status-poslato'))
                fajl.status = 'novo';
            else
                alert('Greška sistema, fajl mora da ima status star ili nov');
            if(tdStatus.hasClass('brisanje'))
            {
                fajl.komanda = 'brisanje';
                redosled--;
            }
            else if( fajl.status === 'novo')
                 fajl.komanda = 'dodavanje'; //fajl je nov
            else if(tdStatus.hasClass('azuriranje'))
            {                
                fajl.komanda = 'azuriranje';
            }
            else
               continue; //fajl nije oznacen ni za brisanje, ni za azuriranje, a nije ni nov i zato ga preskacem
          //fajl.id - na pocetku for petlje
          //fajl.status
          //fajl.komanda
//            fajl.naziv = tr.children('#naziv_'+fajl.id).children('.slika-naziv-div')[0].innerHTML;
            fajl.naziv = tr.find('#naziv_'+fajl.id).attr('title');
            fajl.tekst = tr.find('#tekst_'+fajl.id).html();
            fajl.alt = tr.find('#alt_'+fajl.id).val();
            fajl.title = tr.find('#title_'+fajl.id).val();
            fajl.rotacija = getStepenRotacije(tr.find('#rotacija_'+fajl.id).val());            
            fajl.prikaz = tr.find('#prikaz_'+fajl.id).val();
            fajl.redosled = redosled;
            fajlovi.push(fajl);
        }
        return JSON.stringify(fajlovi);
    };

    function getStepenRotacije(index)
    {
        index = parseInt(index);
        switch(index)
        {
            case 0:
                return 0;
            case 1:
                return 90;
            case 2:
                return 270;
            case 3:
                return 180;
        }
        return 0;
    }

    /*************************************************************
    *
    *       Rukovanje greskama
    *
    **************************************************************/
    otpremac_fajlova.ukloniFajloveSaGreskom = function()
    {
        if(this.idFajlovaSaGreskom == null)
            return;
        var nazivi = '';
        for(var i=0, br=this.idFajlovaSaGreskom.length; i<br; i++)
        {
            var id = this.idFajlovaSaGreskom[i];
            var element = $('#naziv_'+id+ ' .slika-naziv-div')[0];
            if(element != null)
            {
                nazivi += element.innerHTML;
                if( i != br-1)
                    nazivi += ', ';
            }
            $('#fajl_' + id).remove();

        }
        this.idFajlovaSaGreskom = null;
        alert('Sledeći fajlovi ne mogu biti otpremljeni jer nisu odgovarajućeg tipa: \\n\\n' + nazivi);
    };

    otpremac_fajlova.oznaciNedefinisane = function()
    {
        if(this.idNedefinisaniFajlovi == null)
            return;
        for(var i=0, br=this.idNedefinisaniFajlovi.length; i<br; i++)
        {
            var id = this.idNedefinisaniFajlovi[i];
            var element = $('#status_'+ id);
            if( element[0] != null)
            {
                element.addClass('greska');
                element[0].innerHTML = nedefinisano;
            }
        }
        this.idNedefinisaniFajlovi = null;
    };

    /*******************************************
    *
    *    Klik na otkazi
    *
    ********************************************/
    otpremac_fajlova.initKlikNaOtkazi = function()
    {
        var dugmeOtkazi = $('#otkazi');
        dugmeOtkazi.unbind('click');
        dugmeOtkazi.click(function()
        {
            otpremac_fajlova.stop();
            var statusi = $('.status-cekanje');
            for (var i = 0, l = statusi.length; i < l; i++)
            {
                var id = otpremac_fajlova.dajIdDeo('status_', statusi[i].id);
                $('#fajl_'+id).remove();
            }            
            initOtpremanjeFajlova(sirina, visina, kvalitet, urlSkripte, csrf, slikaOtpremljeno, slikaCekanje, slikaStaro, slikaStaroBrisanje, slikaOtpremljenoBrisanje, nedefinisano);            
        });
    };

    //dijalog.init = function()
    function Dijalog()
    {
        var self = this;

        if(opisEditor === null)
        {
            opisEditor = CKEDITOR.replace('dijalog-tekst',
            {
                toolbar:
                [
                    ['Source'],
                    ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
                    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
                    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']               
                ],
                width: '560px',
                language:'sr',
                height: '200px'
            });
        }

        function prikaziSliku(tr)
        {
            var urlElement = tr.find('#urlth_' + self.tekuciId);
            var url = '';
            var title = '';
            if(urlElement.size() > 0)
            {
                url = urlElement.val();
                title = 'Pridružena galeriji.';
            }
            else if(tr.hasClass('slika-na-serveru') && tr.find('.status').hasClass('status-poslato'))
            {
                title = 'Nalazi se na serveru ali još uvek nije pridružena galeriji.';
                var ekstenzija = getNaziv(tr).split('.').pop();
                url = '/images/uploads/'+self.tekuciId+'.'+ekstenzija+'?'+ getBrojZaPonovo();
            }
            else
            {
                title = 'Nije otpremljena na server.';
                url = '/images/sajt/slika.png';
            }
            var slika = $('#podesavanje-slike').find('#dijalog-slika');
            slika.html('<img style="height:139px;width:185px;" src="'+url+'?'+getBrojZaPonovo()+'" title="'+title+'"/>');
        }

        function prikaziPodatke(tr)
        {
            self.tekuciId = otpremac_fajlova.dajIdDeo('fajl_', tr[0].id);            
            prikaziSliku(tr);
            var id = self.tekuciId;
            var naziv = getNaziv(tr);
            var status = getStatusTxt(tr);
            var alt = getAlt(tr);
            var title =  getTitle(tr);
            var tekst = tr.find('#tekst_'+id).html();
            var prikaz = tr.find('#prikaz_'+id).val();
            var rotacija = getRotacija(tr);

            var dlg = $('#podesavanje-slike');
            dlg.find('#dijalog-naziv').text(naziv);
            dlg.find('#dijalog-status').text(status);
            dlg.dialog('option', 'title', 'Slika: '+naziv);
            dlg.find('#dijalog-title').val(title);
            dlg.find('#dijalog-alt').val(alt);
            opisEditor.setData(tekst, function(){opisEditor.resetUndo();});
            var check = dlg.find('#dijalog-prikaz')[0];
            if(prikaz !== '0')
                check.checked = true;
            else
                check.checked = false;
            dlg.find('#dijalog-rotacija').prop('selectedIndex', rotacija);
            osveziKontrole(tr);
        }

        function getRotacija(tr)
        {
            return tr.find('#rotacija_'+self.tekuciId).val();
        }

        function getStatusTxt(tr)
        {
            var txt = '';
            var status = tr.find('.status');
            if(status.hasClass('status-poslato'))
            {
                if(status.hasClass('brisanje'))
                    txt = 'Otpremljena, nakon snimanja biće izbrisana.';
                else
                    txt = 'Otpremljena, nakon snimanja biće pridružena galeriji.'
            }
            else if(status.hasClass('status-staro'))
            {
                if(status.hasClass('brisanje'))
                    txt = 'Deo je galerije, nakon snimanja ta veza će biti izbrisana.';
                else
                    txt = 'Deo je galerije.'
            }
            else if(status.hasClass('status-cekanje'))
            {
                txt = 'Čeka slanje na server.'
            }
            return txt;
        }
              
        function getNaziv(tr)
        {
            //return tr.find('.slika-naziv-div').text();
            return tr.find('#naziv_'+self.tekuciId).attr('title');
        }

        function getTitle(tr)
        {
            return tr.find('#title_'+self.tekuciId).val();
        }

        function getUrl(tr)
        {
            return tr.find('#url_'+self.tekuciId).val();
        }
        function getUrlTh(tr)
        {
            return tr.find('#urlth_'+self.tekuciId).val();
        }

        function getAlt(tr)
        {
            return tr.find('#alt_'+self.tekuciId).val();
        }

        function osveziKontrole(tr)
        {
            var sledeciTr = tr.next();
            if(sledeciTr.size() === 0)
                sledeca.addClass('sledeca-onemoguceno');
            else
                sledeca.removeClass('sledeca-onemoguceno');
            var prethodniTr = tr.prev();
            if(prethodniTr.size() === 0)
                prethodna.addClass('prethodna-onemoguceno');
            else
                prethodna.removeClass('prethodna-onemoguceno');
            if(tr.find('.status').hasClass('status-staro'))
                $('#umetanje-glavni').removeClass('nevidjlivo');
            else
                $('#umetanje-glavni').addClass('nevidjlivo');
        }

        function upisiPodatke()
        {
            var dlg = $('#podesavanje-slike');
            var alt =  dlg.find('#dijalog-alt').val();
            var title = dlg.find('#dijalog-title').val();
            var tekst = opisEditor.getData(); //dlg.find('#dijalog-tekst').val();
            var prikaz = dlg.find('#dijalog-prikaz')[0].checked;
            var rotacija = dlg.find('#dijalog-rotacija').prop("selectedIndex");
            var id = self.tekuciId;
            var tr = self.getTekuciTr();
            tr.find('#alt_'+id).val(alt);
            tr.find('#title_'+id).val(title);
            tr.find('#tekst_'+id).html(tekst);
            tr.find('#prikaz_'+id).val(prikaz ? 1 : 0);
            tr.find('#rotacija_'+id).val(rotacija);
            tr.find('.status').addClass('azuriranje');
            var naziv = tr.find('.slika-naziv');
            if(prikaz)
                naziv.removeClass('iskljuceno-iz-galerije');
            else
                naziv.addClass('iskljuceno-iz-galerije');
            if($.trim(title) != '')
            {
                 naziv.find('.slika-naziv-div').text(title);
            }
        }

        this.getTekuciTr = function()
        {
            return $('#filelist').find('#fajl_'+this.tekuciId);
        };

        var prethodna = $("#podesavanje-slike").find('#prethodna-slika');
        prethodna.unbind('click');
        prethodna.click(function(event)
        {
            event.preventDefault();
            self.prethodna();
        });

        var sledeca = $("#podesavanje-slike").find('#sledeca-slika');
        sledeca.unbind('click');
        sledeca.click(function(event)
        {
            event.preventDefault();
            self.sledeca();
        });

        $("#podesavanje-slike").unbind( "dialogopen");
        $("#podesavanje-slike").bind( "dialogopen", function(event, ui)
        {
            self.popuni();
        });

        var umetanjeGlavni = $("#podesavanje-slike").find('#umetanje-glavni');
        umetanjeGlavni.unbind('click');
        umetanjeGlavni.click(function(e)
        {
            e.preventDefault();
            //var id = otpremac_fajlova.dajIdDeo('fajl_',dijalog.getTekuciTr().id);
            upisiPodatke();
            var tr = dijalog.getTekuciTr();
            Encoder.EncodeType = "numerical";
            var alt = Encoder.htmlEncode(getAlt(tr));
            var title = Encoder.htmlEncode(getTitle(tr));
            var imgHtml = '<img class="umetnuta-slika slika_'+self.tekuciId+
                '" src="'+getUrl(tr)+
                '" alt="'+alt+
                '" title="'+title+'"/>';

            var imgElement = CKEDITOR.dom.element.createFromHtml(imgHtml);
            glavniTekstEditor.insertElement(imgElement);
        });

        var gotovo = $("#podesavanje-slike").find('#gotovo');
        gotovo.unbind('click');
        gotovo.click(function(e)
        {
            e.preventDefault();
            upisiPodatke();
             $('#podesavanje-slike').dialog('close');
        });

        this.otvori = function(id)
        {
            this.tekuciId = id;
            $('#podesavanje-slike').dialog('open');
        };

        this.popuni = function()
        {           
            prikaziPodatke(this.getTekuciTr());
        };

        this.sledeca = function()
        {
            var sledeciTr = this.getTekuciTr().next();
            if(sledeciTr.size() > 0)
            {
                upisiPodatke();
                prikaziPodatke(sledeciTr);
            }
        };

        this.prethodna = function()
        {
            var prethodniTr = this.getTekuciTr().prev();
            if(prethodniTr.size() > 0)
            {
                upisiPodatke();
                prikaziPodatke(prethodniTr);
            }
        };

    }

    /***************************************************
    *
    *       initKlikNaNazivSlike
    *
    *********************************************************/
    otpremac_fajlova.initKlikNaNazivSlike = function()
    {
        var nazivSlike = $('#filelist td.slika-naziv');
        nazivSlike.unbind('click');
        nazivSlike.click(function()
        {
            var id = otpremac_fajlova.dajIdDeo('naziv_', this.id);
            dijalog.otvori(id);
        });
    };

    otpremac_fajlova.naCekanjuZaSlanje = function()
    {
        return $('.status-cekanje').size();
    }

    otpremac_fajlova.getTr = function(id)
    {        
        return $('#galerija').find('#uploader').find('#filelist-kontejner').find('#fajl_'+id);
    };

    function zameni(el1, el2)
    {
        var tag1 = $('<span/>').insertBefore(el1); // drop a marker in place
        var tag2 = $('<span/>').insertBefore(el2); // drop a marker in place
        tag1.replaceWith(el2);
        tag2.replaceWith(el1);
        el1.find('.status').addClass('azuriranje');
        el2.find('.status').addClass('azuriranje');
    }


    /***************************************************
    *
    *       Klik na strelicu (gore ili dole)
    *
    *********************************************************/
    otpremac_fajlova.initKlikNaStrelicu = function()
    {
        var strelicaDole = $('#filelist td.strelice .strelica-dole');
        strelicaDole.unbind('click');
        strelicaDole.click(function()
        {
            var id = otpremac_fajlova.dajIdDeo('strelica-dole_', this.id);
            var tekuci = otpremac_fajlova.getTr(id);
            var sledeci = tekuci.next();
            if(sledeci.size() > 0)
                zameni(tekuci, sledeci);
        });

        var strelicaGore = $('#filelist td.strelice .strelica-gore');
        strelicaGore.unbind('click');
        strelicaGore.click(function()
        {
            var id = otpremac_fajlova.dajIdDeo('strelica-gore_', this.id);
            var tekuci = otpremac_fajlova.getTr(id);
            var sledeci = tekuci.prev();
            if(sledeci.size() > 0)
                zameni(tekuci, sledeci);
        });
    };

    /***************************************************
    *
    *       Klik na komandu (komanda + ili -)
    *
    *********************************************************/
    otpremac_fajlova.initKlikNaKomandu = function()
    {
        var komanda = $('#filelist td.slika-komanda');
        komanda.unbind('click');

        komanda.click(function()
        {
           var id = otpremac_fajlova.dajIdDeo('komanda_', this.id);
           var status = $('#status_'+id);

           if(status.hasClass('status-cekanje')) //uklanjam fajl iz liste za slanje i iz prikaza
           {
                $.each(otpremac_fajlova.files, function(i, file)
                {
                    if(file && file.id == id)
                    {
                        otpremac_fajlova.removeFile(file);
                        $('#fajl_'+id).remove();
                        otpremac_fajlova.refresh();
                    }
                });
           }
           else if(status.hasClass('status-staro') || status.hasClass('status-poslato'))
           {
                //postavljam fajlu oznaku (klasu) brisanje i ne uklanjam ga iz prikaza
                //ali naziv ispisujem drugom bojom
                var konkretnaKomanda = $('#komanda_'+id);
                var slikaIzbrisanog = slikaStaroBrisanje;
                var slika = slikaStaro;
                if(status.hasClass('status-poslato'))
                {
                    slikaIzbrisanog = slikaOtpremljenoBrisanje;
                    slika = slikaOtpremljeno;
                }

                if( ! status.hasClass('brisanje'))//postavljam oznaku ako je vec nema
                {
                    status.addClass('brisanje');
                    status[0].innerHTML = slikaIzbrisanog;
                    konkretnaKomanda.addClass('izbrisano');
                    $('#naziv_'+id).addClass('brisanje');
                }
                else//skidam oznaku brisanje
                {
                    status.removeClass('brisanje');
                    status[0].innerHTML = slika;
                    konkretnaKomanda.removeClass('izbrisano');
                    $('#naziv_'+id).removeClass('brisanje');
                }
           }
           else
           {
                alert('pogresan status fajla!');
           }

           if(otpremac_fajlova.naCekanjuZaSlanje() == 0)
               $('#otpremanje').css('display', 'none');
     });
    };

    //na primer klasa:naziv_ i klasa_id: naziv_3443434 vraca 3443434
    otpremac_fajlova.dajIdDeo = function(klasa, klasa_id)
    {
        return klasa_id.substring(klasa.length);
    };

    otpremac_fajlova.mojInit = function()
    {
        dijalog = new Dijalog();
        //dijalog.init();
        this.initKlikNaNazivSlike();
        this.initKlikNaStrelicu();
        this.initKlikNaKomandu();
        this.initKlikNaOtpremanje();
        this.initKlikNaOtkazi();
    };

    otpremac_fajlova.bind('FilesRemoved', function(up,files)
    {
        /*
        for( var i in files)
        {
            $('#fajl_'+files[i].id).css('display', 'none');
        }    */
    });

    /****************************************************
    *
    *    FilesAdded
    *
    *****************************************************/
    otpremac_fajlova.bind('FilesAdded', function(up, files)
    {
        var postoji = false;
        for (var i in files)
        {
            postoji = true;
            var id = files[i].id;
            var noviFajl =
                '<tr class="nova-slika" id="fajl_' + id + '">' +
                    '<td class="slika-naziv" id="naziv_'+ id +'" title="'+files[i].name+'"><div class="slika-naziv-div">' + files[i].name + '</div></td>' +
                    '<td class="status status-cekanje" id="status_'+ id +'">'+slikaCekanje+'</td>' +
                    '<td id="strelice_'+id+ '" class="strelice"><div id="strelica-gore_'+id+'" class="strelica-gore"></div><div id="strelica-dole_'+id+'" class="strelica-dole"></div></td>'+
                    '<td class="slika-komanda" id="komanda_'+ id +'" title="Uklanja sliku iz galerije"></td>' +
                    '<td style="display:none;">' +
                        '<input type="hidden" id="alt_' + id +'" name="alt" value=""/>'+
                        '<input type="hidden" id="title_' + id + '" name="title" value=""/>' +                        
                        '<div id="tekst_' + id + '"></div>'+
                        '<input type="hidden" id="prikaz_' + id + '" name="tekst" value="1"/>' +
                        '<input type="hidden" id="rotacija_'+ id +'" name="rotacija" value="0"/>'
                    '</td>'+
                '</tr>';
            $('#filelist > tbody:last').append(noviFajl);
        }
        if(postoji)
        {
            $('#otpremanje').css('display', 'inline');
            $('#otpremanje').removeAttr('disabled');
        }
        otpremac_fajlova.ukloniFajloveSaGreskom();
        otpremac_fajlova.mojInit();

    });

    /****************************************************
    *
    *    UploadProgress
    *
    *****************************************************/
    otpremac_fajlova.bind('UploadProgress', function(up, file)
    {
        var element = $('#status_' + file.id)[0];
        if(element == null)
            return;
        element.innerHTML = file.percent + '%';
        $('#progres-labela')[0].innerHTML = otpremac_fajlova.total.uploaded + '/' + otpremac_fajlova.ukupanBr;
        $('#progressbar').progressbar({value:otpremac_fajlova.total.percent});
        if(file.percent == '100')
        {
            element = $('#fajl_' + file.id);
            element.removeClass('nova-slika');
            element.addClass('slika-na-serveru');
            element = $('#status_'+file.id);
            element.removeClass('status-cekanje');
            element.addClass('status-poslato');
            element[0].innerHTML = slikaOtpremljeno;
        }
    });

    /****************************************************
    *
    *    Error
    *
    *****************************************************/
    otpremac_fajlova.bind('Error', function(up, args)
    {
        switch(args.code)
        {
            case plupload.FILE_EXTENSION_ERROR:
                if(args.file != null)
                {
                    if(otpremac_fajlova.idFajlovaSaGreskom == null)
                        otpremac_fajlova.idFajlovaSaGreskom = new Array();
                    otpremac_fajlova.idFajlovaSaGreskom.push(args.file.id);

                }
                else
                {
                    alert('Izabrani fajl je pogrešne vrste. Takvi fajlovi neće biti prikazani u listi i neće biti otpremljeni.');
                }
                return;                 //IZLAZIM IZ FUNKCIJE
            case plupload.FAILED:
                alert('Greška, postoje fajlovi koji nisu otpremljeni!');
                break;
            case plupload.FILE_SIZE_ERROR:
                alert('Greška, postoje fajlovi koji zbog svoje veličine ne mogu biti otpremljeni!');
                break;
            case plupload.GENERIC_ERROR:
                alert('Došlo je do greške u vezi sa otpremačem fajlova!');
                break;
            case plupload.HTTP_ERROR:
                alert('Greška pri prenosu fajla preko mreže!');
                break;
            case plupload.IO_ERROR:
                alert('Greška pri čitanju ili upisivanju fajla!');
                break;
            case plupload.INIT_ERROR:
                alert('Greška u inicijalizaciji otpremača fajlova!');
                break;
            case plupload.SECURITY_ERROR:
                alert('Sigurosna greška otpremača fajlova!');
                break;
            default:
                alert('Došlo je do nepoznate greške!');
        }
        if(args.file != null)
        {
            if(otpremac_fajlova.idNedefinisaniFajlovi == null)
                otpremac_fajlova.idNedefinisaniFajlovi = new Array();
            otpremac_fajlova.idNedefinisaniFajlovi.push(args.file.id);
        }
    });

    /****************************************************
    *
    *    UploadFile
    *
    *****************************************************/
    otpremac_fajlova.bind('UploadFile', function(up, file)
    {
            //$('#uploader')[0].innerHTML += '<input type=\"hidden\" name=\"Slika_' + file.id + '\" value=\"' + file.name + '\" />';
    });

    /****************************************************
    *
    *    uploader.start
    *
    *****************************************************/
    $('#otpremanje')[0].onclick = function()
    {
        $('#otpremanje').css('display', 'none');
        otpremac_fajlova.ukupanBr = otpremac_fajlova.total.queued;
        $('#progres-labela')[0].innerHTML = '0/' + otpremac_fajlova.ukupanBr;
        otpremac_fajlova.start();
    };

    /****************************************************
    *
    *    UploadComplete
    *
    *****************************************************/
    otpremac_fajlova.bind('UploadComplete', function(up, files)
    {
        var dugmeSacuvaj = $('#dugme-sacuvaj');
        dugmeSacuvaj.removeAttr('disabled');
        dugmeSacuvaj.removeClass('onemoguceno');
        var labela  = $('#progres-labela')[0];
        labela.innerHTML = 'Послато : '+ labela.innerHTML;
        otpremac_fajlova.oznaciNedefinisane();

        window.FileList = null;
        initOtpremanjeFajlova(sirina, visina, kvalitet, urlSkripte, csrf, slikaOtpremljeno, slikaCekanje, slikaStaro, slikaStaroBrisanje, slikaOtpremljenoBrisanje, nedefinisano);        
    });
    
    otpremac_fajlova.mojInit();
}