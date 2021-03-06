<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
	<div class="page-header">
		<div class="container-fluid">
			<h1><?php echo $ms_question_manage; ?></h1>

			<ul class="breadcrumb">
				<?php foreach ($breadcrumbs as $breadcrumb) { ?>
					<li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
				<?php } ?>
			</ul>
		</div>
	</div>

	<div class="container-fluid">
		<div class="alert alert-danger" style="display: <?php echo (isset($error_warning) && $error_warning) ? 'block' : 'none'; ?>;"><i class="fa fa-exclamation-circle"></i><?php if (isset($error_warning) && $error_warning) { echo $error_warning; } ?>
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>

		<?php if (isset($success) && $success) { ?>
			<div class="alert alert-success" style="display: <?php echo (isset($success) && $success) ? 'block' : 'none'; ?>;"><i class="fa fa-check-circle"></i> <?php if(isset($success) && $success) { echo $success; } ?>
				<button type="button" class="close" data-dismiss="alert">&times;</button>
			</div>
		<?php } ?>

		<div class="panel panel-default">
			<div class="panel-heading">
				<h1><?php echo $ms_question_breadcrumbs; ?></h1>
			</div>
			<div class="panel-body">
				<div class="table-responsive">
					<form class="form-inline" action="" method="post" enctype="multipart/form-data" id="form-question">
						<table class="list mmTable table table-bordered table-hover" style="text-align: center" id="list-questions">
							<thead>
								<tr>
									<td class="large"><?php echo $ms_question_column_product; ?></td>
									<td class="large"><?php echo $ms_question_column_customer; ?></td>
									<td><?php echo $ms_question_column_answer; ?></td>
									<td class="medium"><?php echo $ms_question_column_date_added; ?></td>
									<td class="medium"><?php echo $ms_action; ?></td>
								</tr>

								<tr class="filter">
									<td><input type="text"/></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</form>
				</div>
			</div>
		</div>

	</div>
</div>
<script type="text/javascript">
    var msGlobals = {
        token: '<?php echo $token; ?>'
    };
</script>
<?php echo $footer; ?>