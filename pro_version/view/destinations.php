<?php 
$options = WC_Order_Export_Main_Settings::get_settings();
if( $options['limit_button_test'] == 1)
	$limit_text = __( 'First suitable order', 'woocommerce-order-export' );
else	
	$limit_text = __( 'All suitable orders', 'woocommerce-order-export' );
$test_mode_info =  __( 'this button sends ', 'woocommerce-order-export' );

?>
<div id="my-shedule-destination" class="my-block">
    <div class="wc-oe-header"><?php _e( 'Destination', 'woocommerce-order-export' ) ?></div>
	<?php
	if ( isset( $settings['destination']['type'] ) && ! is_array( $settings['destination']['type'] ) ) {
		$settings['destination']['type'] = array( $settings['destination']['type'] );
	}
	?>
    <div class="button-secondary output_destination"><input type="checkbox" name="settings[destination][type][]"
                                                            value="email"
			<?php if ( isset( $settings['destination']['type'] ) AND in_array( 'email',
					$settings['destination']['type'] ) ) {
				echo 'checked';
			} ?>
        > <?php _e( 'Email', 'woocommerce-order-export' ) ?>
        <span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span>
    </div>

    <div class="button-secondary output_destination"><input type="checkbox" name="settings[destination][type][]"
                                                            value="ftp"
			<?php if ( isset( $settings['destination']['type'] ) AND in_array( 'ftp',
					$settings['destination']['type'] ) ) {
				echo 'checked';
			} ?>
        > <?php _e( 'FTP', 'woocommerce-order-export' ) ?>
        <span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span>
    </div>

    <div class="button-secondary output_destination"><input type="checkbox" name="settings[destination][type][]"
                                                            value="sftp"
			<?php if ( isset( $settings['destination']['type'] ) AND in_array( 'sftp',
					$settings['destination']['type'] ) ) {
				echo 'checked';
			} ?>
        > <?php _e( 'SFTP', 'woocommerce-order-export' ) ?>
        <span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span>
    </div>

    <div class="button-secondary output_destination"><input type="checkbox" name="settings[destination][type][]"
                                                            value="http"
			<?php if ( isset( $settings['destination']['type'] ) AND in_array( 'http',
					$settings['destination']['type'] ) ) {
				echo 'checked';
			} ?>
        > <?php _e( 'HTTP POST', 'woocommerce-order-export' ) ?>
        <span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span>
    </div>

    <div class="button-secondary output_destination"><input type="checkbox" name="settings[destination][type][]"
                                                            value="folder"
			<?php if ( isset( $settings['destination']['type'] ) AND in_array( 'folder',
					$settings['destination']['type'] ) ) {
				echo 'checked';
			} ?>
        > <?php _e( 'Directory', 'woocommerce-order-export' ) ?>
        <span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span>
    </div>

    <div class="button-secondary output_destination"><input type="checkbox" name="settings[destination][type][]"
                                                            value="zapier"
			<?php if ( isset( $settings['destination']['type'] ) AND in_array( 'zapier',
					$settings['destination']['type'] ) ) {
				echo 'checked';
			} ?>
        > <?php _e( 'Zapier', 'woocommerce-order-export' ) ?>
        <span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span>
    </div>

    <div class="padding-bottom-10 set-destination my-block" id="email" style="display: none;">
        <div class="wc-oe-header"><?php _e( 'Email settings', 'woocommerce-order-export' ) ?></div>
        <div class="wc_oe-row">
            <div class="col-100pr">
                <label>
                    <div><?php _e( 'From email', 'woocommerce-order-export' ) ?>
                        <i>(<?php _e( 'leave blank to use default', 'woocommerce-order-export' ) ?>)</i></div>
                    <input type="text" name="settings[destination][email_from]" class="width-100"
                           value="<?php echo $tab->get_value( $settings, "[destination][email_from]" ); ?>">
                </label>
            </div>
        </div>
        <div class="wc_oe-row">
            <div class="col-100pr">
                <label>
                    <div><?php _e( 'From name', 'woocommerce-order-export' ) ?>
                        <i>(<?php _e( 'leave blank to use default', 'woocommerce-order-export' ) ?>)</i></div>
                    <input type="text" name="settings[destination][email_from_name]" class="width-100"
                           value="<?php echo $tab->get_value( $settings,
						       "[destination][email_from_name]" ); ?>">
                </label>
            </div>
        </div>
        <div class="wc_oe-row">
            <div class="col-100pr">
                <label>
                    <div><?php _e( 'Email subject', 'woocommerce-order-export' ) ?></div>
                    <input type="text" name="settings[destination][email_subject]" class="width-100"
                           value="<?php echo $tab->get_value( $settings,
						       "[destination][email_subject]" ); ?>">
                </label>
            </div>
        </div>

        <div class="wc_oe-row">
            <div class="col-100pr">
                <label>
                    <div><?php _e( 'Email body', 'woocommerce-order-export' ) ?> <a
                                id="show-email-body"><?php _e( 'Edit', 'woocommerce-order-export' ); ?></a></div>

                    <div id="destination-email-body">
                        <textarea name="settings[destination][email_body]"
                                  class="email_body_textarea"><?php echo $tab->get_value( $settings,
		                        "[destination][email_body]" ); ?></textarea>
                    </div>
                </label>
                <br>
                <div class=""><input name="settings[destination][email_body_append_file_contents]"
                                     type="checkbox" <?php echo $tab->get_value( $settings,
						"[destination][email_body_append_file_contents]" ) ? 'checked' : ''; ?>><?php _e( 'Append file contents to email body',
						'woocommerce-order-export' ) ?></div>
                </label>
            </div>
        </div>

        <div class="wc_oe-row">
            <div class="col-100pr">
                <label>
                    <div><?php _e( 'Recipient(s)', 'woocommerce-order-export' ) ?></div>
                    <textarea name="settings[destination][email_recipients]"
                              class="width-100"><?php echo $tab->get_value( $settings,
							"[destination][email_recipients]" ); ?></textarea>
                </label>
            </div>
        </div>
        <div class="wc_oe-row">
            <div class="col-100pr">
                <label>
                    <div><?php _e( 'Cc Recipient(s)', 'woocommerce-order-export' ) ?></div>
                    <textarea name="settings[destination][email_recipients_cc]"
                              class="width-100"><?php echo $tab->get_value( $settings,
							"[destination][email_recipients_cc]" ); ?></textarea>
                </label>
            </div>
        </div>
        <div class="wc_oe-row">
            <div class="col-100pr">
                <label>
                    <div><?php _e( 'Bcc Recipient(s)', 'woocommerce-order-export' ) ?></div>
                    <textarea name="settings[destination][email_recipients_bcc]"
                              class="width-100"><?php echo $tab->get_value( $settings,
							"[destination][email_recipients_bcc]" ); ?></textarea>
                </label>
            </div>
        </div>

	<div>
	    <label>
		<input name="settings[destination][email_send_separate_files_in_one_email]" type="checkbox" <?php echo $tab->get_value($settings, "[destination][email_send_separate_files_in_one_email]") ? 'checked' : ''; ?>>
		<?php _e('Send separate files in one email', 'woocommerce-order-export') ?>
	    </label>
	</div>

        <div class="wc_oe-row">
            <div class="col-100pr">
                <label>
                    <div class="wrap"><input name="" class="wc_oe_test my-test-button add-new-h2" data-test="email"
                                             type="button" value="<?php _e( 'Test', 'woocommerce-order-export' ) ?>" >
                    </div>
                </label>
				<i>(<?php echo $test_mode_info ?> <b><?php echo $limit_text ?></b>)</i>
            </div>
        </div>
    </div>

    <div class="padding-bottom set-destination my-block" id="ftp" style="display: none;">
        <div class="wc-oe-header"><?php _e( 'FTP settings', 'woocommerce-order-export' ) ?></div>
        <div class="wc_oe-row">
            <div class="col-50pr">
                <label>
                    <div><?php _e( 'Server name', 'woocommerce-order-export' ) ?></div>
                    <input type="text" name="settings[destination][ftp_server]"
                           value="<?php echo $tab->get_value( $settings, "[destination][ftp_server]" ); ?>">
                </label>
            </div>
            <div class="col-50pr">
                <label>
                    <div><?php _e( 'Port', 'woocommerce-order-export' ) ?></div>
                    <input type="text" name="settings[destination][ftp_port]"
                           value="<?php echo $tab->get_value( $settings, "[destination][ftp_port]" ); ?>"
                           placeholder="21">
                </label>
            </div>
        </div>
        <div class="wc_oe-row">

            <div class="col-50pr">
                <label>
                    <div><?php _e( 'Username', 'woocommerce-order-export' ) ?></div>
                    <input type="text" name="settings[destination][ftp_user]"
                           value="<?php echo $tab->get_value( $settings, "[destination][ftp_user]" ); ?>">
                </label>
            </div>
            <div class="col-50pr">
                <label>
                    <div><?php _e( 'Password', 'woocommerce-order-export' ) ?></div>
                    <input type="text" name="settings[destination][ftp_pass]"
                           value="<?php echo $tab->get_value( $settings, "[destination][ftp_pass]" ); ?>">
                </label>
            </div>
        </div>
        <div class="wc_oe-row">
            <div class="col-100pr">
                <label>
                    <div><?php _e( 'Initial path', 'woocommerce-order-export' ) ?></div>
                    <input type="text" class="width-100" name="settings[destination][ftp_path]"
                           value="<?php echo $tab->get_value( $settings, "[destination][ftp_path]" ); ?>">
                </label>
            </div>
        </div>
        <div class="wc_oe-row">
            <div class="col-50pr">
                <label>
                    <div><?php _e( 'Connection timeout (in seconds)', 'woocommerce-order-export' ) ?></div>
                    <input type="text" name="settings[destination][ftp_conn_timeout]"
                           value="<?php echo $tab->get_value( $settings,
						       "[destination][ftp_conn_timeout]" ); ?>" placeholder="15">
                </label>
            </div>
            <div class="col-50pr">
                <label>
                    <div><?php _e( 'Number of retries', 'woocommerce-order-export' ) ?></div>
                    <input type="text" name="settings[destination][ftp_max_retries]"
                           value="<?php echo $tab->get_value( $settings,
						       "[destination][ftp_max_retries]" ); ?>" placeholder="1">
                </label>
            </div>
        </div>
        <div class="wc_oe-row">
            <div class="col-100pr">
                <label>
                    <div class=""><input name="settings[destination][ftp_passive_mode]"
                                         type="checkbox" <?php echo $tab->get_value( $settings,
							"[destination][ftp_passive_mode]" ) ? 'checked' : ''; ?>><?php _e( 'Passive mode',
							'woocommerce-order-export' ) ?></div>
                </label>
            </div>
            <div class="col-100pr">
                <label>
                    <div class=""><input name="settings[destination][ftp_append_existing]"
                                         type="checkbox" <?php echo $tab->get_value( $settings,
							"[destination][ftp_append_existing]" ) ? 'checked' : ''; ?>><?php _e( 'Append to existing file (need custom code!)',
							'woocommerce-order-export' ) ?></div>
                </label>
            </div>
	        <?php if ( function_exists( 'ftp_ssl_connect' ) ) : ?>
            <div class="col-100pr">
                <label>
                    <div class=""><input name="settings[destination][enable_ssl]"
                                         type="checkbox" <?php echo $tab->get_value( $settings,
					        "[destination][enable_ssl]" ) ? 'checked' : ''; ?>><?php _e( 'TLS/SSL mode',
					        'woocommerce-order-export' ) ?></div>
                </label>
            </div>
	        <?php else: ?>
                <input name="settings[destination][enable_ssl]" type="hidden" value="">
            <?php endif; ?>
        </div>
        <div class="wc_oe-row">
            <div class="col-100pr">
                <label>
                    <div class="wrap"><input name="" class="wc_oe_test my-test-button add-new-h2" data-test="ftp"
                                             type="button" value="<?php _e( 'Test', 'woocommerce-order-export' ) ?>" >
                    </div>
                </label>
				<i>(<?php echo $test_mode_info ?> <b><?php echo $limit_text ?></b>)</i>
            </div>
        </div>
    </div>

    <div class="padding-bottom set-destination my-block" id="sftp" style="display: none;">
        <div class="wc-oe-header"><?php _e( 'SFTP settings', 'woocommerce-order-export' ) ?></div>
        <div class="wc_oe-row">
            <div class="col-50pr">
                <label>
                    <div><?php _e( 'Server name', 'woocommerce-order-export' ) ?></div>
                    <input type="text" name="settings[destination][sftp_server]"
                           value="<?php echo $tab->get_value( $settings,
						       "[destination][sftp_server]" ); ?>">
                </label>
            </div>
            <div class="col-50pr">
                <label>
                    <div><?php _e( 'Port', 'woocommerce-order-export' ) ?></div>
                    <input type="text" name="settings[destination][sftp_port]"
                           value="<?php echo $tab->get_value( $settings, "[destination][sftp_port]" ); ?>"
                           placeholder="22">
                </label>
            </div>
        </div>
        <div class="wc_oe-row">

            <div class="col-50pr">
                <label>
                    <div><?php _e( 'Username', 'woocommerce-order-export' ) ?></div>
                    <input type="text" name="settings[destination][sftp_user]"
                           value="<?php echo $tab->get_value( $settings, "[destination][sftp_user]" ); ?>">
                </label>
            </div>
            <div class="col-50pr">
                <label>
                    <div><?php _e( 'Password', 'woocommerce-order-export' ) ?></div>
                    <input type="text" name="settings[destination][sftp_pass]"
                           value="<?php echo $tab->get_value( $settings, "[destination][sftp_pass]" ); ?>">
                </label>
            </div>
        </div>
        <div class="wc_oe-row">
            <div class="col-100pr">
                <label>
                    <div><?php _e( 'Path to private key', 'woocommerce-order-export' ) ?></div>
                    <input type="text" class="width-100" name="settings[destination][sftp_private_key_path]"
                           value="<?php echo $tab->get_value( $settings, "[destination][sftp_private_key_path]" ); ?>">
                </label>
            </div>
        </div>
        <div class="wc_oe-row">
            <div class="col-100pr">
                <label>
                    <div><?php _e( 'Initial path', 'woocommerce-order-export' ) ?></div>
                    <input type="text" class="width-100" name="settings[destination][sftp_path]"
                           value="<?php echo $tab->get_value( $settings, "[destination][sftp_path]" ); ?>">
                </label>
            </div>
        </div>
        <div class="wc_oe-row">
            <div class="col-50pr">
                <label>
                    <div><?php _e( 'Connection timeout (in seconds)', 'woocommerce-order-export' ) ?></div>
                    <input type="text" name="settings[destination][sftp_conn_timeout]"
                           value="<?php echo $tab->get_value( $settings,
						       "[destination][sftp_conn_timeout]" ); ?>" placeholder="15">
                </label>
            </div>
            <div class="col-50pr">
                <label>
                    <div><?php _e( 'Number of retries', 'woocommerce-order-export' ) ?></div>
                    <input type="text" name="settings[destination][sftp_max_retries]"
                           value="<?php echo $tab->get_value( $settings,
						       "[destination][sftp_max_retries]" ); ?>" placeholder="1">
                </label>
            </div>
        </div>
        <div class="wc_oe-row">
            <div class="col-100pr">
                <label>
                    <div class="wrap"><input name="" class="wc_oe_test my-test-button add-new-h2" data-test="sftp"
                                             type="button" value="<?php _e( 'Test', 'woocommerce-order-export' ) ?>" >
                    </div>
                </label>
				<i>(<?php echo $test_mode_info ?> <b><?php echo $limit_text ?></b>)</i>
            </div>
        </div>
    </div>

    <div class="padding-bottom-10 set-destination my-block" id="http" style="display: none;">
        <div class="wc-oe-header"><?php _e( 'HTTP POST settings', 'woocommerce-order-export' ) ?></div>
        <div class="wc_oe-row">
            <div class="col-100pr">
                <label>
                    <div><?php _e( 'URL', 'woocommerce-order-export' ) ?></div>
                    <input type="text" name="settings[destination][http_post_url]" class="width-100"
                           value="<?php echo $tab->get_value( $settings,
						       "[destination][http_post_url]" ); ?>">
                </label>
            </div>
        </div>
        <div class="wc_oe-row">
            <div class="col-50pr">
                <label>
                    <div><?php _e( 'Connection timeout (in seconds)', 'woocommerce-order-export' ) ?></div>
                    <input type="text" name="settings[destination][http_post_conn_timeout]"
                           value="<?php echo $tab->get_value( $settings,
						       "[destination][http_post_conn_timeout]" ); ?>" placeholder="5">
                </label>
            </div>
            <div class="col-50pr">
                <label>
                    <div><?php _e( 'Number of retries', 'woocommerce-order-export' ) ?></div>
                    <input type="text" name="settings[destination][http_post_max_retries]"
                           value="<?php echo $tab->get_value( $settings,
						       "[destination][http_post_max_retries]" ); ?>" placeholder="1">
                </label>
            </div>
        </div>
        <div class="wc_oe-row">
            <div class="col-100pr">
                <label>
                    <div class="wrap"><input name="" class="wc_oe_test my-test-button add-new-h2" data-test="http"
                                             type="button" value="<?php _e( 'Test', 'woocommerce-order-export' ) ?>" >
                    </div>
                </label>
				<i>(<?php echo $test_mode_info ?> <b><?php echo $limit_text ?></b>)</i>
            </div>
        </div>
    </div>

    <div class="padding-bottom-10 set-destination my-block" id="folder" style="display: none;">
        <div class="wc-oe-header"><?php _e( 'Directory settings', 'woocommerce-order-export' ) ?></div>
        <div class="wc_oe-row">
            <div class="col-100pr">
                <label>
                    <div><?php _e( 'Path', 'woocommerce-order-export' ) ?></div>
                    <input type="text" name="settings[destination][path]" class="width-100"
                           value="<?php echo $tab->get_value( $settings,
						       "[destination][path]" ) ? $tab->get_value( $settings,
						       "[destination][path]" ) : ABSPATH; ?>">
                </label>
            </div>
        </div>
        <div class="wc_oe-row">
            <div class="col-100pr">
                <label>
                    <div class="wrap"><input name="" class="wc_oe_test my-test-button add-new-h2" data-test="folder"
                                             type="button" value="<?php _e( 'Test', 'woocommerce-order-export' ) ?>" >
                    </div>
                </label>
				<i>(<?php echo $test_mode_info ?> <b><?php echo $limit_text ?></b>)</i>
            </div>
        </div>
    </div>

    <div class="padding-bottom set-destination my-block" id="zapier" style="display: none;">
        <div class="wc-oe-header"><?php _e( 'Zapier settings', 'woocommerce-order-export' ) ?></div>
        <div class="wc_oe-row"><label><a
                        href="https://algolplus.freshdesk.com/support/solutions/articles/25000016036-setup-zapier-connection"
                        target=_blank><?php _e( 'Please, read step by step guide', 'woocommerce-order-export' ); ?></a></label>
        </div>

        <div class="wc_oe-row">
            <div class="col-100pr">
                <label><b><?php _e( 'Send order', 'woocommerce-order-export' ); ?></b></label>
                <label>
                    <div class="wrap"><input name="settings[destination][zapier_export_type]"
                                             type="radio" <?php echo ( $tab->get_value( $settings,
								"[destination][zapier_export_type]" ) == 'order' ) ? 'checked' : ''; ?>
                                             value="order"><?php _e( 'As single entry', 'woocommerce-order-export' ) ?>
                        <i><b><?php _e( 'Send notification to chats/sms', 'woocommerce-order-export' ) ?></b></i>
                    </div>
                </label>
                <div class="wrap">
                    <label>
                        <span><?php _e( 'Export', 'woocommerce-order-export' ) ?></span>
                        <input type="text" name="settings[destination][zapier_export_order_product_columns]"
                               class="width-15" value="<?php echo $tab->get_value( $settings,
							"[destination][zapier_export_order_product_columns]" ) ? $tab->get_value( $settings,
							"[destination][zapier_export_order_product_columns]" ) : 10; ?>">
                        <span><?php _e( 'product columns', 'woocommerce-order-export' ) ?></span>
                    </label>
                </div>
                <div class="wrap">
                    <label>
                        <span><?php _e( 'Export', 'woocommerce-order-export' ) ?></span>
                        <input type="text" name="settings[destination][zapier_export_order_coupon_columns]"
                               class="width-15" value="<?php echo $tab->get_value( $settings,
							"[destination][zapier_export_order_coupon_columns]" ) ? $tab->get_value( $settings,
							"[destination][zapier_export_order_coupon_columns]" ) : 10; ?>">
                        <span><?php _e( 'coupon columns', 'woocommerce-order-export' ) ?></span>
                    </label>
                </div>
                <label>
                    <div class="wrap"><input name="settings[destination][zapier_export_type]"
                                             type="radio" <?php echo ( $tab->get_value( $settings,
								"[destination][zapier_export_type]" ) == 'order_items' ) ? 'checked' : ''; ?>
                                             value="order_items"><?php _e( 'As multiple entries (repeated for each item)',
							'woocommerce-order-export' ) ?>
                        <br>
                        <i><b><?php _e( 'Export to Google Sheet', 'woocommerce-order-export' ) ?></b></i>
                    </div>
                </label>
                <label>
                    <div class="wrap"><input name="settings[destination][zapier_export_type]"
                                             type="radio" <?php echo ( $tab->get_value( $settings,
								"[destination][zapier_export_type]" ) == 'file' ) ? 'checked' : ''; ?>
                                             value="file"><?php _e( 'Inside formatted file (many orders)',
							'woocommerce-order-export' ) ?>
                        <br>
                        <i><b><?php _e( 'Upload to Google Drive, Dropbox', 'woocommerce-order-export' ) ?></b></i>
                    </div>
                </label>
            </div>
        </div>

		<?php if ( $url = $tab->get_value( $settings, "[destination][zapier_target_url]" ) ): ?>
            <input type="hidden" name="settings[destination][zapier_target_url]" value="<?php echo $url; ?>">
            <br>
            <div class="wc_oe-row">
                <div class="col-100pr">
                    <label><?php _e( 'Connected to Zapier!', 'woocommerce-order-export' ); ?> </label>
                    <a href="https://zapier.com/app/history" target=_blank><?php _e( 'View Zapier history',
							'woocommerce-order-export' ); ?></a>
                </div>
            </div>
            <div class="wc_oe-row">
                <div class="col-100pr">
                    <label>
                        <div class="wrap"><input name="" class="wc_oe_test my-test-button add-new-h2" data-test="zapier"
                                             type="button" value="<?php _e( 'Test', 'woocommerce-order-export' ) ?>" >
						</div>
                    </label>
					<i>(<?php echo $test_mode_info ?> <b><?php echo $limit_text ?></b>)</i>
                </div>
            </div>
		<?php endif; ?>

    </div>

    <div id='test_reply_div'>
        <b><?php _e( 'Test Results', 'woocommerce-order-export' ) ?></b><br>
        <textarea rows=5 id='test_reply' style="overflow: auto; width:100%" wrap='off'></textarea>
    </div>

    <div class="clear"></div>
    <br/>
    <div id="extend_desstination">
		<?php if ( $settings['mode'] !== WC_Order_Export_Pro_Manage::EXPORT_ORDER_ACTION ): ?>
            <div>
                <label>
                    <input name="settings[destination][separate_files]" type="checkbox"
                           value="1" <?php echo $tab->get_value( $settings,
						"[destination][separate_files]" ) ? 'checked' : ''; ?>><?php _e( 'Make separate file for each order',
						'woocommerce-order-export' ) ?>
                </label>
            </div>
		<?php endif; ?>
    </div>
</div>
<br>
