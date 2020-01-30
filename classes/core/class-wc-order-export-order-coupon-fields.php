<?php
class WC_Order_Export_Order_Coupon_Fields {
	var $item;
	var $coupon_meta;
	var $static_vals;

	public function __construct($item, $labels, $static_vals) {
		global $wpdb;

		$this->coupon_meta     = array();
		$get_coupon_meta = ( array_diff( $labels->get_keys(), array( 'code', 'discount_amount', 'discount_amount_tax', 'excerpt' ) ) );
		$coupon_object = new WC_Coupon( $item['name'] );

		if ( $get_coupon_meta ) {
			$recs = $wpdb->get_results( $wpdb->prepare( "SELECT meta_value,meta_key FROM {$wpdb->postmeta} AS meta
				JOIN {$wpdb->posts} AS posts ON posts.ID = meta.post_id
				WHERE posts.post_title=%s", $item['name'] ) );
			foreach ( $recs as $rec ) {
				$this->coupon_meta[ $rec->meta_key ] = $rec->meta_value;
			}

			foreach ( $coupon_object->get_meta_data() as $meta) {
				$this->coupon_meta[ $meta->key ] = $meta->value;
			};
		}
		$this->item = $item;
		$this->static_vals = $static_vals;
	}

	public function get($field) {
		if ( isset( $this->item[ $field ] ) ) {
			return $this->item[ $field ];
		} elseif ( $field == 'code' ) {
			return $this->item["name"];
		} elseif ( $field == 'discount_amount_plus_tax' ) {
			return $this->item["discount_amount"] + $this->item["discount_amount_tax"];
		} elseif ( $field == 'excerpt' ) {
			$post          = get_page_by_title( $this->item['name'], OBJECT, 'shop_' . $this->item['type'] );
			return $post ? $post->post_excerpt : '';
		} elseif ( isset( $this->coupon_meta[ $field ] ) ) {
			return $this->coupon_meta[ $field ];
		} elseif ( isset( $this->static_vals[ $field ] ) ) {
			return $this->static_vals[ $field ];
		} else {
			return '';
		}
	}

	public function get_coupon_meta() {
		return $this->coupon_meta;
	}
}