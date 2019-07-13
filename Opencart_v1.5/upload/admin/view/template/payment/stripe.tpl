<?php echo $header; ?>
<div id="content">
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>
	<?php if ($error_warning) { ?>
	<div class="warning"><?php echo $error_warning; ?></div>
	<?php } ?>
	<div class="box">
		<div class="heading">
			<h1><img src="view/image/payment.png" alt="" /> <?php echo $heading_title; ?></h1>
			<div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a href="<?php echo $cancel; ?>" class="button"><?php echo $button_cancel; ?></a></div>
		</div>
		<div class="content">
			<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
				<table class="form">
					<tr>
						<td><span class="required">*</span> <?php echo $entry_environment; ?></td>
						<td>
							<select name="stripe_environment">
								<?php if($stripe_environment == 'test') { ?>
								<option value="test" selected="selected"><?php echo $text_test; ?></option>
								<option value="live"><?php echo $text_live; ?></option>
								<?php } else { ?>
								<option value="test"><?php echo $text_test; ?></option>
								<option value="live" selected="selected"><?php echo $text_live; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><span class="required">*</span> <?php echo $entry_test_public_key; ?></td>
						<td><input type="text" name="stripe_test_public_key" value="<?php echo $stripe_test_public_key; ?>" />
							<?php if ($error_test_public_key) { ?>
							<span class="error"><?php echo $error_test_public_key; ?></span>
							<?php } ?>
						</td>
					</tr>
					<tr>
						<td><span class="required">*</span> <?php echo $entry_test_secret_key; ?></td>
						<td><input type="text" name="stripe_test_secret_key" value="<?php echo $stripe_test_secret_key; ?>" />
							<?php if ($error_test_secret_key) { ?>
							<span class="error"><?php echo $error_test_secret_key; ?></span>
							<?php } ?>
						</td>
					</tr>
					<tr>
						<td><span class="required">*</span> <?php echo $entry_live_public_key; ?></td>
						<td><input type="text" name="stripe_live_public_key" value="<?php echo $stripe_live_public_key; ?>" />
							<?php if ($error_live_public_key) { ?>
							<span class="error"><?php echo $error_live_public_key; ?></span>
							<?php } ?>
						</td>
					</tr>
					<tr>
						<td><span class="required">*</span> <?php echo $entry_live_secret_key; ?></td>
						<td><input type="text" name="stripe_live_secret_key" value="<?php echo $stripe_live_secret_key; ?>" />
							<?php if ($error_live_secret_key) { ?>
							<span class="error"><?php echo $error_live_secret_key; ?></span>
							<?php } ?>
						</td>
					</tr>
					<tr>
						<td><span class="required">*</span> <?php echo $entry_order_success_status; ?></td>
						<td>
							<select name="stripe_order_success_status_id">
								<?php foreach ($order_statuses as $order_status) { ?>
								<?php if ($order_status['order_status_id'] == $stripe_order_success_status_id) { ?>
								<option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
								<?php } else { ?>
								<option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
								<?php } ?>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><span class="required">*</span> <?php echo $entry_order_failed_status; ?></td>
						<td>
							<select name="stripe_order_failed_status_id">
								<?php foreach ($order_statuses as $order_status) { ?>
								<?php if ($order_status['order_status_id'] == $stripe_order_failed_status_id) { ?>
								<option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
								<?php } else { ?>
								<option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
								<?php } ?>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $entry_status; ?></td>
						<td>
							<select name="stripe_status">
								<?php if ($stripe_status) { ?>
								<option value="1" selected="selected"><?php echo $text_enabled; ?></option>
								<option value="0"><?php echo $text_disabled; ?></option>
								<?php } else { ?>
								<option value="1"><?php echo $text_enabled; ?></option>
								<option value="0" selected="selected"><?php echo $text_disabled; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $entry_debug; ?></td>
						<td>
							<select name="stripe_debug">
								<?php if ($stripe_debug) { ?>
								<option value="1" selected="selected"><?php echo $text_enabled; ?></option>
								<option value="0"><?php echo $text_disabled; ?></option>
								<?php } else { ?>
								<option value="1"><?php echo $text_enabled; ?></option>
								<option value="0" selected="selected"><?php echo $text_disabled; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $entry_sort_order; ?></td>
						<td><input type="text" name="stripe_sort_order" value="<?php echo $stripe_sort_order; ?>" size="1" /></td>
					</tr>
				</table>
			</form>
		</div>
	</div>
</div>
<?php echo $footer; ?>