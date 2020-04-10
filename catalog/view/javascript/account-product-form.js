$(function() {
	// additional data block
	var $mmAdditionalData = $('#mm_additional_data');
	if ($mmAdditionalData.children().filter(':not(legend)').length > 0) {
		$mmAdditionalData.show();
	}

	var $mmSearchOptimization = $('#mm_search_optimization');
	if ($mmSearchOptimization.children().filter(':not(legend)').length > 0) {
		$mmSearchOptimization.show();
	}

	var $mmShipping = $('#mm_product_shipping');
	var $mmDimensions = $('#mm_product_dimensions');
	var $mmDigitalProduct = $('select[name="product_is_digital"]');

	// filters
	$('input[name=\'filter\']').autocomplete({
		'source': function(request, response) {
			$.ajax({
				url: 'index.php?route=catalog/filter/autocomplete&filter_name=' +  encodeURIComponent(request),
				dataType: 'json',
				success: function(json) {
					response($.map(json, function(item) {
						return {
							label: item['name'],
							value: item['filter_id']
						}
					}));
				}
			});
		},
		'select': function(item) {
			$('input[name=\'filter\']').val('');

			$('#product-filter' + item['value']).remove();

			$('#product-filter').append('<div id="product-filter' + item['value'] + '"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="product_filter[]" value="' + item['value'] + '" /></div>');
		}
	});

	$('#product-filter').delegate('.fa-minus-circle', 'click', function() {
		$(this).parent().remove();
	});

	// Related
	$('input[name=\'related\']').autocomplete({
		'source': function(request, response) {
			$.ajax({
				url: 'index.php?route=catalog/product/jxAutocompleteRelated&filter_name=' +  encodeURIComponent(request) +
				'&product_id=' + encodeURIComponent(msGlobals.product_id) + '&seller_id=' + encodeURIComponent(msGlobals.seller_id),
				dataType: 'json',

				success: function(json) {
					var existing_product_relateds = [];
					$.map($(document).find('input[name^="product_related"]'), function(related_product) {
						existing_product_relateds.push($(related_product).val());
					});

					response($.map(json, function(item) {
						if($.inArray(item['product_id'], existing_product_relateds) == -1) {
							return {
								label: item['name'],
								value: item['product_id']
							}
						}
					}));
				}
			});
		},
		'select': function(item) {
			$('input[name=\'related\']').val('');

			$('#product-related' + item['value']).remove();

			$('#product-related').append('<div id="product-related' + item['value'] + '"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="product_related[]" value="' + item['value'] + '" /></div>');
		}
	});

	$('#product-related').delegate('.fa-minus-circle', 'click', function() {
		$(this).parent().remove();
	});

	if ($("input[name='product_enable_shipping']:checked").val() == 0) {
		$('#shipping_tab').hide();
	}

	$("body").on("change", "input[name='product_enable_shipping']", function() {
		$('#shipping_tab').toggle();
		if (msGlobals.config_enable_quantities == 2) $("input[name='product_quantity']").parents('.form-group').toggle();
	});

	if($("#image-holder").length > 0)
		new Sortable($("#image-holder")[0])

	if($("#file-holder").length > 0)
		new Sortable($("#file-holder")[0])

	$("body").delegate(".ms-price-dynamic", "propertychange input paste focusout", function(){
		$(".alert.ms-commission span").load($('base').attr('href') + "index.php?route=seller/account-product/jxGetFee&price=" + $(".ms-price-dynamic").val());
	});
	$(".alert.ms-commission span").load($('base').attr('href') + "index.php?route=seller/account-product/jxGetFee&price=" + $(".ms-price-dynamic").val());

	$("body").delegate(".mm_price", "propertychange input paste focusout", function(){
		if(msGlobals.fee_priority == 1) {
			getCategoryFee();
		}
	});

	$("body").delegate(".date", "focusin", function(){
		$(this).datetimepicker({pickTime: false});
	});

	$("body").delegate(".datetime", "focusin", function(){
		$(this).datetimepicker({pickTime: true, pickDate: true});
	});

	$("body").delegate(".time", "focusin", function(){
		$(this).datetimepicker({pickDate: false});
	});

	$('body').delegate("a.ms-button-delete", "click", function() {
		$(this).parents('tr').remove();
	});

	$('body').delegate(".mm_clone", "click", function() {
		var lastRow = $(this).closest('fieldset').find('.form-group:last input:last').attr('name');

		if (typeof lastRow == "undefined") {
			var newRowNum = 1;
		} else {
			var newRowNum = parseInt(lastRow.match(/[0-9]+/)) + 1;
		}

		var newRow = $(this).closest('fieldset').find('.form-group.ffSample').clone();

		newRow.find('input,select,textarea').attr('name', function(i,name) {
			return name.replace('[0]','[' + newRowNum + ']');
		});

		$(this).closest('fieldset').find('.form-group:last').after(newRow.removeClass('ffSample'));
	});

	$('body').delegate("a.ffClone", "click", function() {
		// Get table position in the fieldset. Needed for Shipping fieldset
		var tableNum = ':first';
		if($(this).hasClass('elsewhere-shipping-clone')) {
			tableNum = ':last';
		}

		var lastRow = $(this).closest('fieldset').find('tbody' + tableNum + ' tr:last input:last').attr('name');
		if (typeof lastRow == "undefined") {
			var newRowNum = 1;
		} else {
			var newRowNum = parseInt(lastRow.match(/[0-9]+/)) + 1;
		}

		var newRow = $(this).closest('fieldset').find('tbody' + tableNum + ' tr.ffSample').clone();
		newRow.find('input,select,textarea').attr('name', function(i,name) {
			return name.replace('[0]','[' + newRowNum + ']');
		});
		$(this).closest('fieldset').find('tbody' + tableNum).append(newRow.removeClass('ffSample'));
	});
	
	// Manufacturer
	$('input[name=\'product_manufacturer\']').autocomplete({
		'source': function(request, response) {
			$.ajax({
				url: $('base').attr('href') + 'index.php?route=seller/account-product/jxautocomplete&filter_name=' +  encodeURIComponent(request),
				dataType: 'json',
				success: function(json) {
					json.unshift({
						manufacturer_id: 0,
						name: msGlobals.text_none
					});

					response($.map(json, function(item) {
						return {
							label: item['name'],
							value: item['manufacturer_id']
						}
					}));
				}
			});
		},
		'select': function(item) {
			$('input[name=\'product_manufacturer\']').val(item.label);
			$('input[name=\'product_manufacturer_id\']').val(item.value);
		}
	});

	// Attributes
	$('#mm_attribute_new').autocomplete({
		'delay': 250,
		'source': function(request, response) {
			$.ajax({
				url: 'index.php?route=seller/account-product/jxAutocompleteAttributes&filter_name=' +  encodeURIComponent(request),
				dataType: 'json',
				success: function(json) {
					var existing_attrs = [];
					$.map($(document).find('.mm_attribute').not('.ffSample'), function(attribute_row) {
						existing_attrs.push($(attribute_row).find('input[name^="product_attribute"][name$="[name]"]').val());
					});

					response($.map(json, function(item) {
						if($.inArray(item.name, existing_attrs) == -1) {
							return {
								category: item.attribute_group,
								label: item.name,
								value: item.attribute_id
							}
						}
					}));
				}
			});
		},
		'select': function(item) {
			$(this).parents('#mm_product_attributes').find('.mm_clone').click();
			var newAttr = $(this).parents('#mm_product_attributes').find('.form-group:last');
			newAttr.find('input[name$="[attribute_id]"]').val(item['value']);
			newAttr.find('input[name^="product_attribute"][name$="[name]"]').val(item['label']);
			newAttr.find('label').append(item['label']);
			newAttr.find('textarea').focus();
			$(this).blur();
		}
	});

	$('#mm_option_new').autocomplete({
		'delay': 250,
		'source': function(request, response) {
			$.ajax({
				url: 'index.php?route=seller/account-product/jxAutocompleteOptions&filter_name=' +  encodeURIComponent(request),
				dataType: 'json',
				success: function(json) {
					response($.map(json, function(item) {
						return {
							category: item['category'],
							label: item['name'],
							value: item['option_id'],
							type: item['type'],
							option_value: item['option_value']
						}
					}));
				}
			});
		},
		'select': function(item) {
			var select = this;

			var lastRow = $(this).closest('fieldset').find('.form-group:last input:last').attr('name');

			if (typeof lastRow == "undefined") {
				var newRowNum = 1;
			} else {
				var newRowNum = parseInt(lastRow.match(/[0-9]+/)) + 1;
			}

			var newRow = $(this).closest('fieldset').find('.form-group.ffSample').clone();

			newRow.find('input,select,textarea').attr('name', function(i,name) {
				return name.replace('[0]','[' + newRowNum + ']');
			});

			$(this).closest('fieldset').find('.form-group:last').after(newRow.removeClass('ffSample'));


			var newOpt = $(this).parents('#mm_product_options').find('.form-group:last');
			newOpt.find('label').append(item['label']);

			$.get($('base').attr('href') + 'index.php?route=seller/account-product/jxRenderOptionValues&option_id=' + item['value'], function(data) {
				var data = $(data);
				data.find('input,select').attr('name', function(i,name) {
					if (name) return name.replace('product_option[0]','product_option[' + newRowNum + ']');
				});

				newOpt.append(data);
			});

			$(this).blur();
		}
	});

	// delete option value
	$('body').delegate(".option_value .mm_remove", "click", function() {
		$(this).closest('.option_value').remove();
	});

	// delete attribute
	$('body').delegate(".mm_attribute .mm_remove", "click", function() {
		$(this).closest('.mm_attribute').remove();
	});

	// delete attribute
	$('body').delegate(".mm_option .mm_remove", "click", function() {
		$(this).closest('.mm_option').remove();
	});

	$('body').delegate('table .mm_remove', 'click', function() {
		$(this).closest('tr').remove();
	});

// load options tab
	$('#tab-options').load($('base').attr('href') + 'index.php?route=seller/account-product/jxRenderOptions&product_id=' + msGlobals.product_id, function(data){
		// load existing product options
		if (msGlobals.product_id.length > 0) {
			$.get($('base').attr('href') + 'index.php?route=seller/account-product/jxRenderProductOptions&product_id=' + msGlobals.product_id, function(data) {
				$('div.options').append(data).find('input[name$="[option_id]"]').each(function(index) {
					$(this).closest('.ms-options').find('.select_option option[value="'+ $(this).val() + '"]').attr('disabled', true );
				});
			});
		}
	});

	$('body').delegate("select.select_option_value", "change", function() {
		var lastRow = $(this).closest('.mm_values').find('tbody tr.option_value:last input:last').attr('name');
		if (typeof lastRow == "undefined") {
			var newRowNum = 1;
		} else {
			var newRowNum = parseInt(lastRow.match(/[0-9]+/g).pop()) + 1;
		}
		var newVal = $(this).closest('.mm_values').find('tbody tr.ffSample').clone();

		newVal.find('.option_name').append($(this).children(':selected').text());
		newVal.find('input[name$="[option_value_id]"]').val($(this).children(':selected').val());

		newVal.find('input,select,textarea').attr('name', function(i,name) {
			if(name !== undefined) return name.replace('[product_option_value][0]','[product_option_value][' + newRowNum + ']');
		});

		$(this).closest('.mm_values').find('tbody').append(newVal.removeClass('ffSample'));

		$(this).val(0);
	});

	$(document).on('click', '.image-holder .ms-remove', function() {
		var holder = $(this).parent().parent();
		$(this).parent().remove();
		if(!holder.find('.image-holder').length){
			$('#ms-image').show();
		}
	});
	
	$(document).on('click', '.file-holder .ms-remove', function() {
		var holder = $(this).parent().parent();
		$(this).parent().remove();
		if(!holder.find('.file-holder').length){
			$('#ms-file').show();
		}
	});

	$(document).on('click', '.hidder', function(e) {
		e.preventDefault();
		$(this).parent().addClass('hidden');
	});

	$("#ms-submit-button").click(function() {
		var url = 'jxsubmitproduct';

		if (msGlobals.config_enable_rte == 1) {

			for (var instance in CKEDITOR.instances) {
				CKEDITOR.instances[instance].updateElement();
			}
		}
		$.ajax({
			type: "POST",
			dataType: "json",
			url: $('base').attr('href') + 'index.php?route=seller/account-product/'+url,
			data: $("form#ms-new-product").serialize(),
			beforeSend: function() {
				$('.error').html('');
				$('.alert-danger').hide().find('i').text('');
			},
			complete: function(jqXHR, textStatus) {
				if (textStatus != 'success') {
					$('.alert-danger:first').text(msGlobals.formError).show();
					window.scrollTo(0,0);
					setTimeout(function() {
						$(".ms-spinner" ).button('reset');
					}, 1000);
				}
			},
			error: function() {
				$('#error-holder').removeClass('hidden');
				$('#error-holder').empty();
				$('#error-holder').text(msGlobals.formError);
			},
			success: function(jsonData) {
				if (jsonData.fail) {
					$('.alert-danger:first').show().find('i').text(msGlobals.formError);
					window.scrollTo(0,0);
				} else 	if (!jQuery.isEmptyObject(jsonData.errors)) {
					if(jsonData.errors) {
						$('#error-holder').removeClass('hidden');
						$('#error-holder').empty();
						$('#error-holder').append('<a href="#" class="close hidder"><i class="fa fa-times"></i></a>');
					}
					for (error in jsonData.errors) {
						var error_key = error.replace(/[\[\]*()?]/g, "\\$&");

						$('#error-holder').append(jsonData.errors[error] + '<BR>');
						if (!jsonData.errors.hasOwnProperty(error)) {
							continue;
						}

						if ($('#error_' + error_key).length > 0) {
							$('#error_' + error_key).text(jsonData.errors[error]);
							$('#error_' + error_key).parents('.form-group').addClass('has-error');
						} else {
							$('[name^="' + error_key + '"]').nextAll('.error:first').text(jsonData.errors[error]);
							$('[name^="' + error_key + '"]').parents('.form-group').addClass('has-error');
						}

					}
					$('.alert-danger:first').show();
					window.scrollTo(0,0);
				} else if (!jQuery.isEmptyObject(jsonData.data) && jsonData.data.amount) {
					$(".ms-payment-form form input[name='custom']").val(jsonData.data.custom);
					$(".ms-payment-form form input[name='amount']").val(jsonData.data.amount);
					$(".ms-payment-form form").submit();
				} else {
					window.location = jsonData['redirect'];
				}
			}
		});
	});
	
	$('#ms-image-loader').on('click', function(e) {
		e.preventDefault();
		$('#ms-image').click();
	});
	
	$('#ms-file-loader').on('click', function(e) {
		e.preventDefault();
		$('#ms-file').click();
	});
	
	var image = initUploader('ms-image', 'index.php?route=seller/account-product/jxUploadImages', 'large', true, false);
	var file = initUploader('ms-file', 'index.php?route=seller/account-product/jxUploadDownloads', 'large', true, true);

	$('.ms-file-updatedownload').each(function() {
		var fileTag = $(this);
		var parentContainer = $(this).parents('.ms-download');

		new MSUploader(
			{
				browse_button: fileTag.attr('id'),
				preinit : {
					UploadFile: function(up, file) {
						up.settings.multipart_params.file_id = fileTag.attr('id');
					}
				}
			},
			{
				url: 'seller/account-product/jxUpdateFile',
				initSelectors: { addId: '.product_download_files', addClass: '.download.progress, #error_product_download' },
				fileUploadedCb: function (data) {
					if (!$.isEmptyObject(data.fileName)) {
						parentContainer.find('.ms-download-name').text(data.fileMask);
						parentContainer.find('input:hidden[name$="[filename]"]').val(data.fileName);
						parentContainer.find('.ms-button-download').replaceWith('<span class="ms-button-download disabled"></span>');
					}
				}
			});

	});

	if (msGlobals.config_enable_rte == 1) {

		$('.ckeditor').each(function () {
			CKEDITOR.replace(this);
		});
	}
	
	// language select
	$(".select-input-lang").on( "click", function() {
		var selectedLang = $(this).data('lang');
		$('.lang-select-field').each(function() {
			if ($(this).data('lang') == selectedLang) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
		$('.lang-chooser img').each(function() {
			if ($(this).data('lang') == selectedLang) {
				$(this).addClass('active');
			} else {
				$(this).removeClass('active');
			}
		});
	});

	// custom fields
	var cf_file_fields = $(document).find('.cf_file');
	$.each(cf_file_fields, function(key, value) {
		var custom_field_id = $(value).closest('.form-group').find('input[name="custom_field_id"]').val();
		initUploader('ms-file-' + custom_field_id, 'index.php?route=seller/account-product/jxUploadCustomFieldFile', 'large', true, true, 'product_cf');
	});

	$('.date').datetimepicker({
		pickTime: false
	});

	$('.time').datetimepicker({
		pickDate: false
	});

	$('.datetime').datetimepicker({
		pickDate: true,
		pickTime: true
	});

	// Vendor shipping
	(function() {
		var $productShippingContainer = $('#mm_product_shipping'),
			$currentRequest = null;

		$(document).on('focus', '.ac-shipping-countries', shippingFieldAutocomplete('country'));
		$(document).on('focus', '.ac-shipping-companies', shippingFieldAutocomplete('company'));

		function shippingFieldAutocomplete(shippingFieldType) {
			if (shippingFieldType == undefined) {
				shippingFieldType = 'company';
			}

			return function () {
				var $this = $(this);
				if($productShippingContainer.find('ul.dropdown-menu').length) {
					$productShippingContainer.find('ul.dropdown-menu').remove();
				}

				var referrer = '',
					itemLabelKey = 'name',
					itemValueKey = '',
					sourceUrl = function(reqEndPoint) {
						var url = 'index.php?route=seller/account-product';
						url += reqEndPoint;
						return function (request) {
							return url + '&filter_name=' + encodeURIComponent(request);
						}
					};

				// Shipping locations
				if (shippingFieldType == 'country') {
					if ($this.attr('id') && $this.attr('id') == 'mm_shipping_from_country') {
						referrer = 'country_from';
					} else {
						referrer = 'to_geo_zone';
					}

					sourceUrl = sourceUrl('/jxAutocompleteShippingCountry&referrer=' + encodeURIComponent(referrer));
					itemValueKey = 'country_id';

				// Shipping companies
				} else {
					sourceUrl = sourceUrl('/jxAutocompleteShippingMethod');
					itemValueKey = 'method_id';
				}



				$(this).autocomplete({
					'delay': 250,
					'source': function(request, response) {
						$currentRequest = $.ajax({
							url: sourceUrl(request),
							dataType: 'json',
							beforeSend: function() {
								if($currentRequest != null) {
									$currentRequest.abort();
								}
							},
							success: function(json) {
								var items = $.map(json, function(item) {
									return {
										label: item[itemLabelKey],
										value: item[itemValueKey]
									}
								});

								if(shippingFieldType == 'country' &&
									items.length > 1 && referrer != '' && referrer != 'country_from') {
										setTimeout(function() {
											$this.siblings('ul.dropdown-menu').children('li').first().css('margin-bottom', '10px');
										}, 5);
								}

								response(items.slice(0, 10));
								$currentRequest = null;
							},
							error: function(e) {
								console.error(e);
								$currentRequest = null;
							}
						});
					},
					'select': function(item) {
						$this.val(item.label);
						$this.siblings('.' + itemValueKey.replace('_', '-')).val(item.value);
					}
				});
			}
		}

		$(document).on('change', '.product_free_shipping input[type="checkbox"]', function() {
			$('.shipping-locations').toggleClass('hidden');
		});

		$('.combined_shipping_override input[type="checkbox"]').click(function() {
			$('#mm_product_shipping .grey_out').toggleClass('hidden');
		});
	})();

	// Opencart and Multimerch categories
	$(document).on('click', '.remove-category', function(e) {
		e.preventDefault();
		$(this).closest('.category-holder').remove();

		// Calculate category fees
		if(msGlobals.fee_priority == 1) {
			getCategoryFee();
		}
	});

	$(document).on('click', '.add-category', function(e) {
		e.preventDefault();
		var categoryType = $(this).closest('div').attr('id').toString().toLowerCase().split('_')[1];

		var row = $('#clone' + categoryType.charAt(0).toUpperCase() + categoryType.slice(1) + 'Row').clone();
		row.attr('id', '');

		var num_row = $(document).find('#product_' + categoryType + '_category_block .row').length + 1;
		var new_row_id = 'product_' + categoryType + '_categories_' + num_row + '_0';

		row.find('input').attr('id', new_row_id);

		$(this).before(row);
		initCategorySelectize(new_row_id, categoryType);
	});

	function getCategoryFee() {
		var product_categories_ids = $.map($('[name^="product_oc_category"]'), function (item) {
			return $(item).val();
		}).join(',');

		var price = $('.mm_price').val();

		if (product_categories_ids) {
			$.ajax({
				type: 'post',
				dataType: 'json',
				data: {'category_id': product_categories_ids, 'price': price, 'msg': msGlobals.category_based_fee},
				url: $('base').attr('href') + 'index.php?route=seller/account-product/jxGetCategoryFee',
				success: function (json) {
					if (json['rate']) {
						$('.ms-commission').find('p.rate').html(json['rate']);
					}

					if (json['type']) {
						$('.ms-commission').find('p.type').html(json['type']);
						$('.ms-commission').find('p.type').show();
					} else {
						$('.ms-commission').find('p.type').hide();
					}
				}
			});
		}
	}

	function initCategorySelectize(dom_element_id, category_type) {
		var $this = $('#' + dom_element_id);
		var $initialized = false;

		var $category_item = $this.closest('.category-item');
		var $all_category_items = $this.closest('.category-holder').find('.category-item');

		// Selectize params
		var delimiter = '~';
		var maxOptions = 1000;
		var valueField = 'category_id';
		var labelField = 'name';
		var searchField = 'name';
		var jxUrl = 'index.php?route=seller/account-product/jxAutocompleteCategories';

		/**
		 * Get category row and category parent ids.
		 * Structure is product_(type)_categories_(row_id)_(parent_id).
		 */
		var category_input = dom_element_id.replace('product_' + category_type + '_categories_', '').split('_');
		var row_id = category_input[0] ? category_input[0] : 1;
		var parent_id = category_input[1] ? category_input[1] : 0;

		var category_input_name = 'product_' + category_type + '_category_' + row_id + '[]';

		var initial_items = [];
		$.map($('input[name^="product_' + category_type + '_category_' + row_id + '[]"'), function(initial_item) {
			if ($(initial_item).attr('data-parent-id') == parent_id)
				initial_items.push($(initial_item).data('name'));
		});

		$this.selectize({
			valueField: valueField,
			labelField: labelField,
			searchField: searchField,
			maxItems: 1,
			maxOptions: maxOptions,
			preload: true,
			delimiter: delimiter,
			create: false,
			createOnBlur: false,
			selectOnTab: true,
			render: {
				option: function (item, escape) {
					return '<div>' + escape(item.name) + '</div>';
				}
			},
			load: function (query, callback) {
				$.ajax({
					url: jxUrl + '&category_id=' + parent_id + '&type=' + category_type,
					type: 'GET',
					dataType: 'json',
					error: function () {
						callback();
					},
					success: function (res) {
						callback(res);
					}
				});
			},
			onItemAdd: function(value, item) {
				if ($('input[name="' + category_input_name + '"][value="' + value + '"]').length === 0) {
					var new_category_input_id = 'product_' + category_type + '_categories_' + row_id + '_' + value;

					// if user changes previously selected category then remove old category_id value
					$.map($category_item.find('input[name="' + category_input_name + '"]'), function(item) {
						$(item).remove();
					});

					// add new category_id value
					$this.after('<input type="hidden" name="' + category_input_name + '" value="' + value + '" />');

					// calculate category commissions
					if(msGlobals.fee_priority == 1) {
						getCategoryFee();
					}

					// if user changes previously selected category then remove all child categories of this category
					$.map($all_category_items, function(item) {
						if ($(item)[0] == $category_item[0])
							$(item).nextAll('.category-item').remove();
					});

					// check newly selected category has child categories
					$.ajax({
						url: jxUrl + '&category_id=' + value + '&type=' + category_type,
						type: 'GET',
						dataType: 'json',
						success: function (res) {
							var html = '';
							html += '<div class="category-item">';
							html += '	<input type="text" id="' + new_category_input_id + '" />';
							html += '</div>';

							// if newly selected category has childs, initalize Selectize for created input
							if (res.length > 0) {
								$this.closest('.category-item').after(html);

								initCategorySelectize(new_category_input_id, category_type)
							}
						}
					});
				}
			},
			onItemRemove: function(value) {
				$category_item.find('input[name="' + category_input_name + '"]').remove();

				$.map($all_category_items, function(item) {
					if ($(item)[0] == $category_item[0])
						$(item).nextAll('.category-item').remove();
				});
			},
			onLoad: function(data) {
				if(!$initialized) {
					var selectize = $this[0].selectize;

					$.each(initial_items, function(key, item) {
						selectize.addItem(selectize.search(item).items[0].id);
					});

					$initialized = true;
				}
			}
		});
	}

	$.map($('input[id^="product_ms_categories"]'), function(item) {
		if ($(item).closest('.category-holder').attr('id') != 'cloneMsRow')
			initCategorySelectize($(item).attr('id'), 'ms');
	});

	$.map($('input[id^="product_oc_categories"]'), function(item) {
		if ($(item).closest('.category-holder').attr('id') != 'cloneOcRow')
			initCategorySelectize($(item).attr('id'), 'oc');
	});

	/* Digital products shipping */
	digitalProductShipping();
	$(document).on('change', $mmDigitalProduct, digitalProductShipping);

	function digitalProductShipping() {
		if ($mmShipping.length > 0) {
			if($mmDigitalProduct.val() == 1) {
				$mmShipping.hide();
			} else {
				$mmShipping.show();
			}
		}
	}

	/* Digital products dimensions */
	digitalProductDimensions();
	$(document).on('change', $mmDigitalProduct, digitalProductDimensions);

	function digitalProductDimensions() {
		if ($mmDimensions.length > 0) {
			if($mmDigitalProduct.val() == 1) {
				$mmDimensions.hide();
			} else {
				$mmDimensions.show();
			}
		}
	}
});
