var lg_async_error = false;
var lg_has_data = false;
var lg_request_complete = false;
function lg_async_handle_error(jqXHR, textStatus, errorThrown) {
	window.lg_async_error = true;
	alert(textStatus);
	alert(errorThrown);
}
$(document).ready(function() {
	$("form").each(function(index, value) {
		window.lg_async_error = false;
		$(value).submit(function() {
			$.ajax({
				url: '/',
				context: document.body,
				method: 'POST',
				data: {
					lg_lookup: $("#lg_lookup").val(),
					lg_router: $("#lg_router").val(),
					lg_lookuptype: $('input[name=lg_lookuptype]:checked').val(),
					async: 'true'
				},
				success: function(async_id, textStatus, jqXHR) {
					var nextchunk = 0;
					async_id = async_id.toString().trim();
					if(async_id == "") {
						alert("errar!");
					}
					do {
						window.lg_has_data = false;
						window.lg_request_complete = false;
						$.ajax({
							url: '/async.php',
							method: 'POST',
							data: {
								async_id: async_id,
								nextchunk: nextchunk
							},
							error: lg_async_handle_error,
							success: function(data, textStatus, jqXHR) {
								if(data == "error") {
									window.lg_async_error = true;
									return false;
								}
								if(data == "init") {
									return false;
								}
								if(data == "wait") {
									return false;
								}
								if(jqXHR.getResponseHeader('X-LG-Async-Status') == 'complete') {
									window.lg_request_complete = true;
								}
								if(!window.lg_has_data) {
									$("form").after("<pre>" + data + "</pre>");
								}
								window.lg_has_data = true;
								$("pre").html($("pre").html() + data);
							}
						});
						if(window.lg_async_error) {
							break;
						}
						if(!window.lg_has_data) {
							continue;
						}
						if(window.lg_request_complete) {
							break;
						}
						nextchunk += 1;
					} while(true);
					return true;
				},
				error: lg_async_handle_error
			});
			return false;
		});
	});

});

