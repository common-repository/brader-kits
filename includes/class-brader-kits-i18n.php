<?php

class Brader_Kits_i18n {
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'brader-kits',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
