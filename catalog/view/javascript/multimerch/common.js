$(function() {
	$.fn.dataTableExt.sErrMode = 'throw';

	if (typeof config_language != 'undefined') {
		$.extend($.fn.dataTable.defaults, {
			"oLanguage": {
				"sUrl": config_language
			}
		});
	}
	
	$.extend($.fn.dataTable.defaults, {
		"bProcessing": true,
		"bSortCellsTop": true,
		"bServerSide": true,
		"sPaginationType": "full_numbers",
		"aaSorting": [],
		"bAutoWidth": false,
		"bLengthChange": false,
		"sDom": 'rt<"pagination"pi><"clear">',
		"asStripeClasses": [],
		// todo insert proper value
		"iDisplayLength": 10
	});
	
	$("body").delegate(".dataTable .filter input[type='text']", "keyup",  function() {
		$(this).parents(".dataTable").dataTable().fnFilter(this.value, $(this).parents(".dataTable").find("thead tr.filter td").index($(this).parent("td")));
	});

	$(document).ready(function() {
		$('.input-date-datepicker').datetimepicker({
				format: 'YYYY-MM-DD',
				pickTime: false,
				useCurrent: false,
				focusOnShow: false,
				showClear: true
			})
			.on('dp.change', function() {
				$(this).trigger('keyup');
			})
			.attr('readonly', 'readonly');

		$('.daterangepicker div.ranges div.range_inputs .applyBtn').removeClass('btn-success');
		$('.daterangepicker div.ranges div.range_inputs .applyBtn').addClass('btn-primary');
	});
});