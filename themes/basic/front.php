<?php require "themes/{$global_config['theme']}/header.php"; ?>
	<div class="container">
		<form action="<?php echo LG_FORMACTION; ?>" method="post">
			<fieldset>
				<div class="labels row text-center">
						<label for="<?php echo LG_FORM_LOOKUP; ?>">Hostname, IPv4 or IPv6 address:</label>
						<label for="<?php echo LG_FORM_ROUTER; ?>">Choose router:</label>
				</div>
				<div class="fields row text-center">
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
				<div class="controls row text-center">
					<input type="radio" checked="checked" id="<?php echo LG_FORM_TYPE_PING; ?>" name="<?php echo LG_FORM_LOOKUPTYPE; ?>" value="<?php echo LG_FORM_TYPE_PING; ?>" />
					<label for="<?php echo LG_FORM_TYPE_PING; ?>">Ping</label>
					<input type="radio" id="<?php echo LG_FORM_TYPE_TRACEROUTE; ?>" name="<?php echo LG_FORM_LOOKUPTYPE; ?>" value="<?php echo LG_FORM_TYPE_TRACEROUTE; ?>" />
					<label for="<?php echo LG_FORM_TYPE_TRACEROUTE; ?>">Traceroute</label>
					<input type="radio" id="<?php echo LG_FORM_TYPE_BGP; ?>" name="<?php echo LG_FORM_LOOKUPTYPE; ?>" value="<?php echo LG_FORM_TYPE_BGP; ?>" />
					<label for="<?php echo LG_FORM_TYPE_BGP; ?>">BGP</label>
					<input type="radio" id="<?php echo LG_FORM_TYPE_DNS; ?>" name="<?php echo LG_FORM_LOOKUPTYPE; ?>" value="<?php echo LG_FORM_TYPE_DNS; ?>" />
					<label for="<?php echo LG_FORM_TYPE_DNS; ?>">DNS</label>
					<input class="btn-info" type="submit" value="Look it up!" />
				</div>
			</fieldset>
		</form>
		<?php if(isset($error) && $error): ?>
		<h3 class="text-center">>Error!</h3>
		<p class="errmsg"><?php echo $error_str; ?></p>
		<?php endif; ?>
		<?php if(isset($result) && (false!==$result) && !empty($result)): ?>
		<pre><?php echo htmlspecialchars($result, ENT_QUOTES); ?></pre>
		<?php endif; ?>
	</div>
<?php require "themes/{$global_config['theme']}/footer.php"; ?>
