<?php
class Brader
{
    public $BaseApiURL="https://api.brader.id";
    //public $BaseApiURL="http://192.168.64.1:9091";
    public $brader_woo_kits_phone_number="brader_woo_kits_phone_number";
    public $brader_token="brader_token";
    public $brader_woo="brader-kits";
    public $app_name="Brader Kits";
    public $brader_woo_kits_whatsapp_templates="brader_woo_kits_whatsapp_templates";
    public $brader_woo_kits_api_key="brader_woo_kits_api_key";
    public $DefaultTemplates=array(
      "order_on_hold"=>'Halo %billing_name%,

_(Mohon abaikan pesan ini jika bukan Anda yang melakukan pemesanan di bawah ini)_

Terimakasih untuk pemesanan Anda sebagai berikut: 

Order ID: *%order_id%*
Tanggal: *%order_date%*
Status: *%order_status%*
Produk: *%order_items%*
Total: *%order_total%*

Harap segera lakukan pembayaran supaya pesanan Anda bisa segera kami proses ya.

%order_bank_details%

Terimakasih banyak sudah berbelanja di website kami.

Salam,
%site_name%',
        "order_complete"=>'Halo %billing_name%,

_(Mohon abaikan pesan ini jika bukan Anda yang melakukan pemesanan di bawah ini)_

*Pesanan Anda saat ini telah selesai kami kirimkan.*

%indo_ongkir_resi%

Sebagai pengingat, detail pemesanan Anda adalah sebagai berikut: 

Order ID: *%order_id%*
Tanggal: *%order_date%*
Status: *%order_status%*
Produk: *%order_items%*
Total: *%order_total%*

Terimakasih banyak sudah berbelanja di website kami.

Salam,
%site_name%',
        "order_cancelled"=>'Halo %billing_name%,

_(Mohon abaikan pesan ini jika bukan Anda yang melakukan pemesanan di bawah ini)_

*Pesanan Anda berikut ini telah dibatalkan:*

Order ID: *%order_id%*
Tanggal: *%order_date%*
Status: *%order_status%*
Produk: *%order_items%*
Total: *%order_total%*

Jika Anda masih tertarik, silahkan melakukan pemesanan ulang di website kami. 

Salam,
%site_name%',
        "order_processing"=> 'Halo %billing_name%,

_(Mohon abaikan pesan ini jika bukan Anda yang melakukan pemesanan di bawah ini)_

*Terimakasih untuk pembayaran Anda. Pesanan Anda saat ini sedang kami proses.*

Sebagai pengingat, detail pemesanan Anda adalah sebagai berikut: 

Order ID: *%order_id%*
Tanggal: *%order_date%*
Status: *%order_status%*
Produk: *%order_items%*
Total: *%order_total%*

Terimakasih banyak sudah berbelanja di website kami.

Salam,
%site_name%'
    );

    public function IsWooKitsAdminPage(){
        $requestUri=$_SERVER["REQUEST_URI"];
        $test=strrpos($requestUri,"brader-kits")!=false;
        return $test;
    }

}