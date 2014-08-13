<?php

class QuantiPress_Commissions extends QuantiPress_Instructor_Commission{

	function organize_data($start_date,$end_date){
		$order_data=$this->grab_order_data($start_date,$end_date);

		
		$product_array=array();
		foreach($order_data as $key=>$prd){ 
			$product_array[$prd->product_id] = $prd->order_item_amount; //Only sales
		}

		return $product_array;
	}

	function course_data($start_date,$end_date){

		$product_data=$this->organize_data($start_date,$end_date);

		$course_data=array();
		foreach($product_data as $product=>$sales){
			$courses = qm_sanitize(get_post_meta($product,'qm_courses',false));
			
			$divide = apply_filters('quantipress_commission_multi_course_divisor',count($courses));

			$value = round($sales/$divide);

			foreach($courses as $course){
				$course_data[$course] = $course_data[$course]+apply_filters('quantipress_commission_course_sale_value',$value,$sales,$courses);
			}
		}

		return $course_data;
	}

	public function instructor_data($start_date,$end_date){
		$courses=$this->course_data($start_date,$end_date);
		$commissions = get_option('instructor_commissions');
		$instructor_data=array();

		foreach($courses as $c=>$sales){

			

			$course=get_post($c);	
			//Multi Author Support Hook.
			$instructor = apply_filters('quantipress_commission_course_instructors',$course->post_author,$course->ID);
			
			
			$val = intval(round(($commissions[$c][$course->post_author]*$sales)/100));

			if(is_array($instructor)){ 
				foreach($instructor as $inst){
					$val=round(($commissions[$c][$inst]*$sales)/100);
					$inst = intval($inst);
					$instructors_data[$inst] = $instructors_data[$inst]+ $val;
				}
			}else{
					if(!isset($instructor_data[$instructor])){
			           $instructor_data[$instructor]=0;
			        }

					$instructor_data[$instructor] += $val;
			}

			//echo $instructor.' Commission % '.$commissions[$c][$instructor].' for Course '. $course->post_title .' = '.$val.'<br />';
		}
		
		return $instructor_data;
	}

}

class QuantiPress_Instructor_Commission{

	function get_order_report_data( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'data'         => array(),
			'where'        => array(),
			'where_meta'   => array(),
 			'query_type'   => 'get_row',
			'group_by'     => '',
			'order_by'     => '',
			'limit'        => '',
			'filter_range' => false,
			'nocache'      => false,
			'debug'        => false
		);

		$args = wp_parse_args( $args, $defaults );

		extract( $args );

		if ( empty( $data ) )
			return false;

		$select = array();

		foreach ( $data as $key => $value ) {
			$distinct = '';

			if ( isset( $value['distinct'] ) )
				$distinct = 'DISTINCT';

			if ( $value['type'] == 'meta' )
				$get_key = "meta_{$key}.meta_value";
			elseif( $value['type'] == 'post_data' )
				$get_key = "posts.{$key}";
			elseif( $value['type'] == 'order_item_meta' )
				$get_key = "order_item_meta_{$key}.meta_value";
			elseif( $value['type'] == 'order_item' )
				$get_key = "order_items.{$key}";

			if ( $value['function'] )
				$get = "{$value['function']}({$distinct} {$get_key})";
			else
				$get = "{$distinct} {$get_key}";

			$select[] = "{$get} as {$value['name']}";
		}

		$query['select'] = "SELECT " . implode( ',', $select );
		$query['from']   = "FROM {$wpdb->posts} AS posts";

		// Joins
		$joins         = array();
		$joins['rel']  = "LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID";
		$joins['tax']  = "LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )";
		$joins['term'] = "LEFT JOIN {$wpdb->terms} AS term USING( term_id )";

		foreach ( $data as $key => $value ) {
			if ( $value['type'] == 'meta' ) {

				$joins["meta_{$key}"] = "LEFT JOIN {$wpdb->postmeta} AS meta_{$key} ON posts.ID = meta_{$key}.post_id";

			} elseif ( $value['type'] == 'order_item_meta' ) {

				$joins["order_items"] = "LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON posts.ID = order_id";
				$joins["order_item_meta_{$key}"] = "LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta_{$key} ON order_items.order_item_id = order_item_meta_{$key}.order_item_id";

			} elseif ( $value['type'] == 'order_item' ) {

				$joins["order_items"] = "LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON posts.ID = order_id";

			}
		}

		if ( ! empty( $where_meta ) ) {
			foreach ( $where_meta as $value ) {
				if ( ! is_array( $value ) )
					continue;

				$key = is_array( $value['meta_key'] ) ? $value['meta_key'][0] : $value['meta_key'];

				if ( isset( $value['type'] ) && $value['type'] == 'order_item_meta' ) {

					$joins["order_items"] = "LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON posts.ID = order_id";
					$joins["order_item_meta_{$key}"] = "LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta_{$key} ON order_items.order_item_id = order_item_meta_{$key}.order_item_id";

				} else {
					// If we have a where clause for meta, join the postmeta table
					$joins["meta_{$key}"] = "LEFT JOIN {$wpdb->postmeta} AS meta_{$key} ON posts.ID = meta_{$key}.post_id";
				}
			}
		}

