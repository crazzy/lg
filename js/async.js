/*
 * LG - Looking Glass
 *
 * @author: Johan Hedberg <mail@johan.pp.se>
 *
 * @license: See LICENSE file here: https://github.com/crazzy/lg
 */
var lg_async_error = false;
var lg_has_data = false;
var lg_request_complete = false;
var lg_async_id = null;
var lg_async_nextchunk = null;
var lg_async_timer = null;
function lg_async_handle_error(jqXHR, textStatus, errorThrown) {
	window.lg_async_error = true;
	$("form").after('<h3>Error!</h3><p class="errmsg">There was an error sending/handling the request, please check the input data and try again.</p>');
}
function lg_async_handle_rlimit() {
	window.lg_async_error = true;
	$("form").after('<h3>Error!</h3><p class="errmsg">Your request was blocked because of ratelimiting, please wait a while and try again!</p>');
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
			jqXHR.getAllResponseHeaders(); // For some odd fucking reason this needs to be called before checking invidual headers - otherwise they're not found....
			var lg_status = jqXHR.getResponseHeader('X-LG-Async-Status');
			if(lg_status == 'complete') {
				window.lg_request_complete = true;
			}
			if(lg_status == "error") {
				window.lg_async_error = true;
				lg_async_handle_error(jqXHR, textStatus, 'error');
				return false;
			}
			if(lg_status == "init") {
				window.lg_async_timer = setTimeout(lg_async_handler, 500);
				return true;
			}
			if(lg_status == "wait") {
				window.lg_async_timer = setTimeout(lg_async_handler, 500);
				return true;
			}
			if(data.trim() == "") {
				if(lg_status != 'complete') {
					window.lg_async_timer = setTimeout(lg_async_handler, 500);
					return true;
				}
			}
			if(window.lg_has_data === false) {
				window.lg_has_data = true;
				$("form").after("<pre>" + data + "</pre>");
			}
			else {
				$("pre").html($("pre").html() + data);
			}
			if(false === window.lg_request_complete) {
				window.lg_async_nextchunk += 1;
				window.lg_async_timer = setTimeout(lg_async_handler, 500);
			}
		}
	});
}
$(document).ready(function() {
	$("form").each(function(index, value) {
		$(value).submit(function() {
			$("h3").remove();
			$(".errmsg").remove();
			$("pre").remove();
			window.lg_async_error = false;
			window.lg_has_data = false;
			window.lg_request_complete = false;
			window.lg_async_id = null;
			window.lg_async_nextchunk = null;
			clearTimeout(window.lg_async_timer);
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
						lg_async_handle_error(jqXHR, textStatus, 'LG_no_async_id_from_server');
						return false;
					}
					if(async_id == "error") {
						lg_async_handle_error(jqXHR, textStatus, 'LG_no_async_id_from_server');
						return false;
					}
					if(async_id == "ratelimit") {
						lg_async_handle_rlimit();
						return false;
					}
					window.lg_async_id = async_id;
					window.lg_has_data = false;
					window.lg_request_complete = false;
					window.lg_async_timer = setTimeout(lg_async_handler, 500);
					return false;
				},
				error: lg_async_handle_error
			});
			return false;
		});
	});

});

