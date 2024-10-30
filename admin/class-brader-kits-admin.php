<?php
require_once __DIR__."/../Brader.php";
class Brader_Kits_Admin extends Brader {
    protected  $dev_server="http://localhost:8081";
	private $plugin_name;
	private $version;
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		if(isset($_GET[$this->brader_woo]) && $_GET[$this->brader_woo]=="logout"){
		    delete_option($this->brader_token);
		    setcookie($this->brader_token,"", time()-1000);
        }

	}

    private function is_dev()
    {
        return false;
    }

	public function enqueue_styles() {
        if ( $this->is_dev() ) {
            wp_enqueue_style( $this->plugin_name . '_dev1', $this->dev_server.'/css/chunk-vendors.css', [], $this->version, 'all' );
            wp_enqueue_style( $this->plugin_name . '_dev2', $this->dev_server.'/css/app.css', [], $this->version, 'all' );
        } else {

            $files=glob(__DIR__.'/../dist/css/*.css');
            $index=1;
            $plugin_dir_url=plugin_dir_url( __DIR__ );
            foreach ($files as $file){
                $temp=explode("/", $file);

                $fileName = $temp[sizeof($temp)-1];
                $src=$plugin_dir_url. 'dist/css/'.$fileName;
                wp_enqueue_style( $this->plugin_name."prod".($index++), $src, [], $this->version, 'all' );
            }
        }

	}

	public function enqueue_scripts() {
        if ( $this->is_dev() ) {
            wp_enqueue_script( $this->plugin_name  . '_dev1', $this->dev_server.'/js/app.js', [], $this->version, false );
            wp_enqueue_script( $this->plugin_name  . '_dev2', $this->dev_server.'/js/chunk-vendors.js', [], $this->version, false );
        } else {
            $files=glob(__DIR__.'/../dist/js/*.js');
            $index=1;
            $plugin_dir_url=plugin_dir_url(__DIR__);
            //$plugin_url=plugin_url();
            foreach ($files as $file){
                $temp=explode("/", $file);
                $fileName = $temp[sizeof($temp)-1];
                wp_enqueue_script( $this->plugin_name."prod".($index++), $plugin_dir_url . 'dist/js/'.$fileName, [], $this->version, false );
            }
        }

        if(isset($_GET["token"])){
            $token=$_GET["token"];
            //$token=esc_html($_GET["token"]);
            delete_option($this->brader_token);
            add_option($this->brader_token, $token,'',true);
            delete_option($this->brader_woo_kits_whatsapp_templates);
            add_option($this->brader_woo_kits_whatsapp_templates, json_encode($this->DefaultTemplates));
            delete_option($this->brader_woo_kits_api_key);
            add_option($this->brader_woo_kits_api_key, $token,"", true);
            
            //Get Phone Number
            $response = wp_remote_get($this->BaseApiURL."/team/member/v1",
                array(
                    'headers' => array(
                        'token' => $token,
                    )
                )
            );
            $members=json_decode($response["body"]);
            if(sizeof($members)>0){
                $member=$members[0];
                delete_option($this->brader_woo_kits_phone_number);
                add_option($this->brader_woo_kits_phone_number, $member->PhoneNumber,"", true);
            }

            $site=get_site_url()."/wp-admin/admin.php?page=brader-kits";
            wp_redirect($site);
        }else{
            $token=get_option($this->brader_token);
        }
        if(isset($token)){
            setcookie($this->brader_token,$token, time()+604800);
        }


	}

}
