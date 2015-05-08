window.taveo_ajax = (function(window, document, jQuery) {
    var app = {};

    app.cache = function() {
        app.$ajax_button = jQuery('.add_to_taveo_button');
        app.$ajax_button_parent = jQuery('#returned_url');
        app.$prompt_button = jQuery('#taveo_post_btn');
        app.$link_table = jQuery('#tlinktable');

    };

    app.init = function() {
        app.cache();
        app.$prompt_button.on('click', function(){
        	app.tpopup(taveossdata.pagepost_url);
        	//unnecessary to do in an ajax call, but leave here for potential future use
        	//var data = {
        	//		'action': 'taveo_get_permalink',
        	//		'pid': taveossdata.pagepost_id
        	//	};
        	//	jQuery.post(ajaxurl, data, function(response) {
        	//		alert('Got this from the server: ' + response);
        	//		app.tpopup(response);
        	//	});
        });      
		//load Taveo data on the page
		app.loadtdata();
		
    };

    app.tpopup = function(url) {
    	//var url = decodeURIComponent(taveossdata.page_permalink);
		app.mbox = new Impromptu({state0: {
			title: 'Create Taveo Link?',
			html: 'Destination URL: ' + url + '<br /><label>Comment (optional): <input type="text" name="comment" placeholder="Tweet of post.."></label><br />' +
				  	'<p>Adding a comment in highly encouraged. Make it short and unique. It it used to remember what this Link was used for. </p>',
			buttons: {"Create" : true, "Cancel": false},
			focus: 0,
			submit:function(e,v,m,f){
				if (v==0) {
					//mbox.close();
					return true;
				}
				jQuery.ajax({
					dataType: "json",
					url: taveossdata.create_api_url,
					type: 'GET',
					data: { "apikey": taveossdata.api_key, "destination" : url, "comment" : f.comment },
					success: function(data) {
						if (data.status == "ok") {
							var pmpt = new Impromptu("Success - Link url is: " + data.url);
							//refresh link table
							app.loadtdata();
						}
						else {
							var pmpt = new Impromptu("Fail! - " + data.msg);
						}
					},
					error: function( xhr, textStatus, errorThrown) {
						console.log(xhr.responseText);
						var pmpt = new Impromptu("Error " + xhr.status + " (" + xhr.responseText + ")");
					}
				});
					 
			}
			 
		}});  
	}
	
	app.loadtdata = function() {
		var url = taveossdata.pagepost_url;
		var body = app.$link_table.children('tbody');
		jQuery.ajax({
			dataType: "json",
			url: taveossdata.by_dest_url,
			type: 'GET',
			data: { "apikey": taveossdata.api_key, "destination" : url },
			success: function(data) {
				if (data.status == "ok") {
					//success
					console.log(data);
					jQuery("#curtlinks").html(data.count);
					jQuery("#tlinkmsg").show();
					app.loadlinktable(data.links);
					//jQuery("#tlinkdata").html(data.links +"YEAA");
				}
				else {
					body.empty();
					body.append('<tr><td colspan="9"> Taveo Error: ' + data.msg + '</td></tr>'  );
				}
			},
			error: function( xhr, textStatus, errorThrown) {
				var msg =  xhr.status + " (" + xhr.responseText + ")"
				body.append('<tr><td colspan="9"> Taveo Server Error: ' + msg + '</td></tr>'  );
			}
		});	
	
	}
	app.loadlinktable = function(data) {
		var body = app.$link_table.children('tbody');
		
		if ( jQuery.fn.DataTable.fnIsDataTable( app.$link_table[0] ) ) {
			console.log("destroying existing table!");
			app.$link_table.dataTable().fnDestroy();
		}
		body.empty();
		var dlen = data.length;
		console.log("dlen is " + dlen);
		for (var i = 0; i < dlen; i++) {
			body.append('\
					<tr>\
						<td>' + data[i].url + '</td> \
						<td>' + data[i].createdhuman + '</td> \
						<td>' + data[i].createdsec + '</td> \
						<td>' + data[i].lastclickhuman + '</td> \
						<td>' + data[i].lastclicksec + '</td> \
						<td>' + data[i].clicks + '</td> \
						<td>' + data[i].comment + '</td> \
						<td>' + app.getstatehtml(data[i].state) + '</td> \
						<td> <a title="View Stats in Taveo" \
							href="https://admin.taveo.net/linkstats?lid='+ data[i].id + '" \
							target="_blank"> <span class="dashicons dashicons-chart-bar"></span></a> \
					</tr> \
			');
		}
		//now transform to datatable
		app.$link_table.DataTable({
			"destroy": true,
    		"columnDefs": [
    		               { "visible": false, "searchable": false ,"targets": [2,4] },
    		               { "orderData": [4], "targets":[3] },
    		               { "orderData": [2], "targets":[1] },
    		               { "orderable": false, "targets": [6,7] }
    		],
    		"order" : [[2,"asc"]],
		});
		app.$link_table.tooltip();
	}
	app.getstatehtml = function(state) {
		if(state == "Enabled") {
			return '<span title="Enabled" class="dashicons dashicons-controls-play tgreen"></span>';
		}
		else {
			return '<span title="Disabled" class="dashicons dashicons-controls-pause tred"></span>';
		}
	}
	
	jQuery(document).ready(app.init);
    return app;

})(window, document, jQuery);
