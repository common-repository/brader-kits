<?php
require_once plugin_dir_path( dirname( __FILE__ ) )."Brader.php";
class Brader_Kits extends Brader{
	protected $loader;
	protected $plugin_name;
	protected $version;
	public function __construct() {
		if ( defined( 'BRADER_KITS_VERSION' ) ) {
			$this->version = BRADER_KITS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'brader-kits';
		$this->load_dependencies();
		$this->set_locale();
		if($this->IsWooKitsAdminPage()){
            $this->define_admin_hooks();
        }

        $this->define_public_hooks();
        add_action( 'admin_menu', array( $this, 'create_settings' ), 101 );
	}


    public function create_settings() {
       $capability = 'manage_options';
       $slug = 'brader-kits';
       $callback = array( $this, 'settings_content' );
       add_submenu_page( 'woocommerce', $this->app_name, $this->app_name, $capability, $slug, $callback );
    }

    public function settings_content() {
        echo '<div id="'.$this->brader_woo.'-app-container" style="margin-top: 15px;"><div id="'.$this->brader_woo.'-app">Loading...</div></div>';
    }

	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-brader-kits-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-brader-kits-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-brader-kits-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-brader-kits-public.php';
        if(!function_exists('wp_get_current_user')) {
            include(ABSPATH . "wp-includes/pluggable.php");
        }
		$this->loader = new Brader_Kits_Loader();

	}