		$query['join'] = implode( ' ', $joins );

		$query['where']  = "
			WHERE 	posts.post_type 	= 'shop_order'
			AND 	posts.post_status 	= 'publish'
			AND 	tax.taxonomy		= 'shop_order_status'
			AND		term.slug			IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "')
			";

		

		foreach ( $data as $key => $value ) {
			if ( $value['type'] == 'meta' ) {

				$query['where'] .= " AND meta_{$key}.meta_key = '{$key}'";

			} elseif ( $value['type'] == 'order_item_meta' ) {

				$query['where'] .= " AND order_items.order_item_type = '{$value['order_item_type']}'";
				$query['where'] .= " AND order_item_meta_{$key}.meta_key = '{$key}'";

			}
		}

		if ( ! empty( $where_meta ) ) {
			$relation = isset( $where_meta['relation'] ) ? $where_meta['relation'] : 'AND';

			$query['where'] .= " AND (";

			foreach ( $where_meta as $index => $value ) {
				if ( ! is_array( $value ) )
					continue;

				$key = is_array( $value['meta_key'] ) ? $value['meta_key'][0] : $value['meta_key'];

				if ( strtolower( $value['operator'] ) == 'in' ) {
					if ( is_array( $value['meta_value'] ) )
						$value['meta_value'] = implode( "','", $value['meta_value'] );
					if ( ! empty( $value['meta_value'] ) )
						$where_value = "IN ('{$value['meta_value']}')";
				} else {
					$where_value = "{$value['operator']} '{$value['meta_value']}'";
				}

				if ( ! empty( $where_value ) ) {
					if ( $index > 0 )
						$query['where'] .= ' ' . $relation;

					if ( isset( $value['type'] ) && $value['type'] == 'order_item_meta' ) {
						if ( is_array( $value['meta_key'] ) )
							$query['where'] .= " ( order_item_meta_{$key}.meta_key   IN ('" . implode( "','", $value['meta_key'] ) . "')";
						else
							$query['where'] .= " ( order_item_meta_{$key}.meta_key   = '{$value['meta_key']}'";

						$query['where'] .= " AND order_item_meta_{$key}.meta_value {$where_value} )";
					} else {
						if ( is_array( $value['meta_key'] ) )
							$query['where'] .= " ( meta_{$key}.meta_key   IN ('" . implode( "','", $value['meta_key'] ) . "')";
						else
							$query['where'] .= " ( meta_{$key}.meta_key   = '{$value['meta_key']}'";

						$query['where'] .= " AND meta_{$key}.meta_value {$where_value} )";
					}
				}
			}

			$query['where'] .= ")";
		}

		if ( ! empty( $where ) ) {
			foreach ( $where as $value ) {
				if ( strtolower( $value['operator'] ) == 'in' ) {
					if ( is_array( $value['value'] ) )
						$value['value'] = implode( "','", $value['value'] );
					if ( ! empty( $value['value'] ) )
						$where_value = "IN ('{$value['value']}')";
				} else {
					$where_value = "{$value['operator']} '{$value['value']}'";
				}

				if ( ! empty( $where_value ) )
					$query['where'] .= " AND {$value['key']} {$where_value}";
			}
		}

		if ( $group_by ) {
			$query['group_by'] = "GROUP BY {$group_by}";
		}

		if ( $order_by ) {
			$query['order_by'] = "ORDER BY {$order_by}";
		}

		if ( $limit ) {
			$query['limit'] = "LIMIT {$limit}";
		}

		$query      = apply_filters( 'woocommerce_reports_get_order_report_query', $query );
		$query      = implode( ' ', $query );

		
		$result =  $wpdb->get_results( $query );



		return $result;
	}


	function grab_order_data($start_date,$end_date){
		
	$order_data=$this->get_order_report_data(  array(
			'data' => array(
				'_line_total' => array(
					'type'            => 'order_item_meta',
					'order_item_type' => 'line_item',
					'function' => 'SUM',
					'name'     => 'order_item_amount'
				),
				'_product_id' => array(
					'type'            => 'order_item_meta',
					'order_item_type' => 'line_item',
					'function'        => '',
					'name'            => 'product_id'
				)
			),
				'where' => array(
					array(
						'key'      => 'post_date',
						'value'    => $start_date,
						'operator' => '>'
					),
					array(
						'key'      => 'post_date',
						'value'    => $end_date,
						'operator' => '<'
					),
				),
				'group_by' => 'product_id',
				'query_type'   => 'get_var',
				'filter_range' => true
			) );
			
		return $order_data;
	}	


}


?>