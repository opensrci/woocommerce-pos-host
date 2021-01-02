<?php
/**
 * REST API Product Variations Controller
 *
 * Handles requests to pos-host/products/variations.
 *
 * @package WooCommerce_pos_host/Classes/API
 */

defined( 'ABSPATH' ) || exit;

/**
 * POS_HOST_REST_Product_Variations_Controller.
 */
class POS_HOST_REST_Product_Variations_Controller extends WC_REST_Product_Variations_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'pos-host';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'products/(?P<product_id>[\d]+)/variations';

	/**
	 * Modify the response.
	 *
	 * @param WC_Data         $object  Object data.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response
	 */
	public function prepare_object_for_response( $object, $request ) {
		$response  = parent::prepare_object_for_response( $object, $request );
		$data      = $response->get_data();
		$variation = new WC_Product_Variation( $data['id'] );

		// Remove unneeded variation data.
		if ( isset( $data ) && is_array( $data ) ) {
			$remove_fields = array(
				'date_created',
				'date_modified',
				'date_modified_gmt',
				'featured',
				'date_on_sale_from',
				'date_on_sale_from_gmt',
				'date_on_sale_to',
				'date_on_sale_to_gmt',
				'virtual',
				'downloadable',
				'downloads',
				'download_limit',
				'download_expiry',
				'date_created',
				'external_url',
				'button_text',
				'reviews_allowed',
				'average_rating',
				'rating_count',
				'related_ids',
				'upsell_ids',
				'cross_sell_ids',
				'menu_order',
			);

			foreach ( $remove_fields as $key ) {
				unset( $data[ $key ] );
			}
		}

		// Set tax_class to standard if not set.
		// $data['tax_class'] = empty( $data['tax_class'] ) ? 'standard' : $data['tax_class'];

		// Include additional product data.
		$data['name'] = $variation->get_name();
		$data['slug'] = $variation->get_slug();

		$response->set_data( $data );

		return rest_ensure_response( $response );
	}

	/**
	 * Get the attributes for a product variation.
	 *
	 * @param WC_Product_Variation $product Variation instance.
	 *
	 * @return array
	 */
	protected function get_attributes( $variation ) {
		$parent     = wc_get_product( $variation->get_parent_id() );
		$attributes = array();

		foreach ( $variation->get_variation_attributes() as $attribute_name => $attribute ) {
			$name = str_replace( 'attribute_', '', $attribute_name );

			if ( empty( $attribute ) && '0' !== $attribute ) {
				continue;
			}

			// Taxonomy-based attributes are prefixed with `pa_`, otherwise simply `attribute_`.
			if ( 0 === strpos( $attribute_name, 'attribute_pa_' ) ) {
				$option_term  = get_term_by( 'slug', $attribute, $name );
				$attributes[] = array(
					'id'     => wc_attribute_taxonomy_id_by_name( $name ),
					'name'   => $this->get_attribute_taxonomy_name( $name, $parent ),
					'slug'   => $name,
					'option' => array(
						'name' => $option_term && ! is_wp_error( $option_term ) ? $option_term->name : $attribute,
						'slug' => $option_term && ! is_wp_error( $option_term ) ? $option_term->slug : $attribute_name,
					),
				);
			} else {
				$attributes[] = array(
					'id'     => 0,
					'name'   => $this->get_attribute_taxonomy_name( $name, $parent ),
					'slug'   => $name,
					'option' => array(
						'name' => $attribute,
						'slug' => wc_sanitize_taxonomy_name( $attribute ),
					),
				);
			}
		}

		return $attributes;
	}

	/**
	 * Get the image for a product variation.
	 *
	 * @param WC_Product_Variation $variation Variation data.
	 * @return array
	 */
	protected function get_image( $variation ) {
		if ( ! $variation->get_image_id() ) {
			return;
		}

		$attachment_id   = $variation->get_image_id();
		$attachment_post = get_post( $attachment_id );
		if ( is_null( $attachment_post ) ) {
			return;
		}

		$attachment = wp_get_attachment_image_src( $attachment_id, 'full' );
		$thumbnail  = wp_get_attachment_image_src( $attachment_id, array( 350, 350 ) );
		if ( ! is_array( $attachment ) || ! is_array( $thumbnail ) ) {
			return;
		}

		if ( ! isset( $image ) ) {
			return array(
				'src'           => current( $attachment ),
				'thumbnail_src' => current( $thumbnail ),
				'name'          => get_the_title( $attachment_id ),
				'alt'           => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
			);
		}
	}
}
