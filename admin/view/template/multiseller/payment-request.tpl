<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
	<div class="page-header">
		<div class="container-fluid">
			<h1><?php echo $heading; ?></h1>
			<ul class="breadcrumb">
				<?php foreach ($breadcrumbs as $breadcrumb) { ?>
					<li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
				<?php } ?>
			</ul>

			<div class="pull-right">
				<a id="ms-button-pay" data-toggle="tooltip" title="<?php echo $ms_payment_new; ?>" class="btn btn-primary"><i class="fa fa-credit-card"></i></a>
				<button style="display: none;" id="ms-button-delete" type="button" data-toggle="tooltip" title="Delete" class="btn btn-danger ms-delete" data-referrer="invoice"><i class="fa fa-trash-o"></i></button>
			</div>
		</div>
	</div>
	<div class="container-fluid">
		<?php if ($error_warning) { ?>
			<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
				<button type="button" class="close" data-dismiss="alert">&times;</button>
			</div>
		<?php } ?>
		<?php if ($success) { ?>
			<div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
				<button type="button" class="close" data-dismiss="alert">&times;</button>
			</div>
		<?php } ?>

		<div class="error-holder"></div>

		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $heading; ?></h3>
			</div>
			<div class="panel-body">
				<div class="table-responsive">
					<form class="form-inline" action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
						<table class="list mmTable table table-bordered table-hover" style="text-align: center" id="list-payment-requests">
							<thead>
								<tr>
									<td class="tiny"><input type="checkbox" onclick="$(document).find('#list-payment-requests input[name*=\'selected\']').prop('checked', $(this).prop('checked'));" /></td>
									<td class="small"><?php echo $ms_id; ?></td>
									<td class="medium"><?php echo $ms_type; ?></td>
									<td class="medium"><?php echo $ms_seller; ?></td>
									<td class="small"><?php echo $ms_amount; ?></td>
									<td><?php echo $ms_description; ?></td>
									<td class="medium"><?php echo $ms_date_created; ?></td>
									<td class="medium"><?php echo $ms_status; ?></td>
									<td class="medium"><?php echo $ms_pg_payment_number; ?></td>
									<td class="medium"><?php echo $ms_date_paid; ?></td>
								</tr>
								<tr class="filter">
									<td></td>
									<td><input type="text"/></td>
									<td><input type="text"/></td>
									<td><input type="text"/></td>
									<td><input type="text"/></td>
									<td></td>
									<td><input type="text" class="input-date-datepicker"/></td>
									<td><input type="text"/></td>
									<td><input type="text"/></td>
									<td><input type="text" class="input-date-datepicker"/></td>
								</tr>
							</thead>

							<tbody>
							</tbody>
						</table>
					</form>
				</div>
			</div>
		</div>

	</div>
</div>

<script type="text/javascript">
	var msGlobals = {
		token: '<?php echo $token; ?>',
		ms_pg_request_error_select_payment_request: '<?php echo htmlspecialchars($ms_pg_request_error_select_payment_request, ENT_QUOTES, "UTF-8"); ?>'
	};
</script>
<?php echo $footer; ?>