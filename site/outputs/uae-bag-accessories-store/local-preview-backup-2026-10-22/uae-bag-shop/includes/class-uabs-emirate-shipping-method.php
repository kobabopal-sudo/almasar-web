<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UABS_Emirate_Shipping_Method extends WC_Shipping_Method {
	public function __construct( $instance_id = 0 ) {
		$this->id = 'uabs_emirate_shipping';
		$this->instance_id = absint( $instance_id );
		$this->method_title = 'Delivery by Emirate';
		$this->method_description = 'Uses the delivery charge saved in UAE Bag Shop settings for the destination emirate.';
		$this->supports = array( 'shipping-zones' );
		$this->title = 'Delivery by Emirate';
		$this->enabled = 'yes';
	}

	public function calculate_shipping( $package = array() ) {
		if ( 'yes' !== $this->enabled ) {
			return;
		}
		$state = isset( $package['destination']['state'] ) ? strtoupper( sanitize_key( $package['destination']['state'] ) ) : '';
		$settings = get_option( UABS_Plugin::OPTION, array() );
		$key = 'rate_' . strtolower( $state );
		if ( ! isset( $settings[ $key ] ) || '' === $settings[ $key ] ) {
			return;
		}
		$label = UABS_Plugin::instance()->is_arabic() ? 'التوصيل إلى الإمارة' : $this->title;
		$this->add_rate( array( 'id' => $this->get_rate_id(), 'label' => $label, 'cost' => (float) $settings[ $key ], 'package' => $package ) );
	}
}
