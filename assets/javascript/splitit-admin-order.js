( function( $ ) {
	"use strict";

	var decodeHTML = function (html) {
        var txt = document.createElement('textarea');
        txt.innerHTML = html;
        return txt.value;
    };

	$(document).ready(function(){
		var mainElem = jQuery('.column-billing_address .description:contains(payment-title-checkout)');
		if(mainElem != undefined){
			var splititHTML = mainElem.html();
			if(splititHTML != undefined){
				var viaText = splititHTML.substr(0,splititHTML.indexOf(' '));
				var elem = splititHTML.substr(splititHTML.indexOf(' ')+1);
				mainElem.html(viaText + ' ' + decodeHTML(elem));
			}
		}
	});

})(jQuery);
