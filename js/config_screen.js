window.taveo_dashboard_ajax = (function(window, document, jQuery) {
	var app = {};
	
	app.cache = function() {
		app.$dtable = jQuery('#taveo_links');
	};
    
    app.init = function() {
        app.cache();
        //check if we had a API error or not
        if (app.$dtable.find('tr:last').html() != '') {
        	app.$dtable.DataTable({
        		"autoWidth": false,
        		"columnDefs": [
        		               { "width": "22px", "targets": 6 },
        		               { "width": "75px", "targets": 1 },
        		               { "className": "taveo_truncate", "targets": 1 },
        		               { "visible": false, "searchable": false ,"targets": 3 },
        		               { "orderData": [3], "targets":[2] },
        		               { "orderable": false, "targets": [5,6] }

        		],
        		"order" : [[2,"asc"]],
        	}); 
        	jQuery( document ).tooltip();
        }
		
    };
	
    jQuery(document).ready(app.init);
    return app;
})(window, document, jQuery);
