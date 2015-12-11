<?php
/*
Plugin Name: cf-easy-pop
Version: 0.1.0
 */

add_action( 'caldera_forms_autopopulate_types', function(){
	if( defined( 'CEP_VER' ) ) {
		echo "<option value=\"easy_pod\"{{#is auto_type value=\"easy_pod\"}} selected=\"selected\"{{/is}}>" . esc_html__('Easy Pod', 'caldera-forms') . "</option>";
	}

	if( defined( 'CAEQ_VER' ) ) {
		echo "<option value=\"easy_query\"{{#is auto_type value=\"easy_query\"}} selected=\"selected\"{{/is}}>" . esc_html__('Easy Query', 'caldera-forms') . "</option>";
	}
});

add_action ( 'caldera_forms_autopopulate_type_config', function(){
	if( defined( 'CEP_VER' ) ) {

	}

	if( defined( 'CAEQ_VER' ) ) {
		$easy_queries = calderawp\caeq\options::get_registry();
		if( ! empty( $easy_queries ) ) {
		?>
			<div class="caldera-config-group caldera-config-group-auto-easy_query auto-populate-type-panel" style="display:none;">
				<label> <?php echo esc_html__( 'Choose An Easy Query', 'caldera-forms' ); ?> </label>
				<div class="caldera-config-field">
					<select class="block-input field-config choose-easy-query" name="{{_name}}[easy_query]">
						<?php foreach ( $easy_queries as $easy_query ) {
							printf( "<option value=\"%s\" {{#is value_field value=\"%s\"}}selected=\"selected\"{{/is}}>
		%s
	</option>\r\n", $easy_query[ 'id' ], $easy_query[ 'id' ], $easy_query[ 'name' ] );
						} ?>

					</select>
				</div>
			</div>
<?php
		}//ifempty

	}//CAEQ
});


/**
 * Verify and format select options
 *
 * @since 1.3.2
 *
 * @param array $field Field config
 *
 * @return array
 */
function cep_format_select_options( $field ) {
	if ( ( empty( $field['config']['value_field']) || $field[ 'config' ][ 'value_field' ] == 'name' ) && isset( $field[ 'config' ] ) && isset( $field[ 'config' ][ 'option' ] ) && is_array( $field[ 'config' ][ 'option' ] ) ){

		foreach( $field[ 'config' ][ 'option' ] as &$option){
			$option[ 'value' ] = $option[ 'label' ];
		}

	}else{
		if ( empty( $field[ 'config' ]['show_values'] ) ){
			if( !empty( $field[ 'config' ][ 'option' ] ) ){
				foreach( $field[ 'config' ][ 'option' ] as &$option){
					$option[ 'value' ] = $option[ 'label' ];
				}
			}
		}else{
			$field[ 'config' ][ 'option' ] = array();
		}

	}

	return $field;

}
add_filter('caldera_forms_render_get_field', function( $field ){
	if ( ! empty( $field[ 'config' ][ 'auto' ] ) && isset( $field[ 'config' ][ 'auto_type' ] ) && 'easy_query' == $field[ 'config' ][ 'auto_type' ] && isset( $field[ 'config' ][ 'easy_query' ] ) ) {
		$easy_query = $field[ 'config' ][ 'easy_query' ];
		$easy_query = calderawp\caeq\options::get_single( $easy_query );
		if( is_array( $easy_query ) ) {
			$caeq = calderawp\caeq\core::get_instance();
			$args = $caeq->build_query_args( $easy_query );
			$query = new WP_Query( $args );
			if( $query->have_posts() ) {
				while( $query->have_posts() ) {
					$query->the_post();
					$field[ 'config' ][ 'option' ][ $query->post->ID ] = array(
						'value' => $query->post->ID,
						'label' => $query->post->post_title
					);
				}
			}
		}
	}

	return $field;
}, 27);
