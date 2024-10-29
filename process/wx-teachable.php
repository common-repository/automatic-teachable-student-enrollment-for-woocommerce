<?php
/**
 * Version: 1.0.0
 */
if (ATSEW_TEACHABLEAPIKEY !== '') {
	# code...

	
	// un enrollment from teachable if order cancelled or refunded
	add_action( 'woocommerce_order_status_refunded', 'atsew_wpteachable_unenroll_user_to_teachable' );
	add_action( 'woocommerce_order_status_cancelled', 'atsew_wpteachable_unenroll_user_to_teachable' );

	if( !function_exists('atsew_wpteachable_unenroll_user_to_teachable')){
		function atsew_wpteachable_unenroll_user_to_teachable( $order_id ) {

			$order = wc_get_order( $order_id );
	
			foreach ( $order->get_items() as $item_id => $item ) {
				$meta_course_id = get_post_meta( $item->get_product_id(), 'teachable_course_id_'.$item->get_product_id(), true );
	
				if($meta_course_id!==null){
					if($meta_course_id>0){
						// opportunity to change enrollment student's email & name using below filters
						$billing_email = apply_filters('atsew_teachable_student_email',$order->get_billing_email(),$order_id,$item_id);
						$billing_name = apply_filters('atsew_teachable_student_name',$order->get_billing_first_name(),$order_id,$item_id);
		
						$teachable_user_id=atsew_push_to_teachable($billing_name,$billing_email);
						if((int)$teachable_user_id>0){
							$args = array(
								'headers' => array(
									'Content-Type'=>'application/json',
									'accept'=>'application/json',
									'apiKey'=>ATSEW_TEACHABLEAPIKEY
								),	
								'body'=>json_encode(['user_id'=>$teachable_user_id,'course_id'=>$meta_course_id]),
								'method' => 'POST'
							);
							wp_remote_post( 'https://developers.teachable.com/v1/unenroll', $args );
						}
					}
				}
			}
	
		}
	}

	// enrole student data to teachable
	$atsew_orderStatusShow = get_option('teachable_fild_order_status');

	if( !function_exists('atsew_wpteachable_enroll_user_to_teachable')){
		add_action( 'woocommerce_order_status_'.$atsew_orderStatusShow, 'atsew_wpteachable_enroll_user_to_teachable' );

		function atsew_wpteachable_enroll_user_to_teachable( $order_id ) {
	
			$order = wc_get_order( $order_id );
			foreach ( $order->get_items() as $item_id => $item ) {
				$meta_course_id = get_post_meta( $item->get_product_id(), 'teachable_course_id_'.$item->get_product_id(), true );
	
				if($meta_course_id!==null){
					if((int)$meta_course_id>0){
						// opportunity to change enrollment student's email & name using below filters
						$billing_email = apply_filters('atsew_teachable_student_email',$order->get_billing_email(),$order_id,$item_id);
						$billing_name = apply_filters('atsew_teachable_student_name',$order->get_billing_first_name(),$order_id,$item_id);
		
						$teachable_user_id=atsew_push_to_teachable($billing_name,$billing_email);
						if($teachable_user_id>0){

							$args = array(
								'headers' => array(
									'Content-Type'=>'application/json',
									'accept'=>'application/json',
									'apiKey'=>ATSEW_TEACHABLEAPIKEY
								),	
								'body'=>json_encode(['user_id'=>$teachable_user_id,'course_id'=>$meta_course_id]),
								'method' => 'POST'
							);
							wp_remote_post( 'https://developers.teachable.com/v1/enroll', $args );
							
						}
					}
				}
			}
		}
	}


 if( !function_exists('atsew_push_to_teachable')){
	
	function atsew_push_to_teachable($name,$email){

		$args = array(
			'headers' => array(
				'Content-Type'=>'application/json',
				'accept'=>'application/json',
				'apiKey'=>ATSEW_TEACHABLEAPIKEY
			),	
			'body'=>json_encode(['email'=>$email,'name'=>$name]),
			'method' => 'POST'
		);
		$response=wp_remote_post( 'https://developers.teachable.com/v1/users', $args );
		
		$response=wp_remote_retrieve_body($response);
		$response = json_decode($response, true);
		return $teachable_user_id = $response['id'];
	}
 }


}
