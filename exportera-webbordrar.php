<?php
/**
 * Plugin Name: Exportera webbordrar
 * Description: Exportera webbordrar i ett format som kan läsas in i affärssystemet Pyramid.
 * Version: 1.0.0
 * Author: Tommy Johansson
 **/
 
 

function exportera_ordrar ($order_id) {
	if ( ! $order_id )
	return;

	$order = wc_get_order( $order_id );
	$frakt = $order->get_shipping_method() == "Postnord" ? "F" : "H";
	$betalning = $order->get_payment_method() == "swish" ? "SW" : "3";
	$projekttyp = $order->get_payment_method() == "swish" ? "W2" : "W";
	$shipping_zip = $order->get_shipping_postcode(); 
	$shipping_zip_formatted = preg_replace('/\s+/', ' ', substr($shipping_zip, 0, 3) . ' ' . substr($shipping_zip, 3));
	$billing_zip = $order->get_billing_postcode(); 
	$billing_zip_formatted = preg_replace('/\s+/', ' ', substr($billing_zip, 0, 3) . ' ' . substr($billing_zip, 3));
    $orderdata = 
		// 01 = orderhuvud
        "01\n" .
		"#12211;" . $projekttyp . "\n" .
        "#12312;" . date('ymd', strtotime(get_post($order->get_id())->post_date)). "\n" .
        "#12321;" . "\n" .
        "#12373;" . $frakt . "\n" .
		"#12215;" . $order->get_billing_first_name().' '.$order->get_billing_last_name() . "\n" .
		"#12227;" . $order->get_shipping_company(). "\n" .
        "#12230;" . $order->get_shipping_address_1(). "\n" .
        "#12231;" . $order->get_shipping_address_2(). "\n" .
        "#12236;" . $shipping_zip_formatted .' '.$order->get_shipping_city() . "\n" .
        "#12331;" . $order->get_meta('additional_godsmarkning'). "\n" .
		"#12371;" . $betalning . "\n" .
		"¤3084;" . $order->get_id() . "\n" .	
		"¤3086;" . $order->get_meta('additional_e_faktura') . "\n" .
		"#12205;999999\n" .
		// 12 = textrader. Utropstecken först på raden gör att den inte kommer med på blanketterna (fakturor etc).
		"12\n" .
		"#12434;! Webborder: " . $order->get_id() .
		"\n12\n" .
		"#12434;! Fakturaadress:" . 		
		"\n12\n" .
		"#12434;! Företag: " . $order->get_billing_company() .
		"\n12\n" .
		"#12434;! Gatuadress: " . $order->get_billing_address_1() .
		"\n12\n" .
		"#12434;! Utdelningsadress: " . $order->get_billing_address_2() .
		"\n12\n" .
		"#12434;! Postadress: " . $billing_zip_formatted .' '.$order->get_billing_city() .
		"\n12\n" .
		"#12434;! Land: " . $order->get_billing_country() .
		"\n12\n" .
		"#12434;! Organisationsnr: " . $order->get_meta('additional_organisationsnummer') .
		"\n12\n" .
		"#12434;! Kundens referens: " . $order->get_meta('additional_kundreferens') .
		"\n12\n" .
		"#12434;! Kundnummer: " . $order->get_meta('additional_kundnummer') .		
		"\n12\n" .
		"#12434;! E-post: " . $order->get_billing_email() .
		"\n12\n" .
        "#12434;! Telefon: " . $order->get_billing_phone() .
		"\n12\n" .
		"#12434;! Kundens ordernr: " . $order->get_meta('additional_kundens_ordernr') .		
		"\n12\n" .
        "#12434;! E-post för fakturor: " . $order->get_meta('additional_e_post_for_fakturor') . 
		"\n12\n" .
        "#12434;! Betalningsmetod: " . $order->get_payment_method() .
		"\n12\n" .
        "#12434;! Kundens kommentar: " . $order->get_customer_note() . "\n";
		// 11 = artikelrader
		$items = "";
		foreach ( $order->get_items() as $item_id => $item ) {
			$product = $item->get_product();
			$startkostnad = "Startkostnad KALIBRERING";	
			
			$addon = "";

			$addonstring_temp = !empty($item->get_meta( 'Temperaturkalibrering', true)) ? $item->get_meta( 'Temperaturkalibrering', true) : false;

			$addonstring_ugn = !empty($item->get_meta( 'Temperaturkalibrering–Ugn', true)) ? $item->get_meta( 'Temperaturkalibrering–Ugn', true) : false;			

			$addonstring_fukt = !empty($item->get_meta( 'Fuktkalibrering', true)) ? $item->get_meta( 'Fuktkalibrering', true) : false;

			$addonstring_luft = !empty ($item->get_meta( 'Lufthastighetskalibrering', true)) ? $item->get_meta( 'Lufthastighetskalibrering', true) : false;

			$addonstring_luft_tryck = !empty($item->get_meta( 'Lufthastighet-tryckkalibrering', true)) ? $item->get_meta( 'Lufthastighet-tryckkalibrering', true) : false;	

			$addonstring_tryck = !empty($item->get_meta( 'Tryckkalibrering', true)) ? $item->get_meta( 'Tryckkalibrering', true) : false;
			
			$addonstring_co2_fukt_temp = !empty($item->get_meta( 'CO2-fukt-temperaturkalibrering')) ? $item->get_meta( 'CO2-fukt-temperaturkalibrering') : false;

			$addonstring_co2 = !empty($item->get_meta( 'CO2-kalibrering')) ? $item->get_meta( 'CO2-kalibrering') : false;

			$addonstring_tryck_temp = !empty($item->get_meta( 'Tryck-temperaturkalibrering')) ? $item->get_meta( 'Tryck-temperaturkalibrering') : false;	

			$addonstring_byggfukt_celsicom = !empty($item->get_meta( 'Byggfuktsjustering-Celsicom')) ? $item->get_meta( 'Byggfuktsjustering-Celsicom') : false;

			$addonstring_rbk = !empty($item->get_meta( 'RBK-fuktkalibrering')) ? $item->get_meta( 'RBK-fuktkalibrering') : false;

			$addonstring_rbk_matset = !empty($item->get_meta( 'RBK-fuktkalibrering-matset')) ? $item->get_meta( 'RBK-fuktkalibrering-matset') : false;


				if (strpos($addonstring_temp, "0520 9001") !== false){
					$addon = "11\n#12401;0521 9101\n#12441;". $item->get_quantity()./*"\n#12421;". $startkostnad .*/"\n11\n#12401;0520 9001\n#12441;". $item->get_quantity()./*"\n#12421;". $addonstring_temp .*/"\n";
				}				
				
				elseif (strpos($addonstring_temp, "0520 9011") !== false){
					$addon = "11\n#12401;0521 9101\n#12441;". $item->get_quantity()./*"\n#12421;". $startkostnad .*/"\n11\n#12401;0520 9011\n#12441;". $item->get_quantity()./*"\n#12421;". $addonstring_temp .*/"\n";
				}	
				
				elseif (strpos($addonstring_ugn, "0520 9151") !== false){
					$addon = "11\n#12401;0521 9101\n#12441;". $item->get_quantity()./*"\n#12421;". $startkostnad .*/"\n11\n#12401;0520 9151\n#12441;". $item->get_quantity()./*"\n#12421;". $addonstring_ugn .*/"\n";
				}				
		
				elseif (strpos($addonstring_fukt, "0520 9006") !== false){
					$addon = "11\n#12401;0521 9101\n#12441;". $item->get_quantity()./*"\n#12421;". $startkostnad .*/"\n11\n#12401;0520 9006\n#12441;". $item->get_quantity()./*"\n#12421;". $addonstring_fukt .*/"\n";
				}
				
				elseif (strpos($addonstring_fukt, "0520 9086") !== false){
					$addon = "11\n#12401;0521 9101\n#12441;". $item->get_quantity()./*"\n#12421;". $startkostnad .*/"\n11\n#12401;0520 9086\n#12441;". $item->get_quantity()./*"\n#12421;". $addonstring_fukt .*/"\n";
				}				
				
				elseif (strpos($addonstring_luft, "0520 9017") !== false){
					$addon = "11\n#12401;0521 9101\n#12441;". $item->get_quantity()./*"\n#12421;". $startkostnad .*/"\n11\n#12401;0520 9017\n#12441;". $item->get_quantity()./*"\n#12421;". $addonstring_luft .*/"\n";
				}
			
				elseif (strpos($addonstring_luft_tryck, "0520 9017 / 0520 9005") !== false){
					$addon = "11\n#12401;0521 9101\n#12441;". $item->get_quantity()./*"\n#12421;". $startkostnad .*/"\n11\n#12401;0520 9017\n#12441;". $item->get_quantity()./*"\n#12421;". $addonstring_luft_tryck .*/"\n11\n#12401;0520 9005\n#12441;". $item->get_quantity()./*"\n#12421;". $addonstring_luft_tryck .*/"\n";
				}

				elseif (strpos($addonstring_tryck, "0520 9005") !== false){
					$addon = "11\n#12401;0521 9101\n#12441;". $item->get_quantity()./*"\n#12421;". $startkostnad .*/"\n11\n#12401;0520 9005\n#12441;". $item->get_quantity()./*"\n#12421;". $addonstring_tryck . */"\n";
				}	
				
				elseif (strpos($addonstring_tryck, "0520 9015") !== false){
					$addon = "11\n#12401;0521 9101\n#12441;". $item->get_quantity()./*"\n#12421;". $startkostnad .*/"\n11\n#12401;0520 9015\n#12441;". $item->get_quantity()./*"\n#12421;". $addonstring_tryck . */"\n";
				}					
			
				elseif (strpos($addonstring_co2_fukt_temp, "0520 9032 / 0520 9006") !== false){
					$addon = "11\n#12401;0521 9101\n#12441;". $item->get_quantity()./*"\n#12421;". $startkostnad .*/"\n11\n#12401;0520 9032\n#12441;". $item->get_quantity()./*"\n#12421;". $addonstring_co_fukt_temp .
					*/"\n11\n#12401;0520 9006\n#12441;". $item->get_quantity()./*"\n#12421;". $addonstring_co_fukt_temp .*/"\n";
				}	
				
				elseif (strpos($addonstring_co2, "0520 9033") !== false){
					$addon = "11\n#12401;0521 9101\n#12441;". $item->get_quantity()./*"\n#12421;". $startkostnad .*/"\n11\n#12401;0520 9033\n#12441;". $item->get_quantity()./*"\n#12421;". $addonstring_co2 .*/"\n";
				}				
			
				elseif (strpos($addonstring_rbk, "0520 9085") !== false){
					$addon = "11\n#12401;0521 9101\n#12441;". $item->get_quantity()./*"\n#12421;". $startkostnad .*/"\n11\n#12401;0520 9085\n#12441;". $item->get_quantity()./*"\n#12421;". $addonstring_rbk .*/"\n";
				}

				elseif (strpos($addonstring_rbk_matset, "0520 9085") !== false){
					$addon = "11\n#12401;0521 9101\n#12441;". ($item->get_quantity() * 3) ./*"\n#12421;". $startkostnad .*/"\n11\n#12401;0520 9085\n#12441;". ($item->get_quantity() * 3) ./*"\n#12421;". $addonstring_rbk_matset .*/"\n";
				}

				elseif (strpos($addonstring_byggfukt_celsicom, "7080 7595") !== false){
					$addon = "11\n#12401;7080 7595\n#12441;". $item->get_quantity()./*"\n#12421;". $addonstring_byggfukt_celsicom .*/"\n";
				}													
                
				$items .=  
				"11\n" .
				"#12441;" . $item->get_quantity(). "\n" .
			/*	"#12421;" . $item->get_name(). "\n" . */
				"#12401;" . $product->get_sku(). "\n" .
				$addon;



 		}	
        
		$orderdata .= $items;
		$orderdata = iconv(mb_detect_encoding($orderdata), 'Windows-1252//TRANSLIT', $orderdata);
		$orderno = $order->get_order_number();
		$file = '/home/nordtecd/nordtec.dev/pyramid/out/order-'.$orderno.'.txt';
        $open = fopen( $file, "w" );
        $write = fputs( $open, $orderdata ); 
        fclose( $open );        
    
}
add_action('woocommerce_checkout_order_processed', 'exportera_ordrar', 10, 1);