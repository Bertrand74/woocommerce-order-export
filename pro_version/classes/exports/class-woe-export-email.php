<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WOE_Export_Email extends WOE_Export {

	static $from = '';
	static $from_name = '';

	static $files = array();

	public function run_export( $filename, $filepath, $is_last_order = true ) {
		//must rename tmp file
		$newfilepath = dirname( $filepath ) . "/" . $filename;
		//die($newfilepath);
		if ( ! @copy( $filepath, $newfilepath ) ) {
			return __( "Can't rename temporary file", 'woocommerce-order-export' );
		}

		$attachments_files = array(
		    array(
			'filename' => $filename,
			'filepath' => $newfilepath,
		    )
		);

		if ( !empty( $this->destination['email_send_separate_files_in_one_email'] ) ) {

		    static::$files = array_merge(static::$files, $attachments_files);

		    if ( ! $is_last_order ) {
			$this->finished_successfully = true;
			return;
		    }

		    $attachments_files = static::$files;

		    static::$files = array();
		}

		$to      = apply_filters( "woe_export_email_recipients",
			preg_split( "#,|\r?\n#", $this->destination['email_recipients'], null, PREG_SPLIT_NO_EMPTY ) );
		$subject = apply_filters( "woe_export_email_subject",
			WC_Order_Export_Pro_Engine::make_filename( $this->destination['email_subject'] ) );

		$message = WC_Order_Export_Pro_Engine::make_filename( $this->destination['email_body'] );
		// should use json/xml as body
		if ( ! empty( $this->destination['email_body_append_file_contents'] ) ) {
		    foreach ($attachments_files as $file) {
			$message .= file_get_contents( $file['filepath'] );
		    }
		}
		if ( empty( $message ) ) {
			$message = __( "Please, review the attachment", 'woocommerce-order-export' );
		}
		$message = str_replace( "{orders_exported}", WC_Order_Export_Pro_Engine::$orders_exported, $message );
		$message = apply_filters( "woe_export_email_message", $message );

		$headers = array();
		if ( $message != strip_tags( $message ) ) {
			$headers[] = "Content-Type: text/html";
		} else {
			$headers[] = "Content-Type: text/plain";
		}

		//From config
		self::$from_name = $this->destination['email_from_name'];
		add_action( 'wp_mail_from_name', function ( $original_email_from_name ) {
			return WOE_Export_Email::$from_name;
		} );

		self::$from = $this->destination['email_from'];
		if ( self::$from ) {
			add_action( 'wp_mail_from', function ( $original_email_from ) {
				return WOE_Export_Email::$from;
			} );
		}

		// have to add CC?
		if ( ! empty( $this->destination['email_recipients_cc'] ) ) {
			$cc_emails = preg_split( "#,|\r?\n#", $this->destination['email_recipients_cc'], null,
				PREG_SPLIT_NO_EMPTY );
			foreach ( $cc_emails as $cc_email ) {
				$headers[] = "Cc: " . $cc_email;
			}
		}

		// have to add BCC?
		if ( ! empty( $this->destination['email_recipients_bcc'] ) ) {
			$bcc_emails = preg_split( "#,|\r?\n#", $this->destination['email_recipients_bcc'], null,
				PREG_SPLIT_NO_EMPTY );
			foreach ( $bcc_emails as $bcc_email ) {
				$headers[] = "Bcc: " . $bcc_email;
			}
		}

		$attachments = apply_filters( "woe_export_email_attachments", array_map(function ($v) { return $v['filepath']; }, $attachments_files) );

		try {
			$result = wp_mail( $to, $subject, $message, $headers, $attachments );
		} catch ( Exception $e ) {
			//$e->getMessage();
			$result = false;
		}

		foreach ($attachments as $file) {
		    //delete renamed copy
		    unlink( $file );
		}

		if ( ! $result ) {
			global $ts_mail_errors;
			global $phpmailer;
			if ( ! isset( $ts_mail_errors ) ) {
				$ts_mail_errors = array();
			}
			if ( isset( $phpmailer ) ) {
				$ts_mail_errors[] = $phpmailer->ErrorInfo;
			}
		}
		if ( empty( $ts_mail_errors ) ) {
			$this->finished_successfully = true;
			$return                      = sprintf( __( "File '%s' has sent to '%s'", 'woocommerce-order-export' ),
				 join( ", ", array_map(function ($v) { return $v['filename']; }, $attachments_files) ), join( ",", $to ) );
		} else {
			$return = implode( ';', $ts_mail_errors );
		}

		return $return;
	}
}
