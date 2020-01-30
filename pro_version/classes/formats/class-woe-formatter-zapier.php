<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once WOE_PLUGIN_BASEPATH . '/classes/formats/trait-woe-plain-format.php';

class WOE_Formatter_Zapier extends WOE_Formatter {
	use WOE_Order_Export_Plain_Format;
	var $prev_added_zapier = false;
	private $export_type;
	private $products_repeat;
	private $duplicate_settings;
	private $start_tag = '[';
	private $end_tag = ']';

	public function __construct(
		$mode,
		$filename,
		$settings,
		$format,
		$labels,
		$field_formats,
		$date_format,
		$offset
	) {
		parent::__construct( $mode, $filename, $settings, $format, $labels, $field_formats, $date_format, $offset );
		$this->prev_added_zapier = ( $offset > 0 );
	}

	public function start( $data = '' ) {
		parent::start( $data );

		$this->export_type = $this->settings['global_job_settings']['destination']['zapier_export_type'];

		$this->duplicate_settings = array(
			'products' => array(
				'repeat'                 => $this->export_type == 'order' ? 'columns' : 'rows',
				'max_cols'               => (int) $this->settings['global_job_settings']['destination']['zapier_export_order_product_columns'],
				'populate_other_columns' => '1',
				'group_by'               => 'product',
			),
			'coupons'  => array(
				'repeat'                 => $this->export_type == 'order' ? 'columns' : 'rows',
				'max_cols'               => (int) $this->settings['global_job_settings']['destination']['zapier_export_order_coupon_columns'],
				'populate_other_columns' => '1',
				'group_by'               => 'product',
			),
		);

		$this->settings = array();
		$start_text     = $this->convert_literals( $this->start_tag );
		$this->format   = 'zapier';

		fwrite( $this->handle, apply_filters( "woe_zapier_start_text", $start_text ) );
	}

	private function maybe_multiple_fields( $rec ) {
		$products_repeat = $this->duplicate_settings['products']['repeat'];
		$coupons_repeat  = $this->duplicate_settings['coupons']['repeat'];

		$tmp_rec = array();
		foreach ( $this->labels['order']->get_labels() as $label_data ) {
			$original_key = $label_data['key'];
			$key          = $label_data['parent_key'] ? $label_data['parent_key'] : $original_key;

			$tmp_rec[ $key ] = isset( $rec[ $key ] ) ? $rec[ $key ] : "";
		}
		$rec = $tmp_rec;

		$new_rows = array();
		if ( $this->export_type == 'order' ) {
			$repeat_as_cols = array();
			if ( 'columns' == $products_repeat ) {
				$repeat_as_cols[] = 'products';
			}
			if ( 'columns' == $coupons_repeat ) {
				$repeat_as_cols[] = 'coupons';
			}
			if ( $repeat_as_cols ) {
				$rec      = $this->add_nested_rows_as_columns( $rec, $repeat_as_cols );
				$new_rows = array( $rec );
			}
		} elseif ( $this->export_type == 'order_items' ) {
			if ( 'rows' == $products_repeat || 'rows' == $coupons_repeat ) {
				$new_rows = $this->try_multi_rows( $rec );
			}
		} else {
			$new_rows = array( $rec );
		}


		foreach ( $new_rows as $index => &$row ) {
			if ( isset( $row['products'] ) ) {
				unset( $row['products'] );
			}
			if ( isset( $row['coupons'] ) ) {
				unset( $row['coupons'] );
			}
			if ( isset( $row['line_number'] ) && $index > 0 ) {
				$row['line_number'] = $this->counter_value;
				$this->counter_value ++;
			}

//			json for complex structures, don't encode nested products&coupons
			foreach ( $row as $key => &$val ) {
				if ( is_array( $val ) ) {
					$val = json_encode( $val );
				}
			}
		}

		return $new_rows;
	}

	public function output( $rec ) {
		$rec  = parent::output( $rec );
		$rows = $this->maybe_multiple_fields( $rec );

		if ( $this->prev_added_zapier ) {
			fwrite( $this->handle, "," );
			$this->prev_added_zapier = false;
		}

		$pattern = '';
		if ( $this->export_type == 'order' ) {
			$pattern = "/^plain_(products|coupons)_(.+)_(\d+)$/";
		} elseif ( $this->export_type == 'order_items' ) {
			$pattern = "/^plain_(products|coupons)_(.+)$/";
		} else {
			return;// we skip orders in "file" mode
		}
		$rec_out = array();
		foreach ( $rows as $row ) {
			if ( $this->prev_added_zapier ) {
				fwrite( $this->handle, "," );
			}
			foreach ( $row as $field => $value ) {
				if ( preg_match( $pattern, $field, $matches ) ) {
					$type           = $matches[1];
					$type_field_key = $matches[2];
					$col_index      = isset( $matches[3] ) ? (string) $matches[3] : "";

					$label_type = '';
					if ( 'products' == $type ) {
						$label_type = 'Product';
					} elseif ( 'coupons' == $type ) {
						$label_type = 'Coupon';
					}


					$type_field_label_data = $this->labels[ $type ]->$type_field_key;

					$field_label = join(
						' ',
						array_filter( array( $label_type, $col_index, $type_field_label_data['label'] ) )
					);

					$rec_out[ $field_label ] = $value;
				} else {
					$label_data                      = $this->labels['order']->$field;
					$rec_out[ $label_data['label'] ] = $value;
				}
			}
			$json = json_encode( $rec_out );

			if ( $this->has_output_filter ) {
				$json = apply_filters( "woe_zapier_output_filter", $json, $rec );
			}
			fwrite( $this->handle, $json );

			// first record added!
			if ( ! $this->prev_added_zapier ) {
				$this->prev_added_zapier = true;
			}
		}

	}

	public function finish( $data = '' ) {
		$end_text = $this->convert_literals( $this->end_tag );
		fwrite( $this->handle, apply_filters( "woe_zapier_end_text", $end_text ) );
		parent::finish();
	}
}