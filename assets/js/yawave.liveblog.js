if (jQuery("#yawave-liveblog-results")[0]){	

	var timeleft = 15;
	var liveblog_reload_intervall_time = timeleft + '000';
	
	if (jQuery("#yawave-liveblog-results")[0]){	
		jQuery('#liveblog-counter-time').html('15');		
		jQuery.ajax({
			url: '/wp-admin/admin-ajax.php?action=js_liveblog_update&lbid=' + liveblog_id + '&sorting=' + liveblog_sorting,
			data: [],
			type: 'GET',
			success: function (res) {
				var result = jQuery.parseJSON(res);
				jQuery('#yawave-liveblog-results').html(result['html']);
				jQuery('#liveblog_loaded_items').val(result['ids']);
				jQuery('#liveblog_all_items').val(result['count_all_posts']);			
			}
		});	
		var myIntervalLiveblog = window.setInterval(function () {
			liveblog_intervall_call();
		}, liveblog_reload_intervall_time);
	}
	
	function liveblog_intervall_call() {
		var page = jQuery('#liveblog-actually-show-page').val();
		var loaded_items = jQuery('#liveblog_loaded_items').val();
			
		jQuery.ajax({
			url: '/wp-admin/admin-ajax.php?action=js_liveblog_update&lbid=' + liveblog_id + '&page=' + page + '&intervallload=1' + '&itemids=' + loaded_items + '&sorting=' + liveblog_sorting,
			data: [],
			type: 'GET',
			success: function (res) {	
				if(res != "null") {				
					var result = jQuery.parseJSON(res);					
					if(result['no_items'] == 0) {					
						if(result['before_id'] != null) {						
							jQuery('#yawave-liveblog-results div[data-tickerid="' + result['before_id'] + '"]').before(result['html']);						
						} else {						
							jQuery('.no-liveblog-posts').remove();
							jQuery('#yawave-liveblog-results').prepend(result['html']);						
						}	
						jQuery('#liveblog_all_items').val(result['count_all_posts']);			
					}	
					if(result['ids'] !== undefined) {
						var loaded_items = jQuery('#liveblog_loaded_items').val();
						jQuery('#liveblog_loaded_items').val(loaded_items + ',' + result['ids']);		
					}	
				}			
			}
		});
	}
	
	
	var liveblog_timer_show = setInterval(function(){
  	var actTimeSek = jQuery('#liveblog-counter-time').html();
  	if(actTimeSek == 0) {
	  	actTimeSek = 16;
  	}
  	actTimeSek -= 1;
  	jQuery('#liveblog-counter-time').html(actTimeSek);
	}, 1000);
	
	
	function liveblog_load_next_page_entrys(pagenr) {	
		jQuery.ajax({
			url: '/wp-admin/admin-ajax.php?action=js_liveblog_update&lbid=' + liveblog_id + '&page=' + pagenr + '&itemids=' + loaded_items + '&sorting=' + liveblog_sorting,
			data: [],
			type: 'GET',
			success: function (res) {			
				if(res != "null") {				
					var result = jQuery.parseJSON(res);				
					jQuery('#yawave-liveblog-results').append(result['html']);				
					var loaded_items = jQuery('#liveblog_loaded_items').val();				
					jQuery('#liveblog_loaded_items').val(loaded_items + ',' + result['ids']);			
				}
			}
		});
		
	}
	
	var page = 2;
	var currentscrollHeight = 0;
	
	if( /Android|webOS|iPhone|iPad|Mac|Macintosh|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
	 	var scroll_pixel_height = 1200;
	} else {
		var scroll_pixel_height = 600;
	}
	
	jQuery(window).on("scroll", () => {	
		var liveblog_all_items = jQuery('#liveblog_all_items').val();
		if(liveblog_all_items > 15) {
			const scrollHeight = jQuery(document).height();
			const scrollPos = Math.floor($(window).height() + jQuery(window).scrollTop());
			const isBottom = scrollHeight - scroll_pixel_height < scrollPos;	
			if (isBottom && currentscrollHeight < scrollHeight) {				
				if(page == 2) {		
					var loaded_items = jQuery('#liveblog_loaded_items').val();			
					jQuery('.liveticker-line').animate({opacity: 0.5}, 200);			
					jQuery.ajax({
						url: '/wp-admin/admin-ajax.php?action=js_liveblog_update&lbid=' + liveblog_id + '&page=' + page + '&sorting=' + liveblog_sorting,
						data: [],
						type: 'GET'
					})
					.done(function( data ){
						if(data != "null") {
							var result = jQuery.parseJSON(data);
							jQuery('#yawave-liveblog-results').append(result['html']);
							var loaded_items = jQuery('#liveblog_loaded_items').val();
							jQuery('#liveblog_loaded_items').val(loaded_items + ',' + result['ids']);
							jQuery('.liveticker-line').animate({opacity: 1}, 200);		
							jQuery('#liveblog-actually-show-page').val(page);
							page = page + 1;
						}
					});	
					currentscrollHeight = scrollHeight;		
				}
			}
		}
	});

}