	private function set_locale() {
		$plugin_i18n = new Brader_Kits_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

    function GetTemplates(){
        $templates=get_option($this->brader_woo_kits_whatsapp_templates);
        //echo $templates;
        $temp=json_decode($templates);
        if(!isset($temp))
            $temp=new stdClass();
        wp_send_json($temp);
        wp_die();
    }

    function SaveTemplates(){
	    /*
	     Dear WordPress Team,
	     I don't think sanitize the following user input is required.
	     I'm expecting user to be able to input any kind of string including Unicode character.
	     I believe add_option is already has dirty/harmful input checking
	    */
        $templates=$_POST["templates"];
        $templates=stripslashes($templates);
        delete_option($this->brader_woo_kits_whatsapp_templates);
        $success=add_option($this->brader_woo_kits_whatsapp_templates,$templates,"", true );
        echo $success;
        wp_die();
    }
    function SavePhoneNumber(){
        $phoneNumber=esc_html($_POST["PhoneNumber"]);
        delete_option($this->brader_woo_kits_phone_number);
        $success=add_option($this->brader_woo_kits_phone_number,$phoneNumber,"", true);
        echo $success;
        wp_die();
    }

	private function define_admin_hooks() {
        add_action("wp_ajax_SavePhoneNumber", [$this, "SavePhoneNumber"]);
        add_action("wp_ajax_SaveTemplates", [$this, "SaveTemplates"]);
        add_action("wp_ajax_GetTemplates", [$this, "GetTemplates"]);
		$plugin_admin = new Brader_Kits_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	private function define_public_hooks() {
		$plugin_public = new Brader_Kits_Public( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        add_action( 'woocommerce_order_status_on-hold', array( $this, 'SendNofifOnHold' ) );
        add_action( 'woocommerce_order_status_processing', array( $this, 'SendNofifProcessing' ) );
        add_action( 'woocommerce_order_status_completed', array( $this, 'SendNofifCompleted' ) );
        add_action( 'woocommerce_order_status_cancelled', array( $this, 'SendNofifCancelled' ) );
        add_action( 'woocommerce_order_status_refunded', array( $this, 'SendNofifRefunded' ) );
        add_action( 'woocommerce_order_status_pending', array( $this, 'SendNofifPending' ) );

	}

	public function SendNofifProcessing($order_id){
	    $this->SendOrderNotif($order_id, "order_processing");
    }
    public function SendNofifOnHold($order_id){
	    $this->SendOrderNotif($order_id, "order_on_hold");
    }
    public function SendNofifCompleted($order_id){
	    $this->SendOrderNotif($order_id, "order_complete");
    }
    public function SendNofifCancelled($order_id){
	    $this->SendOrderNotif($order_id, "order_cancelled");
    }
    public function SendNofifRefunded($order_id){
	    $this->SendOrderNotif($order_id, "order_refunded");
    }
    public function SendNofifPending($order_id){
	    $this->SendOrderNotif($order_id, "order_pending");
    }

    function format_phone($number){
	    $number=str_replace(".","",$number);
	    $number=str_replace(",","",$number);
	    $number=str_replace("-","",$number);
	    $number=str_replace(" ","",$number);
        return $number;
    }

    function SendOrderNotif($order_id, $status){
        if ( empty( $status ) ) {
            return;
        }

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }
        $billing_phone = $order->get_billing_phone();
        if ( empty( $billing_phone ) ) {
            return;
        }
        $billing_phone = $this->format_phone( $billing_phone );
        $templates=(array)json_decode(get_option($this->brader_woo_kits_whatsapp_templates));
        $whatsapp_message = $templates[$status];
        $whatsapp_message = $this->format_message( $order_id, $whatsapp_message );
        $note = $this->send_message( $billing_phone, $whatsapp_message, $prefix );
        if ( $note ) {
            $order->add_order_note( $note );
        }
    }

    private function format_message( $order_id, $whatsapp_message = '' ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return false;
        }
        if ( ! $whatsapp_message ) {
            return false;
        }
        if ( strpos( $whatsapp_message, '%site_name%') !== false ) {
            if ( ! $site_name ) {
                $site_name = get_bloginfo('name');
            }
            $whatsapp_message = str_replace( '%site_name%', $site_name, $whatsapp_message );
        }
        if ( strpos( $whatsapp_message, '%order_id%') !== false ) {
            if ( ! $order_id ) {
                $order_id = $order->get_order_number();
            }
            $whatsapp_message = str_replace( '%order_id%', $order_id, $whatsapp_message );
        }
        if ( strpos( $whatsapp_message, '%order_date%') !== false ) {
            if ( ! $order_date ) {
                $order_date = wc_format_datetime( $order->get_date_created() );
            }
            $whatsapp_message = str_replace( '%order_date%', $order_date, $whatsapp_message );
        }
        if ( strpos( $whatsapp_message, '%order_status%') !== false ) {
            if ( ! $order_status_label ) {
                $order_status_label = wc_get_order_status_name( $order->get_status() );
            }
            $whatsapp_message = str_replace( '%order_status%', $order_status_label, $whatsapp_message );
        }
        if ( strpos( $whatsapp_message, '%order_items%') !== false ) {
            if ( ! $order_items ) {
                $items = $order->get_items();
                $item_names = array();
                foreach ( $items as $item ) {
                    if ( count( $items ) == 1 && $item->get_quantity() == 1 ) {
                        $item_names[] = $item->get_name();
                    }
                    else {
                        $item_names[] = $item->get_name() . ' x ' . $item->get_quantity();
                    }
                }
                $order_items = implode( ', ', $item_names );
            }
            $whatsapp_message = str_replace( '%order_items%', $order_items, $whatsapp_message );
        }
        if ( strpos( $whatsapp_message, '%order_total%') !== false ) {
            if ( ! $order_total ) {
                $order_total = $order->get_formatted_order_total();
                $order_total = strip_tags( $order_total );
            }
            $whatsapp_message = str_replace( '%order_total%', $order_total, $whatsapp_message );
        }
        if ( strpos( $whatsapp_message, '%billing_name%') !== false ) {
            if ( ! $billing_name ) {
                $billing_name = $order->get_billing_first_name();
            }
            $whatsapp_message = str_replace( '%billing_name%', $billing_name, $whatsapp_message );
        }
        if ( strpos( $whatsapp_message, '%billing_email%') !== false ) {
            if ( ! $billing_email ) {
                $billing_email = $order->get_billing_email();
            }
            $whatsapp_message = str_replace( '%billing_email%', $billing_email, $whatsapp_message );
        }
        if ( strpos( $whatsapp_message, '%billing_phone%') !== false ) {
            if ( ! $billing_phone ) {
                $billing_phone = $order->get_billing_phone();
            }
            $whatsapp_message = str_replace( '%billing_phone%', $billing_phone, $whatsapp_message );
        }
        if ( strpos( $whatsapp_message, '%billing_address%') !== false ) {
            if ( ! $billing_address ) {
                $billing_address = $order->get_formatted_billing_address('');
                $billing_address = str_replace( array( '<br>', '<br/>' ), " \n", $billing_address );
            }
            $whatsapp_message = str_replace( '%billing_address%', $billing_address, $whatsapp_message );
        }
        if ( strpos( $whatsapp_message, '%shipping_name%') !== false ) {
            if ( ! $shipping_name ) {
                $shipping_name = $order->get_shipping_first_name();
            }
            $whatsapp_message = str_replace( '%shipping_name%', $shipping_name, $whatsapp_message );
        }
        if ( strpos( $whatsapp_message, '%shipping_address%') !== false ) {
            if ( ! $shipping_address ) {
                $shipping_address = $order->get_formatted_shipping_address('');
                $shipping_address = str_replace( array( '<br>', '<br/>' ), " \n", $shipping_address );
            }
            $whatsapp_message = str_replace( '%shipping_address%', $shipping_address, $whatsapp_message );
        }
        if ( strpos( $whatsapp_message, '%indo_ongkir_resi%') !== false ) {
            if ( ! $indo_ongkir_resi ) {
                $shipping = $order->get_items( 'shipping' );
                $resi_items = array();
                if ( !empty( $shipping ) ) {
                    $resi = get_post_meta( $order->get_id(), '_indo_ongkir_resi', true );
                    $date = get_post_meta( $order->get_id(), '_indo_ongkir_date', true );
                    foreach ( $shipping as $item_id => $item ) {
                        if ( isset($resi[$item_id]) && $resi[$item_id] ) {
                            $resi_items[$item_id]['name'] = $item->get_name();
                            $resi_items[$item_id]['items'] = $item->get_meta( 'Items' );
                            $resi_items[$item_id]['resi'] = $resi[$item_id];
                            $resi_items[$item_id]['date'] = $date[$item_id];
                        }
                    }
                }
                if ( ! empty( $resi_items ) ) {
                    $resi_count = count( $resi_items );
                    $indo_ongkir_resi .= 'Resi Pengiriman:'."\n\n";
                    foreach ( $resi_items as $resi_item ) {
                        $indo_ongkir_resi .= ''.$resi_item['name'].' ('.$resi_item['date'].')'."\n".'*'.$resi_item['resi'].'*'."\n\n";
                    }
                }
            }
            $whatsapp_message = str_replace( '%indo_ongkir_resi%', $indo_ongkir_resi, $whatsapp_message );
        }
        if ( strpos( $whatsapp_message, '%order_bank_details%') !== false ) {
            if ( ! $order_bank_details ) {
                if ( $order->get_status() == 'on-hold' && $order->get_payment_method() == 'bacs' ) {
                    $gateways = WC()->payment_gateways->get_available_payment_gateways();
                    if ( isset( $gateways['bacs']->account_details ) ) {
                        foreach ( $gateways['bacs']->account_details as $bank ) {
                            if ( $bank['bank_name'] ) {
                                $order_bank_details .= $bank['bank_name']."\n";
                            }
                            if ( $bank['account_number'] ) {
                                $order_bank_details .= '*'.$bank['account_number'].'*'."\n";
                            }
                            if ( $bank['account_name'] ) {
                                $order_bank_details .= $bank['account_name']."\n";
                            }
                            $order_bank_details .= "\n";
                        }
                    }
                }
            }
            $whatsapp_message = str_replace( '%order_bank_details%', $order_bank_details, $whatsapp_message );
        }

        $whatsapp_message = str_replace( '&nbsp;', ' ', $whatsapp_message );
        $whatsapp_message = str_replace( array( "\n\n\n", "\r\n\r\n\r\n", "\n\r\n\r\n" ), "\n", $whatsapp_message );

        return $whatsapp_message;
    }

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_loader() {
		return $this->loader;
	}


	public function get_version() {
		return $this->version;
	}
    private function send_message( $phone = '', $message = '')
    {
        $param=array(
            'headers' => array(
                'ApiKey' => get_option($this->brader_woo_kits_api_key),
            ),
            'body' => array(
                'From' => get_option($this->brader_woo_kits_phone_number),
                'To' => $phone,
                'Message' => $message,
            ),
        );
        $response = wp_remote_post(
            $this->BaseApiURL."/wa-gw/send/v3",
            $param
        );
    }
}
