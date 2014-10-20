 /* function monkeyPatchAutocomplete() {

      // Don't really need to save the old fn,
      // but I could chain if I wanted to
      var oldFn = $.ui.autocomplete.prototype._renderItem;

      $.ui.autocomplete.prototype._renderItem = function( ul, item) {
          var re = new RegExp("\\b" + this.term, "i") ;
          var t = item.label.replace(re,"<span style='font-weight:bold;color:Blue;'>" + this.term + "</span>");

          return $( "<li></li>" )
              .data( "item.autocomplete", item )
              .append( "<a>" + t + "</a>" )
              .appendTo( ul );
      };
  }*/
function split( val ) {return val.split( /,\s*/ );}
function extractLast( term ) {return split( term ).pop();}

function monkeyPatchAutocomplete() {

      // don't really need this, but in case I did, I could store it and chain
      var oldFn = $.ui.autocomplete.prototype._renderItem;

      $.ui.autocomplete.prototype._renderItem = function( ul, item) {
          termin = extractLast(this.term);
          var re = new RegExp(termin, 'i');
          var t = item.label.replace(re,"<span style='font-weight:bold;color:Blue;'>" +
                  termin +
                  "</span>");
          return $( "<li></li>" )
              .data( "item.autocomplete", item )
              .append( "<a>" + t + "</a>" )
              .appendTo( ul );
      };
  }
