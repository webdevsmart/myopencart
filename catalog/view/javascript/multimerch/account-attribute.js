$(function() {


	// Attribute list

	$('#list-attributes').dataTable( {
		"sAjaxSource": $('base').attr('href') + "index.php?route=seller/account-attribute/getAttributeTableData",
		"aoColumns": [
			{ "mData": "name"},
			{ "mData": "ag_name"},
			{ "mData": "status" },
			{ "mData": "sort_order" },
			{ "mData": "actions", "bSortable": false, "sClass": "text-right" }
		]
	});

	$('#list-attribute-groups').dataTable( {
		"sAjaxSource": $('base').attr('href') + "index.php?route=seller/account-attribute/getAttributeGroupTableData",
		"aoColumns": [
			{ "mData": "name"},
			{ "mData": "status" },
			{ "mData": "sort_order" },
			{ "mData": "actions", "bSortable": false, "sClass": "text-right" }
		]
	});

	$(document).on('click', '.icon-remove', function(e) {
		e.preventDefault();
		var url = $(this).attr('href');

		if(confirm('Are you sure?')) {
			$.ajax({
				type: "get",
				dataType: "json",
				url: url,
				beforeSend: function () {
					if ($('.alert-success').length > 0)
						$('.alert-success').text('').hide();
				},
				success: function (json) {
					if (json.error) {
						$('#error-holder').text('').append(json.error + '<BR>').show();
					} else {
						window.location.reload();
					}
				}
			})
		}
	});


	/************************************************************/


	// Attribute and attribute group form

	var lang_inputs = $('.lang-select-field');
	if(lang_inputs.length) {
		var current_language = msGlobals.config_language;
		for(var i = 0; i < lang_inputs.length; i++) {
			if($(lang_inputs[i]).data('lang') != current_language) {
				$(lang_inputs[i]).hide();
			} else {
				$(lang_inputs[i]).show();
			}
		}

		$(".select-input-lang").on("click", function() {
			var selectedLang = $(this).data('lang');
			$('.lang-select-field').each(function() {
				if ($(this).data('lang') == selectedLang) {
					$(this).show();
				} else {
					$(this).hide();
				}
			});
		});
	}

	$("#ms-submit-button").click(function() {
		var button = $(this);

		var url = $('base').attr('href') + 'index.php?route=seller/account-attribute';
		var data = '';
		var form_id = '';

		if (button.data('type') == 'attribute') {
			url += '/jxSaveAttribute';
			form_id = "form#ms-new-attribute";
		} else if (button.data('type') == 'attribute-group') {
			url += '/jxSaveAttributeGroup';
			form_id = "form#ms-new-attribute-group";
		}

		data = $(form_id).serialize();

		$.ajax({
			type: "POST",
			dataType: "json",
			url: url,
			data: data,
			beforeSend: function() {
				$('.error').html('');
				$('#error-holder').hide();
			},
			complete: function(jqXHR, textStatus) {
				if (textStatus != 'success') {
					$('#error-holder').text(msGlobals.formError).show();
					window.scrollTo(0,0);
				}
			},
			error: function() {
				$('#error-holder').empty().text(msGlobals.formError).show();
				window.scrollTo(0,0);
			},
			success: function(json) {
				if(json.errors) {
					button.button('reset');

					$('#error-holder').empty();
					$(form_id).find('.text-danger').remove();
					$(form_id).find('div.has-error').removeClass('has-error');

					for (error in json.errors) {
						if ($('[name^="' + error + '"]').length > 0) {
							$('[name^="' + error + '"]').closest('div.form-group').addClass('has-error');
							$('[name^="' + error + '"]').parents('div:first').append('<p class="error" id="error_' + error + '">' + json.errors[error] + '</p>');
						}

						$('#error-holder').append(json.errors[error] + '<BR>').show();
					}

					window.scrollTo(0,0);
				} else {
					window.location = json.redirect;
				}
			}
		});
	});

	var url = document.location.toString();
	if (url.match('#')) {
		$('.nav-tabs a[href="#' + url.split('#')[1] + '"]').tab('show');
	}
});