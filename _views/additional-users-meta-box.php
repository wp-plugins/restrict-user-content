<table class="form-table">
	<tr>
		<th scope="row"><?php _e("Add media from user ID's", 'ruc');?></th>
		<td>
			<input type="text" name="<?php echo $this->_settings_name; ?>[additional_user_ids]" value="<?php echo isset( $settings['additional_user_ids']) ? $settings['additional_user_ids'] : '';?>"><br/>
			<small><?php _e('Enter a comma separated list of any additional users who\'s media you want available to logged in users in addition to their own.');?></small>
		</td>
	</tr>
</table>