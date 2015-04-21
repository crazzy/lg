var lg_async_error = false;
var lg_has_data = false;
var lg_request_complete = false;
var lg_async_id = null;
var lg_async_nextchunk = null;
function lg_async_handle_error(jqXHR, textStatus, errorThrown) {
	window.lg_async_error = true;
	alert(textStatus);
	alert(errorThrown);
}
function lg_async_handler() {
	$.ajax({
		url: '/async.php',
		method: 'POST',
		data: {
			async_id: window.lg_async_id,
			nextchunk: window.lg_async_nextchunk
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
			if(data.trim() == "") {
				return false;
			}
			jqXHR.getAllResponseHeaders(); // For some odd fucking reason this needs to be called before checking invidual headers - otherwise they're not found....
			if(jqXHR.getResponseHeader('X-LG-Async-Status') == 'complete') {
				window.lg_request_complete = true;
			}
			if(window.lg_has_data === false) {
				window.lg_has_data = true;
				$("form").after("<pre>" + data + "</pre>");
			}
			else {
				$("pre").html($("pre").html() + data);
			}
		}
	});
	if(window.lg_async_error) {
		return false;
	}
	if(window.lg_has_data === false) {
		setTimeout(lg_async_handler, 500);
		return true;
	}
	if(window.lg_request_complete === true) { // For extra safety resetting everything now
		window.lg_async_error = false;
		window.lg_has_data = false;
		window.lg_request_complete = false;
		window.lg_async_id = null;
		window.lg_async_nextchunk = null;
		return true;
	}
	window.lg_async_nextchunk += 1;
	setTimeout(lg_async_handler, 500);
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
					window.lg_async_nextchunk = 0;
					async_id = async_id.toString().trim();
					if(async_id == "") {
						alert("errar!");
					}
					window.lg_async_id = async_id;
					window.lg_has_data = false;
					window.lg_request_complete = false;
					setTimeout(lg_async_handler, 500);
					return false;
				},
				error: lg_async_handle_error
			});
			return false;
		});
	});

});

