<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
	<div class="page-header">
		<div class="container-fluid">
			<div class="pull-right">
				<button type="submit" form="form-stripe" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i
					 class="fa fa-save"></i></button>
				<a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>
			</div>
			<h1><?php echo $heading_title; ?></h1>
			<ul class="breadcrumb">
				<?php foreach($breadcrumbs as $breadcrumb) { ?>
				<li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
				<?php } ?>
			</ul>
		</div>
	</div>
	<div class="container-fluid">
		<?php if(!empty($error_warning)) { ?>
		<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
		<?php } ?>
		<?php if(!empty($success)) { ?>
		<div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
		<?php } ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
			</div>
			<div class="panel-body">
				<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-stripe" class="form-horizontal">
					<div class="form-group required">
						<label class="control-label col-sm-3" for="stripe_environment">
							<span data-toggle="tooltip" title="<?php echo $entry_environment_help; ?>"><?php echo $entry_environment; ?></span>
						</label>
						<div class="col-sm-9">
							<select name="stripe_environment" class="form-control">
								<?php if($stripe_environment == 'test') { ?>
								<option value="test" selected="selected"><?php echo $text_test; ?></option>
								<option value="live"><?php echo $text_live; ?></option>
								<?php } else { ?>
								<option value="test"><?php echo $text_test; ?></option>
								<option value="live" selected="selected"><?php echo $text_live; ?></option>
								<?php } ?>
							</select>
							<small class="text-info"><?php echo $entry_environment_help; ?></small>
						</div>
					</div>
					<div class="form-group required">
						<label class="control-label col-sm-3" for="stripe_test_public_key">
							<span data-toggle="tooltip" title="<?php echo $entry_test_public_key_help; ?>"><?php echo $entry_test_public_key; ?></span>
						</label>
						<div class="col-sm-9">
							<input type="text" name="stripe_test_public_key" id="stripe_test_public_key" value="<?php echo $stripe_test_public_key; ?>" class="form-control" />
							<small class="text-info"><?php echo $entry_test_public_key_help; ?></small>
							<?php if(!empty($error_test_public_key)) { ?>
							<div class="text-danger"><?php echo $error_test_public_key; ?></div>
							<?php } ?>
						</div>
					</div>
					<div class="form-group required">
						<label class="control-label col-sm-3" for="stripe_test_secret_key">
							<span data-toggle="tooltip" title="<?php echo $entry_test_secret_key_help; ?>"><?php echo $entry_test_secret_key; ?></span>
						</label>
						<div class="col-sm-9">
							<input type="text" name="stripe_test_secret_key" id="stripe_test_secret_key" value="<?php echo $stripe_test_secret_key; ?>" class="form-control" />
							<small class="text-info"><?php echo $entry_test_secret_key_help; ?></small>
							<?php if(!empty($error_test_secret_key)) { ?>
							<div class="text-danger"><?php echo $error_test_secret_key; ?></div>
							<?php } ?>
						</div>
					</div>
					<div class="form-group required">
						<label class="control-label col-sm-3" for="stripe_live_public_key">
							<span data-toggle="tooltip" title="<?php echo $entry_live_public_key_help; ?>"><?php echo $entry_live_public_key; ?></span>
						</label>
						<div class="col-sm-9">
							<input type="text" name="stripe_live_public_key" id="stripe_live_public_key" value="<?php echo $stripe_live_public_key; ?>" class="form-control" />
							 <small class="text-info"><?php echo $entry_live_public_key_help; ?></small>
							<?php if(!empty($error_live_public_key)) { ?>
							<div class="text-danger"><?php echo $error_live_public_key; ?></div>
							<?php } ?>
						</div>
					</div>
					<div class="form-group required">
						<label class="control-label col-sm-3" for="stripe_live_secret_key">
							<span data-toggle="tooltip" title="<?php echo $entry_live_secret_key_help; ?>"><?php echo $entry_live_secret_key; ?></span>
						</label>
						<div class="col-sm-9">
							<input type="text" name="stripe_live_secret_key" id="stripe_live_secret_key" value="<?php echo $stripe_live_secret_key; ?>" class="form-control" />
							 <small class="text-info"><?php echo $entry_live_secret_key_help; ?></small>
							<?php if(!empty($error_live_secret_key)) { ?>
							<div class="text-danger"><?php echo $error_live_secret_key; ?></div>
							<?php } ?>
						</div>
					</div>
					<div class="form-group required">
						<label class="control-label col-sm-3" for="stripe_order_success_status_id">
							<span data-toggle="tooltip" title="<?php echo $entry_order_success_status_help; ?>"><?php echo $entry_order_success_status; ?></span>
						</label>
						<div class="col-sm-9">
							<select name="stripe_order_success_status_id" id="stripe_order_success_status_id" class="form-control">
								<?php foreach($order_statuses as $order_status) { ?>
								<?php if($order_status['order_status_id'] == $stripe_order_success_status_id) { ?>
								<option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
								<?php } else { ?>
								<option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
								<?php } ?>
								<?php } ?>
							</select>
							<small class="text-info"><?php echo $entry_order_success_status_help; ?></small>
						</div>
					</div>
					<div class="form-group required">
						<label class="control-label col-sm-3" for="stripe_order_failed_status_id">
							<span data-toggle="tooltip" title="<?php echo $entry_order_failed_status_help; ?>"><?php echo $entry_order_failed_status; ?></span>
						</label>
						<div class="col-sm-9">
							<select name="stripe_order_failed_status_id" id="stripe_order_failed_status_id" class="form-control">
								<?php foreach($order_statuses as $order_status) { ?>
								<?php if($order_status['order_status_id'] == $stripe_order_failed_status_id) { ?>
								<option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
								<?php } else { ?>
								<option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
								<?php } ?>
								<?php } ?>
							</select>
							<small class="text-info"><?php echo $entry_order_failed_status_help; ?></small>
						</div>
					</div>
					<div class="form-group required">
						<label class="control-label col-sm-3" for="stripe_status">
							<span data-toggle="tooltip" title="<?php echo $entry_status_help; ?>"><?php echo $entry_status; ?></span>
						</label>
						<div class="col-sm-9">
							<select name="stripe_status" class="form-control">
								<?php if($stripe_status) { ?>
								<option value="1" selected="selected"><?php echo $text_enabled; ?></option>
								<option value="0"><?php echo $text_disabled; ?></option>
								<?php } else { ?>
								<option value="1"><?php echo $text_enabled; ?></option>
								<option value="0" selected="selected"><?php echo $text_disabled; ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="form-group required">
						<label class="control-label col-sm-3" for="stripe_debug">
							<span data-toggle="tooltip" title="<?php echo $entry_debug_help; ?>"><?php echo $entry_debug; ?></span>
						</label>
						<div class="col-sm-9">
							<select name="stripe_debug" class="form-control">
								<?php if($stripe_debug) { ?>
								<option value="1" selected="selected"><?php echo $text_enabled; ?></option>
								<option value="0"><?php echo $text_disabled; ?></option>
								<?php } else { ?>
								<option value="1"><?php echo $text_enabled; ?></option>
								<option value="0" selected="selected"><?php echo $text_disabled; ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label" for="stripe_sort_order"><?php echo $entry_sort_order; ?></label>
						<div class="col-sm-9">
							<input type="text" name="stripe_sort_order" value="<?php echo $stripe_sort_order; ?>" placeholder="<?php echo $entry_sort_order; ?>"
							 id="stripe_sort_order" class="form-control" />
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<?php echo $footer; ?>