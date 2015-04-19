<?php require "themes/{$global_config['theme']}/header.php"; ?>
	<form action="<?php echo LG_FORMACTION; ?>" method="post">
		<fieldset>
			<div class="labels">
				<label for="<?php echo LG_FORM_LOOKUP; ?>">Hostname, IPv4 or IPv6 address:</label>
				<label for="<?php echo LG_FORM_ROUTER; ?>">Choose router:</label>
			</div>
			<div class="fields">
				<input type="text" name="<?php echo LG_FORM_LOOKUP; ?>" id="<?php echo LG_FORM_LOOKUP; ?>" value="<?php if(isset($lookup)) echo $lookup; ?>" />
				<select name="<?php echo LG_FORM_ROUTER; ?>" id="<?php echo LG_FORM_ROUTER; ?>">
					<?php
						foreach($routers as $rtrname => $rtr) {
						if(isset($router) && ($rtrname == $router)) {
							$selected = 'selected="selected"';
						}
						else {
							$selected = '';
						}
						echo "<option $selected name=\"{$rtrname}\">{$rtrname}</option>\n";
					}
					?>
				</select>
			</div>
			<div class="controls">
				<input type="radio" checked="checked" id="<?php echo LG_FORM_TYPE_PING; ?>" name="<?php echo LG_FORM_LOOKUPTYPE; ?>" value="<?php echo LG_FORM_TYPE_PING; ?>" />
				<label for="<?php echo LG_FORM_TYPE_PING; ?>">Ping</label>
				<input type="radio" id="<?php echo LG_FORM_TYPE_TRACEROUTE; ?>" name="<?php echo LG_FORM_LOOKUPTYPE; ?>" value="<?php echo LG_FORM_TYPE_TRACEROUTE; ?>" />
				<label for="<?php echo LG_FORM_TYPE_TRACEROUTE; ?>">Traceroute</label>
				<input type="radio" id="<?php echo LG_FORM_TYPE_BGP; ?>" name="<?php echo LG_FORM_LOOKUPTYPE; ?>" value="<?php echo LG_FORM_TYPE_BGP; ?>" />
				<label for="<?php echo LG_FORM_TYPE_BGP; ?>">BGP</label>
				<input type="radio" id="<?php echo LG_FORM_TYPE_DNS; ?>" name="<?php echo LG_FORM_LOOKUPTYPE; ?>" value="<?php echo LG_FORM_TYPE_DNS; ?>" />
				<label for="<?php echo LG_FORM_TYPE_DNS; ?>">DNS</label>
				<input type="submit" value="Look it up!" />
			</div>
			<div class="clearfix"></div>
		</fieldset>
	</form>
	<?php if(isset($error) && $error): ?>
	<h3>Error!</h3>
	<div class="errmsg"><?php echo $error_str; ?></div>
	<?php endif; ?>
	<?php if(isset($result) && (false!==$result) && !empty($result)): ?>
	<h3>Result</h3>
	<pre><?php echo htmlspecialchars($result, ENT_QUOTES); ?></pre>
	<?php endif; ?>
<?php require "themes/{$global_config['theme']}/footer.php"; ?>
