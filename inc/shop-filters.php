<?php
/**
 * AJAX shop filters and toolbar controls.
 *
 * @package Sawerna
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * Filter registry used in the custom sidebar.
 *
 * This remains the central place for curated labels and preferred key mappings.
 * A later category-specific manual override can reuse this registry and select
 * only the keys relevant for a given product category.
 *
 * @return array<string, array{label:string,taxonomies:array<int,string>}>
 */
function sawerna_shop_get_filter_registry() {
	$definitions = array(
		'sugavus' => array(
			'label'    => __( 'Sügavus', 'sawerna' ),
			'taxonomies' => array( 'pa_sugavus-cm', 'pa_sugavus' ),
		),
		'laius' => array(
			'label'    => __( 'Laius', 'sawerna' ),
			'taxonomies' => array( 'pa_laius-cm', 'pa_laius' ),
		),
		'korgus' => array(
			'label'    => __( 'Kõrgus', 'sawerna' ),
			'taxonomies' => array( 'pa_korgus-cm', 'pa_korgus' ),
		),
		'pikkus' => array(
			'label'    => __( 'Pikkus', 'sawerna' ),
			'taxonomies' => array( 'pa_pikkus-cm', 'pa_pikkus' ),
		),
		'madratsi_sisu' => array(
			'label'    => __( 'Madratsi sisu', 'sawerna' ),
			'taxonomies' => array( 'pa_madratsi-sisu' ),
		),
		'moodud' => array(
			'label'    => __( 'Mõõdud', 'sawerna' ),
			'taxonomies' => array( 'pa_moodud' ),
		),
		'varv' => array(
			'label'    => __( 'Värv', 'sawerna' ),
			'taxonomies' => array( 'pa_varv', 'pa_color' ),
		),
		'brand' => array(
			'label'      => __( 'Bränd', 'sawerna' ),
			'taxonomies' => array(
				'product_brand',
				'pwb-brand',
				'yith_product_brand',
				'berocket_brand',
				'pa_brand',
				'pa_brandi',
				'pa_brandid',
			),
		),
		'jalad' => array(
			'label'      => __( 'Jalad', 'sawerna' ),
			'taxonomies' => array( 'pa_jalad' ),
		),
		'jalgade_korgus' => array(
			'label'      => __( 'Jalgade kõrgus', 'sawerna' ),
			'taxonomies' => array( 'pa_jalgade-korgus', 'pa_jalgade_korgus' ),
		),
		'lisafunktsioonid' => array(
			'label'      => __( 'Lisafunktsioonid', 'sawerna' ),
			'taxonomies' => array( 'pa_lisafunktsioonid', 'pa_lisa-funktsioonid' ),
		),
	);

	return apply_filters( 'sawerna_shop_filter_registry', $definitions );
}

/**
 * Backward-compatible alias for the old filter definition accessor.
 *
 * @return array<string, array{label:string,taxonomies:array<int,string>}>
 */
function sawerna_shop_get_filter_definitions() {
	return sawerna_shop_get_filter_registry();
}

/**
 * Build current archive context from the queried object.
 *
 * @return array{taxonomy:string,term_id:int}
 */
function sawerna_shop_get_archive_context() {
	$context = array(
		'taxonomy' => '',
		'term_id'  => 0,
	);

	$object = get_queried_object();
	if ( $object instanceof WP_Term && ! empty( $object->taxonomy ) && ! empty( $object->term_id ) ) {
		$context['taxonomy'] = $object->taxonomy;
		$context['term_id']  = (int) $object->term_id;
	}

	return $context;
}

/**
 * Build context from AJAX request.
 *
 * @param array<string,mixed> $source Request data.
 * @return array{taxonomy:string,term_id:int}
 */
function sawerna_shop_get_context_from_source( array $source ) {
	$taxonomy = isset( $source['context_taxonomy'] ) ? sanitize_key( $source['context_taxonomy'] ) : '';
	$term_id  = isset( $source['context_term_id'] ) ? absint( $source['context_term_id'] ) : 0;

	if ( $taxonomy && ! taxonomy_exists( $taxonomy ) ) {
		$taxonomy = '';
		$term_id  = 0;
	}

	return array(
		'taxonomy' => $taxonomy,
		'term_id'  => $term_id,
	);
}

/**
 * Get the ordered list of taxonomy candidates that may be rendered as filters.
 *
 * @return string[]
 */
function sawerna_shop_get_filter_taxonomy_candidates() {
	$candidates = array();

	foreach ( sawerna_shop_get_filter_registry() as $definition ) {
		if ( empty( $definition['taxonomies'] ) || ! is_array( $definition['taxonomies'] ) ) {
			continue;
		}

		foreach ( $definition['taxonomies'] as $taxonomy ) {
			$taxonomy = sanitize_key( (string) $taxonomy );
			if ( $taxonomy && taxonomy_exists( $taxonomy ) ) {
				$candidates[] = $taxonomy;
			}
		}
	}

	if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
		$attribute_taxonomies = wc_get_attribute_taxonomies();
		if ( is_array( $attribute_taxonomies ) ) {
			foreach ( $attribute_taxonomies as $attribute_taxonomy ) {
				if ( empty( $attribute_taxonomy->attribute_name ) ) {
					continue;
				}

				$taxonomy = wc_attribute_taxonomy_name( $attribute_taxonomy->attribute_name );
				if ( $taxonomy && taxonomy_exists( $taxonomy ) ) {
					$candidates[] = $taxonomy;
				}
			}
		}
	}

	return array_values( array_unique( $candidates ) );
}

/**
 * Build a stable filter key for a taxonomy.
 *
 * @param string $taxonomy Taxonomy slug.
 * @return string
 */
function sawerna_shop_get_filter_key_for_taxonomy( $taxonomy ) {
	$taxonomy = sanitize_key( (string) $taxonomy );
	if ( '' === $taxonomy ) {
		return '';
	}

	foreach ( sawerna_shop_get_filter_registry() as $key => $definition ) {
		if ( empty( $definition['taxonomies'] ) || ! is_array( $definition['taxonomies'] ) ) {
			continue;
		}

		foreach ( $definition['taxonomies'] as $candidate ) {
			if ( sanitize_key( (string) $candidate ) === $taxonomy ) {
				return sanitize_key( (string) $key );
			}
		}
	}

	if ( 0 === strpos( $taxonomy, 'pa_' ) ) {
		return sanitize_key( substr( $taxonomy, 3 ) );
	}

	return sanitize_key( $taxonomy );
}

/**
 * Resolve a preferred taxonomy for a registered filter key.
 *
 * @param string $key Filter key.
 * @return string
 */
function sawerna_shop_get_registered_filter_taxonomy( $key ) {
	$key = sanitize_key( (string) $key );
	if ( '' === $key ) {
		return '';
	}

	$registry = sawerna_shop_get_filter_registry();
	if ( empty( $registry[ $key ]['taxonomies'] ) || ! is_array( $registry[ $key ]['taxonomies'] ) ) {
		return '';
	}

	foreach ( $registry[ $key ]['taxonomies'] as $taxonomy ) {
		$taxonomy = sanitize_key( (string) $taxonomy );
		if ( $taxonomy && taxonomy_exists( $taxonomy ) ) {
			return $taxonomy;
		}
	}

	return '';
}

/**
 * Build a human label for a taxonomy.
 *
 * @param string $taxonomy Taxonomy slug.
 * @return string
 */
function sawerna_shop_get_filter_label_for_taxonomy( $taxonomy ) {
	$taxonomy = sanitize_key( (string) $taxonomy );
	if ( '' === $taxonomy ) {
		return '';
	}

	foreach ( sawerna_shop_get_filter_registry() as $definition ) {
		if ( empty( $definition['taxonomies'] ) || ! is_array( $definition['taxonomies'] ) ) {
			continue;
		}

		foreach ( $definition['taxonomies'] as $candidate ) {
			if ( sanitize_key( (string) $candidate ) === $taxonomy ) {
				return (string) $definition['label'];
			}
		}
	}

	if ( function_exists( 'wc_attribute_label' ) ) {
		$label = wc_attribute_label( $taxonomy );
		if ( is_string( $label ) && '' !== trim( $label ) ) {
			return trim( $label );
		}
	}

	$taxonomy_object = get_taxonomy( $taxonomy );
	if ( $taxonomy_object && ! empty( $taxonomy_object->labels->singular_name ) ) {
		return (string) $taxonomy_object->labels->singular_name;
	}

	return ucwords( str_replace( array( 'pa_', '-', '_' ), array( '', ' ', ' ' ), $taxonomy ) );
}

/**
 * Get the option key used for admin-controlled filter settings.
 *
 * @return string
 */
function sawerna_shop_get_filter_config_option_name() {
	return 'sawerna_shop_filter_config';
}

/**
 * WPML string package/context for filter labels.
 *
 * @return string
 */
function sawerna_shop_get_filter_wpml_context() {
	return 'sawerna-shop-filters';
}

/**
 * Register a filter label with WPML String Translation.
 *
 * @param string $taxonomy Taxonomy slug.
 * @param string $label    Label.
 * @return void
 */
function sawerna_shop_filter_wpml_register_label( $taxonomy, $label ) {
	if ( ! is_string( $label ) || '' === trim( $label ) || ! has_action( 'wpml_register_single_string' ) ) {
		return;
	}

	do_action(
		'wpml_register_single_string',
		sawerna_shop_get_filter_wpml_context(),
		'filter_label_' . sanitize_key( (string) $taxonomy ),
		$label
	);
}

/**
 * Translate a filter label with WPML String Translation.
 *
 * @param string $taxonomy Taxonomy slug.
 * @param string $label    Label.
 * @return string
 */
function sawerna_shop_filter_wpml_translate_label( $taxonomy, $label ) {
	if ( ! is_string( $label ) || '' === trim( $label ) || ! has_filter( 'wpml_translate_single_string' ) ) {
		return (string) $label;
	}

	return (string) apply_filters(
		'wpml_translate_single_string',
		$label,
		sawerna_shop_get_filter_wpml_context(),
		'filter_label_' . sanitize_key( (string) $taxonomy )
	);
}

/**
 * Get supported filter display types.
 *
 * @return array<string,string>
 */
function sawerna_shop_get_filter_type_options() {
	return array(
		'auto'     => __( 'Auto', 'sawerna' ),
		'checkbox' => __( 'Checkbox list', 'sawerna' ),
		'range'    => __( 'Range slider', 'sawerna' ),
	);
}

/**
 * Normalize the filter display type.
 *
 * @param mixed $type Raw type.
 * @return string
 */
function sawerna_shop_normalize_filter_type( $type ) {
	$type = sanitize_key( (string) $type );

	return in_array( $type, array( 'auto', 'checkbox', 'range' ), true ) ? $type : 'auto';
}

/**
 * Normalize a single saved filter configuration row.
 *
 * @param mixed  $entry    Raw entry.
 * @param string $taxonomy Taxonomy slug.
 * @param int    $order    Fallback display order.
 * @return array{enabled:bool,label:string,type:string,order:int,hide_single:bool}
 */
function sawerna_shop_normalize_filter_config_entry( $entry, $taxonomy = '', $order = 100 ) {
	$entry = is_array( $entry ) ? $entry : array();

	$label = isset( $entry['label'] ) ? sanitize_text_field( (string) $entry['label'] ) : '';
	if ( '' === $label && '' !== $taxonomy ) {
		$label = sawerna_shop_get_filter_label_for_taxonomy( $taxonomy );
	}

	return array(
		'enabled'     => ! isset( $entry['enabled'] ) || wc_string_to_bool( (string) $entry['enabled'] ),
		'label'       => $label,
		'type'        => sawerna_shop_normalize_filter_type( isset( $entry['type'] ) ? $entry['type'] : 'auto' ),
		'order'       => isset( $entry['order'] ) ? (int) $entry['order'] : (int) $order,
		'hide_single' => ! empty( $entry['hide_single'] ) && wc_string_to_bool( (string) $entry['hide_single'] ),
	);
}

/**
 * Sanitize the full admin-controlled filter configuration array.
 *
 * @param mixed $config Raw option value.
 * @return array<string,array{enabled:bool,label:string,type:string,order:int,hide_single:bool}>
 */
function sawerna_shop_sanitize_filter_config( $config ) {
	$config = is_array( $config ) ? $config : array();

	if ( isset( $config['filters'] ) && is_array( $config['filters'] ) ) {
		$config = $config['filters'];
	}

	$sanitized = array();
	foreach ( $config as $taxonomy => $entry ) {
		$taxonomy = sanitize_key( (string) $taxonomy );
		if ( '' === $taxonomy ) {
			continue;
		}

		$sanitized[ $taxonomy ] = sawerna_shop_normalize_filter_config_entry( $entry, $taxonomy );
	}

	return $sanitized;
}

/**
 * Register configured filter labels with WPML.
 *
 * @param array<string,array{label:string}> $config Filter config.
 * @return void
 */
function sawerna_shop_register_filter_config_wpml_strings( array $config ) {
	foreach ( $config as $taxonomy => $entry ) {
		if ( empty( $entry['label'] ) || ! is_string( $entry['label'] ) ) {
			continue;
		}

		sawerna_shop_filter_wpml_register_label( (string) $taxonomy, $entry['label'] );
	}
}

/**
 * Read saved filter configuration.
 *
 * @return array<string,array{enabled:bool,label:string,type:string,order:int,hide_single:bool}>
 */
function sawerna_shop_get_filter_config() {
	$stored = get_option( sawerna_shop_get_filter_config_option_name(), array() );

	return sawerna_shop_sanitize_filter_config( $stored );
}

/**
 * Read the effective configuration for a taxonomy.
 *
 * @param string $taxonomy Taxonomy slug.
 * @param int    $order    Fallback order.
 * @return array{enabled:bool,label:string,type:string,order:int,hide_single:bool}
 */
function sawerna_shop_get_filter_config_for_taxonomy( $taxonomy, $order = 100 ) {
	$taxonomy = sanitize_key( (string) $taxonomy );
	$config   = sawerna_shop_get_filter_config();
	$entry    = isset( $config[ $taxonomy ] ) ? $config[ $taxonomy ] : array();

	$normalized = sawerna_shop_normalize_filter_config_entry(
		wp_parse_args(
			$entry,
			array(
				'enabled'     => true,
				'label'       => sawerna_shop_get_filter_label_for_taxonomy( $taxonomy ),
				'type'        => 'auto',
				'order'       => (int) $order,
				'hide_single' => false,
			)
		),
		$taxonomy,
		$order
	);

	$normalized['label'] = sawerna_shop_filter_wpml_translate_label( $taxonomy, $normalized['label'] );

	return $normalized;
}

/**
 * Get product IDs for the current archive context without any active filters.
 *
 * @param array{taxonomy:string,term_id:int} $context Context.
 * @return int[]
 */
function sawerna_shop_get_context_product_ids( array $context ) {
	static $cache = array();

	$cache_key = $context['taxonomy'] . ':' . (int) $context['term_id'];
	if ( isset( $cache[ $cache_key ] ) ) {
		return $cache[ $cache_key ];
	}

	$query_args = array(
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'fields'              => 'ids',
		'posts_per_page'      => -1,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'tax_query'           => WC()->query->get_tax_query(),
		'meta_query'          => WC()->query->get_meta_query(),
	);

	if ( ! empty( $context['taxonomy'] ) && ! empty( $context['term_id'] ) ) {
		$query_args['tax_query'][] = array(
			'taxonomy' => $context['taxonomy'],
			'field'    => 'term_id',
			'terms'    => array( (int) $context['term_id'] ),
		);
	}

	$query = new WP_Query( $query_args );
	$cache[ $cache_key ] = array_map( 'intval', $query->posts );

	return $cache[ $cache_key ];
}

/**
 * Count how many current archive products use a given taxonomy.
 *
 * @param string $taxonomy Taxonomy slug.
 * @param int[]  $product_ids Product IDs in current archive scope.
 * @return int
 */
function sawerna_shop_get_taxonomy_product_coverage_count( $taxonomy, array $product_ids ) {
	static $cache = array();

	$taxonomy  = sanitize_key( (string) $taxonomy );
	$product_ids = array_values( array_unique( array_map( 'intval', $product_ids ) ) );
	if ( '' === $taxonomy || empty( $product_ids ) ) {
		return 0;
	}

	$cache_key = $taxonomy . ':' . md5( implode( ',', $product_ids ) );
	if ( isset( $cache[ $cache_key ] ) ) {
		return $cache[ $cache_key ];
	}

	$terms = wp_get_object_terms(
		$product_ids,
		$taxonomy,
		array(
			'fields' => 'all_with_object_id',
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		$cache[ $cache_key ] = 0;
		return 0;
	}

	$object_ids = array();
	foreach ( $terms as $term ) {
		if ( isset( $term->object_id ) ) {
			$object_ids[] = (int) $term->object_id;
		}
	}

	$cache[ $cache_key ] = count( array_unique( $object_ids ) );

	return $cache[ $cache_key ];
}

/**
 * Get direct child product categories for the current archive context.
 *
 * @param array{taxonomy:string,term_id:int} $context Context.
 * @return int[]
 */
function sawerna_shop_get_context_child_product_category_ids( array $context ) {
	static $cache = array();

	if ( 'product_cat' !== $context['taxonomy'] || empty( $context['term_id'] ) ) {
		return array();
	}

	$cache_key = (int) $context['term_id'];
	if ( isset( $cache[ $cache_key ] ) ) {
		return $cache[ $cache_key ];
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => (int) $context['term_id'],
			'hide_empty' => true,
			'fields'     => 'ids',
		)
	);

	$cache[ $cache_key ] = is_wp_error( $terms ) ? array() : array_map( 'intval', $terms );

	return $cache[ $cache_key ];
}

/**
 * Count how many direct child categories meaningfully use a taxonomy.
 *
 * @param string $taxonomy Taxonomy slug.
 * @param int[]  $child_term_ids Direct child product category IDs.
 * @return int
 */
function sawerna_shop_get_taxonomy_child_category_match_count( $taxonomy, array $child_term_ids ) {
	$matches = 0;

	foreach ( $child_term_ids as $child_term_id ) {
		$child_context = array(
			'taxonomy' => 'product_cat',
			'term_id'  => (int) $child_term_id,
		);

		$child_product_ids = sawerna_shop_get_context_product_ids( $child_context );
		if ( count( $child_product_ids ) < 2 ) {
			continue;
		}

		$term_ids = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'object_ids' => $child_product_ids,
				'hide_empty' => false,
				'fields'     => 'ids',
				'number'     => 2,
			)
		);

		if ( is_wp_error( $term_ids ) || count( $term_ids ) < 2 ) {
			continue;
		}

		$matches++;
	}

	return $matches;
}

/**
 * Resolve filter definitions for a specific archive context.
 *
 * This is intentionally context-aware now. The current category decides which
 * product attributes become visible filters. A future manual category override
 * can hook into the returned array and replace or reorder it without needing
 * to rewrite the sidebar/query pipeline.
 *
 * @param array{taxonomy:string,term_id:int}|null $context Context.
 * @return array<string, array{label:string,taxonomy:string}>
 */
function sawerna_shop_get_resolved_filter_definitions( $context = null ) {
	static $cache = array();

	if ( null === $context ) {
		$context = sawerna_shop_get_archive_context();
	}

	$cache_key = $context['taxonomy'] . ':' . (int) $context['term_id'];
	if ( isset( $cache[ $cache_key ] ) ) {
		return $cache[ $cache_key ];
	}

	$product_ids = sawerna_shop_get_context_product_ids( $context );
	if ( empty( $product_ids ) ) {
		$cache[ $cache_key ] = array();
		return $cache[ $cache_key ];
	}

	$resolved = array();
	$position = 0;

	foreach ( sawerna_shop_get_filter_taxonomy_candidates() as $taxonomy ) {
		$taxonomy = sanitize_key( (string) $taxonomy );
		if ( '' === $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}

		$config = sawerna_shop_get_filter_config_for_taxonomy( $taxonomy, $position * 10 );
		$position++;

		if ( empty( $config['enabled'] ) ) {
			continue;
		}

		$minimum_terms = ! empty( $config['hide_single'] ) ? 2 : 1;
		$minimum_terms = max( 1, (int) apply_filters( 'sawerna_shop_filter_minimum_terms', $minimum_terms, $context, $product_ids, $taxonomy, $config ) );

		$term_ids = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'object_ids' => $product_ids,
				'hide_empty' => false,
				'fields'     => 'ids',
				'number'     => $minimum_terms,
			)
		);

		if ( is_wp_error( $term_ids ) || count( $term_ids ) < $minimum_terms ) {
			continue;
		}

		$key = sawerna_shop_get_filter_key_for_taxonomy( $taxonomy );
		if ( '' === $key ) {
			continue;
		}

		$unique_key = $key;
		if ( isset( $resolved[ $unique_key ] ) && $resolved[ $unique_key ]['taxonomy'] !== $taxonomy ) {
			$unique_key = sanitize_key( $key . '_' . $taxonomy );
		}

		$resolved[ $unique_key ] = array(
			'label'    => '' !== trim( (string) $config['label'] ) ? (string) $config['label'] : sawerna_shop_get_filter_label_for_taxonomy( $taxonomy ),
			'taxonomy' => $taxonomy,
			'type'     => $config['type'],
			'order'    => $config['order'],
		);
	}

	uasort(
		$resolved,
		static function ( $left, $right ) {
			$left_order  = isset( $left['order'] ) ? (int) $left['order'] : 100;
			$right_order = isset( $right['order'] ) ? (int) $right['order'] : 100;

			if ( $left_order === $right_order ) {
				return strcasecmp( (string) $left['label'], (string) $right['label'] );
			}

			return $left_order <=> $right_order;
		}
	);

	$cache[ $cache_key ] = apply_filters( 'sawerna_shop_filter_definitions_for_context', $resolved, $context, $product_ids );

	return $cache[ $cache_key ];
}

/**
 * Get query state from request array.
 *
 * @param array<string,mixed>|null $source Source data.
 * @param array{taxonomy:string,term_id:int}|null $context Context.
 * @return array<string,mixed>
 */
function sawerna_shop_get_state( $source = null, $context = null ) {
	if ( null === $source ) {
		$source = wp_unslash( $_GET );
	}

	if ( null === $context ) {
		$context = sawerna_shop_get_archive_context();
	}

	$allowed_per_page = array( 9, 12, 16, 24, 48 );
	$allowed_columns  = array( 3, 4 );
	$allowed_orderby  = array( 'menu_order', 'popularity', 'rating', 'price_asc', 'price_desc', 'title_asc', 'title_desc', 'date_desc', 'date_asc' );

	$per_page = isset( $source['per_page'] ) ? absint( $source['per_page'] ) : 24;
	if ( ! in_array( $per_page, $allowed_per_page, true ) ) {
		$per_page = 24;
	}

	$columns = isset( $source['columns'] ) ? absint( $source['columns'] ) : 3;
	if ( ! in_array( $columns, $allowed_columns, true ) ) {
		$columns = 3;
	}

	$orderby = isset( $source['orderby'] ) ? sanitize_key( $source['orderby'] ) : 'menu_order';
	if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
		$orderby = 'menu_order';
	}

	$page = isset( $source['page'] ) ? absint( $source['page'] ) : 0;
	if ( $page < 1 ) {
		$page = isset( $source['paged'] ) ? absint( $source['paged'] ) : 1;
	}
	if ( $page < 1 ) {
		$page = 1;
	}

	$min_price = isset( $source['min_price'] ) ? (float) $source['min_price'] : 0;
	$max_price = isset( $source['max_price'] ) ? (float) $source['max_price'] : 0;
	if ( $min_price < 0 ) {
		$min_price = 0;
	}
	if ( $max_price < 0 ) {
		$max_price = 0;
	}

	$interior_images = false;
	if ( isset( $source['interior_images'] ) ) {
		$interior_images = wc_string_to_bool( (string) $source['interior_images'] );
	}

	$terms       = array();
	$definitions = sawerna_shop_get_resolved_filter_definitions( $context );

	// Support AJAX state payload format: terms[key] = [slug, ...].
	if ( isset( $source['terms'] ) && is_array( $source['terms'] ) ) {
		foreach ( $source['terms'] as $key => $value ) {
			$key = sanitize_key( (string) $key );
			if ( '' === $key || empty( $definitions[ $key ]['taxonomy'] ) ) {
				continue;
			}

			if ( is_string( $value ) ) {
				$value = explode( ',', $value );
			}

			if ( ! is_array( $value ) ) {
				continue;
			}

			$selected = array();
			foreach ( $value as $slug ) {
				$slug = sanitize_title( (string) $slug );
				if ( '' !== $slug ) {
					$selected[] = $slug;
				}
			}

			$selected = array_values( array_unique( $selected ) );
			if ( ! empty( $selected ) ) {
				$terms[ $key ] = $selected;
			}
		}
	}

	// Support URL state format: f_key=slug1,slug2.
	foreach ( $definitions as $key => $definition ) {
		if ( ! taxonomy_exists( $definition['taxonomy'] ) ) {
			continue;
		}

		$param = 'f_' . $key;
		if ( ! isset( $source[ $param ] ) ) {
			continue;
		}

		$value = $source[ $param ];
		if ( is_string( $value ) ) {
			$value = explode( ',', $value );
		}

		if ( ! is_array( $value ) ) {
			continue;
		}

		$selected = array();
		foreach ( $value as $slug ) {
			$slug = sanitize_title( (string) $slug );
			if ( '' !== $slug ) {
				$selected[] = $slug;
			}
		}

		$selected = array_values( array_unique( $selected ) );
		if ( ! empty( $selected ) ) {
			$terms[ $key ] = $selected;
		}
	}

	return array(
		'per_page'        => $per_page,
		'columns'         => $columns,
		'orderby'         => $orderby,
		'page'            => $page,
		'min_price'       => $min_price,
		'max_price'       => $max_price,
		'interior_images' => $interior_images,
		'terms'           => $terms,
	);
}

/**
 * Build a URL query array from state.
 *
 * @param array<string,mixed> $state State.
 * @return array<string,mixed>
 */
function sawerna_shop_state_to_query_args( array $state ) {
	$query_args = array();

	if ( ! empty( $state['per_page'] ) ) {
		$query_args['per_page'] = (int) $state['per_page'];
	}
	if ( ! empty( $state['columns'] ) ) {
		$query_args['columns'] = (int) $state['columns'];
	}
	if ( ! empty( $state['orderby'] ) ) {
		$query_args['orderby'] = (string) $state['orderby'];
	}
	if ( ! empty( $state['page'] ) && (int) $state['page'] > 1 ) {
		$query_args['paged'] = (int) $state['page'];
	}

	if ( ! empty( $state['min_price'] ) ) {
		$query_args['min_price'] = (string) $state['min_price'];
	}
	if ( ! empty( $state['max_price'] ) ) {
		$query_args['max_price'] = (string) $state['max_price'];
	}
	if ( ! empty( $state['interior_images'] ) ) {
		$query_args['interior_images'] = '1';
	}

	if ( ! empty( $state['terms'] ) && is_array( $state['terms'] ) ) {
		foreach ( $state['terms'] as $key => $slugs ) {
			if ( empty( $slugs ) || ! is_array( $slugs ) ) {
				continue;
			}
			$query_args[ 'f_' . $key ] = implode( ',', array_map( 'sanitize_title', $slugs ) );
		}
	}

	return $query_args;
}

/**
 * Build WP_Query args for current state.
 *
 * @param array<string,mixed> $state State.
 * @param array{taxonomy:string,term_id:int} $context Archive context.
 * @param string $exclude_taxonomy Taxonomy/meta key to skip.
 * @return array<string,mixed>
 */
function sawerna_shop_get_query_args( array $state, array $context, $exclude_taxonomy = '' ) {
	$tax_query = WC()->query->get_tax_query();
	$meta_query = WC()->query->get_meta_query();

	if ( ! empty( $context['taxonomy'] ) && ! empty( $context['term_id'] ) && $context['taxonomy'] !== $exclude_taxonomy ) {
		$tax_query[] = array(
			'taxonomy' => $context['taxonomy'],
			'field'    => 'term_id',
			'terms'    => array( (int) $context['term_id'] ),
		);
	}

	$definitions = sawerna_shop_get_resolved_filter_definitions( $context );
	if ( ! empty( $state['terms'] ) && is_array( $state['terms'] ) ) {
		foreach ( $state['terms'] as $key => $slugs ) {
			if ( empty( $definitions[ $key ]['taxonomy'] ) || $definitions[ $key ]['taxonomy'] === $exclude_taxonomy ) {
				continue;
			}
			if ( ! is_array( $slugs ) || empty( $slugs ) ) {
				continue;
			}
			$tax_query[] = array(
				'taxonomy' => $definitions[ $key ]['taxonomy'],
				'field'    => 'slug',
				'terms'    => array_values( array_unique( array_map( 'sanitize_title', $slugs ) ) ),
				'operator' => 'IN',
			);
		}
	}

	$min_price = isset( $state['min_price'] ) ? (float) $state['min_price'] : 0;
	$max_price = isset( $state['max_price'] ) ? (float) $state['max_price'] : 0;
	if ( $max_price > 0 && $max_price >= $min_price && '_price' !== $exclude_taxonomy ) {
		$meta_query[] = array(
			'key'     => '_price',
			'value'   => array( $min_price, $max_price ),
			'compare' => 'BETWEEN',
			'type'    => 'DECIMAL(10,2)',
		);
	}

	$query_args = array(
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'tax_query'           => $tax_query,
		'meta_query'          => $meta_query,
		'paged'               => isset( $state['page'] ) ? max( 1, (int) $state['page'] ) : 1,
		'posts_per_page'      => isset( $state['per_page'] ) ? (int) $state['per_page'] : 24,
	);

	$orderby = isset( $state['orderby'] ) ? (string) $state['orderby'] : 'menu_order';
	switch ( $orderby ) {
		case 'popularity':
			$query_args['meta_key'] = 'total_sales';
			$query_args['orderby']  = 'meta_value_num';
			$query_args['order']    = 'DESC';
			break;
		case 'rating':
			$query_args['meta_key'] = '_wc_average_rating';
			$query_args['orderby']  = 'meta_value_num';
			$query_args['order']    = 'DESC';
			break;
		case 'price_asc':
			$query_args['meta_key'] = '_price';
			$query_args['orderby']  = 'meta_value_num';
			$query_args['order']    = 'ASC';
			break;
		case 'price_desc':
			$query_args['meta_key'] = '_price';
			$query_args['orderby']  = 'meta_value_num';
			$query_args['order']    = 'DESC';
			break;
		case 'title_asc':
			$query_args['orderby'] = 'title';
			$query_args['order']   = 'ASC';
			break;
		case 'title_desc':
			$query_args['orderby'] = 'title';
			$query_args['order']   = 'DESC';
			break;
		case 'date_asc':
			$query_args['orderby'] = 'date';
			$query_args['order']   = 'ASC';
			break;
		case 'date_desc':
			$query_args['orderby'] = 'date';
			$query_args['order']   = 'DESC';
			break;
		case 'menu_order':
		default:
			$query_args['orderby'] = array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			);
			break;
	}

	return $query_args;
}

/**
 * Build a product query for current state.
 *
 * @param array<string,mixed> $state State.
 * @param array{taxonomy:string,term_id:int} $context Archive context.
 * @param string $exclude_taxonomy Taxonomy/meta key to skip.
 * @return WP_Query
 */
function sawerna_shop_build_query( array $state, array $context, $exclude_taxonomy = '' ) {
	return new WP_Query( sawerna_shop_get_query_args( $state, $context, $exclude_taxonomy ) );
}

/**
 * Render order options.
 *
 * @return array<string,string>
 */
function sawerna_shop_get_order_options() {
	return array(
		'menu_order' => __( 'Populaarsuse järgi', 'sawerna' ),
		'popularity' => __( 'Populaarsuse järgi', 'sawerna' ),
		'rating'     => __( 'Keskmise hinnangu järgi', 'sawerna' ),
		'date_desc'  => __( 'Uudsuse järgi', 'sawerna' ),
		'price_asc'  => __( 'Hind alates odavamast', 'sawerna' ),
		'price_desc' => __( 'Hind alates kallimast', 'sawerna' ),
	);
}

/**
 * Get URL base for current context.
 *
 * @param array{taxonomy:string,term_id:int} $context Context.
 * @return string
 */
function sawerna_shop_get_context_url( array $context ) {
	if ( ! empty( $context['taxonomy'] ) && ! empty( $context['term_id'] ) ) {
		$term_link = get_term_link( (int) $context['term_id'], $context['taxonomy'] );
		if ( ! is_wp_error( $term_link ) ) {
			return $term_link;
		}
	}

	$shop_page_id = wc_get_page_id( 'shop' );
	if ( $shop_page_id > 0 ) {
		return get_permalink( $shop_page_id );
	}

	return wc_get_page_permalink( 'shop' );
}

/**
 * Render toolbar.
 *
 * @param array<string,mixed> $state State.
 * @param int $total Found products.
 * @return string
 */
function sawerna_shop_render_toolbar( array $state, $total ) {
	$per_page = max( 1, (int) $state['per_page'] );
	$page     = max( 1, (int) $state['page'] );
	$start    = 0;
	$end      = 0;
	$interior_enabled = ! empty( $state['interior_images'] );

	if ( $total > 0 ) {
		$start = ( ( $page - 1 ) * $per_page ) + 1;
		$end   = min( $total, $page * $per_page );
	}

	ob_start();
	?>
	<div class="sawerna-shop-toolbar">
		<div class="sawerna-shop-toolbar__result"><?php echo esc_html( sprintf( __( '%d toodet', 'sawerna' ), (int) $total ) ); ?></div>
		<div class="sawerna-shop-toolbar__mobile-result">
			<?php
			echo esc_html(
				sprintf(
					/* translators: 1: start product number, 2: end product number, 3: total products */
					__( 'Kuvatakse: %1$d-%2$d toodet %3$d-st', 'sawerna' ),
					(int) $start,
					(int) $end,
					(int) $total
				)
			);
			?>
		</div>
			<div class="sawerna-shop-toolbar__controls">
				<button
					type="button"
					class="sawerna-shop-control sawerna-shop-control--filter-toggle"
				data-shop-mobile-open="1"
				aria-controls="shop-filters-panel"
				aria-expanded="false"
			>
				<span class="sawerna-shop-control__icon" aria-hidden="true">
					<span></span>
					<span></span>
					<span></span>
				</span>
					<span><?php esc_html_e( 'Filtreeri ja sorteeri tooteid', 'sawerna' ); ?></span>
				</button>

				<label class="sawerna-shop-control sawerna-shop-control--interior-toggle" for="sawerna-desktop-interior-toggle">
					<span><?php esc_html_e( 'Interjööriga pildid', 'sawerna' ); ?></span>
					<span class="sawerna-filter__toggle">
						<input type="checkbox" id="sawerna-desktop-interior-toggle" data-shop-interior-toggle="1" <?php checked( $interior_enabled ); ?>>
						<span class="sawerna-filter__toggle-slider" aria-hidden="true"></span>
					</span>
				</label>

				<label class="sawerna-shop-control sawerna-shop-control--select">
					<span class="screen-reader-text"><?php esc_html_e( 'Tooteid lehel', 'sawerna' ); ?></span>
				<select data-shop-control="per_page" aria-label="<?php esc_attr_e( 'Tooteid lehel', 'sawerna' ); ?>">
					<?php foreach ( array( 9, 12, 16, 24, 48 ) as $value ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( (int) $state['per_page'], $value ); ?>>
							<?php echo esc_html( sprintf( __( '%d toodet', 'sawerna' ), $value ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>
			<label class="sawerna-shop-control sawerna-shop-control--select">
				<span class="screen-reader-text"><?php esc_html_e( 'Järjesta', 'sawerna' ); ?></span>
				<select data-shop-control="orderby" aria-label="<?php esc_attr_e( 'Järjesta', 'sawerna' ); ?>">
					<?php foreach ( sawerna_shop_get_order_options() as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( (string) $state['orderby'], $value ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>
		</div>
	</div>
	<?php

	return (string) ob_get_clean();
}

/**
 * Render active filter chips.
 *
 * @param array<string,mixed> $state State.
 * @param array{taxonomy:string,term_id:int}|null $context Context.
 * @return string
 */
function sawerna_shop_render_active_filters( array $state, $context = null ) {
	$chips       = array();
	$definitions = sawerna_shop_get_resolved_filter_definitions( $context );

	if ( ! empty( $state['terms'] ) && is_array( $state['terms'] ) ) {
		foreach ( $state['terms'] as $key => $slugs ) {
			if ( empty( $definitions[ $key ]['taxonomy'] ) || ! taxonomy_exists( $definitions[ $key ]['taxonomy'] ) ) {
				continue;
			}
			foreach ( $slugs as $slug ) {
				$term = get_term_by( 'slug', $slug, $definitions[ $key ]['taxonomy'] );
				if ( ! $term || is_wp_error( $term ) ) {
					continue;
				}
				$chips[] = array(
					'label' => $term->name,
					'type'  => 'term',
					'key'   => $key,
					'value' => $slug,
				);
			}
		}
	}

	if ( ! empty( $state['max_price'] ) && (float) $state['max_price'] > 0 ) {
		$chips[] = array(
			'label' => sprintf(
				/* translators: 1: min price 2: max price */
				__( 'Hind: %1$s - %2$s', 'sawerna' ),
				wc_price( (float) $state['min_price'] ),
				wc_price( (float) $state['max_price'] )
			),
			'type'  => 'price',
			'key'   => 'price',
			'value' => '',
		);
	}

	if ( ! empty( $state['interior_images'] ) ) {
		$chips[] = array(
			'label' => __( 'Interjööriga pildid', 'sawerna' ),
			'type'  => 'toggle',
			'key'   => 'interior_images',
			'value' => '1',
		);
	}

	$active_count = count( $chips );

	ob_start();
	?>
	<div class="sawerna-shop-active-filters<?php echo empty( $chips ) ? ' is-empty' : ''; ?>">
		<?php if ( ! empty( $chips ) ) : ?>
			<ul class="sawerna-shop-active-filters__list">
				<?php foreach ( $chips as $chip ) : ?>
					<li>
						<button
							type="button"
							class="sawerna-shop-chip"
							data-remove-filter="<?php echo esc_attr( $chip['key'] ); ?>"
							data-remove-value="<?php echo esc_attr( $chip['value'] ); ?>"
						>
							<span><?php echo wp_kses_post( $chip['label'] ); ?></span>
							<span aria-hidden="true">&times;</span>
						</button>
					</li>
				<?php endforeach; ?>
			</ul>
			<button type="button" class="sawerna-shop-active-filters__clear" data-clear-all="1">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %d active filter count */
						__( 'Tühista kõik (%d)', 'sawerna' ),
						(int) $active_count
					)
				);
				?>
			</button>
		<?php endif; ?>
	</div>
	<?php

	return (string) ob_get_clean();
}

/**
 * Get product IDs for count calculation.
 *
 * @param array<string,mixed> $state State.
 * @param array{taxonomy:string,term_id:int} $context Context.
 * @param string $exclude_taxonomy Excluded taxonomy.
 * @return int[]
 */
function sawerna_shop_get_count_ids( array $state, array $context, $exclude_taxonomy ) {
	$count_state              = $state;
	$count_state['page']      = 1;
	$count_state['per_page']  = -1;
	$count_query_args         = sawerna_shop_get_query_args( $count_state, $context, $exclude_taxonomy );
	$count_query_args['fields'] = 'ids';
	$count_query_args['no_found_rows'] = true;

	$count_query = new WP_Query( $count_query_args );
	return array_map( 'intval', $count_query->posts );
}

/**
 * Resolve price bounds from product IDs.
 *
 * @param int[] $ids Product IDs.
 * @return array{min:float,max:float}
 */
function sawerna_shop_get_price_bounds_for_ids( array $ids ) {
	if ( empty( $ids ) ) {
		return array(
			'min' => 0,
			'max' => 0,
		);
	}

	global $wpdb;

	$ids_sql = implode( ',', array_map( 'absint', array_unique( $ids ) ) );
	if ( '' === $ids_sql ) {
		return array(
			'min' => 0,
			'max' => 0,
		);
	}

	$table = $wpdb->postmeta;
	$sql   = "
		SELECT
			MIN(CAST(meta_value AS DECIMAL(10,2))) AS min_price,
			MAX(CAST(meta_value AS DECIMAL(10,2))) AS max_price
		FROM {$table}
		WHERE meta_key = '_price'
			AND meta_value <> ''
			AND post_id IN ({$ids_sql})
	";

	$prices = $wpdb->get_row( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	if ( ! $prices ) {
		return array(
			'min' => 0,
			'max' => 0,
		);
	}

	return array(
		'min' => isset( $prices->min_price ) ? (float) $prices->min_price : 0,
		'max' => isset( $prices->max_price ) ? (float) $prices->max_price : 0,
	);
}

/**
 * Parse a numeric value from a string.
 *
 * @param string $value Value to parse.
 * @return float|null
 */
function sawerna_shop_parse_numeric_value( $value ) {
	$value = is_scalar( $value ) ? (string) $value : '';
	if ( '' === $value ) {
		return null;
	}

	if ( ! preg_match( '/([0-9]+(?:[\\.,][0-9]+)?)/', $value, $matches ) ) {
		return null;
	}

	$number = str_replace( ',', '.', $matches[1] );
	if ( ! is_numeric( $number ) ) {
		return null;
	}

	return (float) $number;
}

/**
 * Build numeric term metadata for slider filters.
 *
 * @param WP_Term[] $terms Terms to parse.
 * @return array<int,array{slug:string,label:string,value:float}>
 */
function sawerna_shop_get_numeric_terms_data( array $terms ) {
	$data = array();

	foreach ( $terms as $term ) {
		if ( ! $term instanceof WP_Term ) {
			continue;
		}

		$value = sawerna_shop_parse_numeric_value( $term->name );
		if ( null === $value ) {
			$value = sawerna_shop_parse_numeric_value( $term->slug );
		}
		if ( null === $value ) {
			continue;
		}

		$data[] = array(
			'slug'  => $term->slug,
			'label' => $term->name,
			'value' => $value,
		);
	}

	usort(
		$data,
		static function ( $left, $right ) {
			return (float) $left['value'] <=> (float) $right['value'];
		}
	);

	return $data;
}

/**
 * Resolve min/max bounds for numeric slider values.
 *
 * @param array<int,array{slug:string,label:string,value:float}> $numeric_terms Numeric term data.
 * @return array{min:float,max:float}
 */
function sawerna_shop_get_numeric_bounds( array $numeric_terms ) {
	if ( empty( $numeric_terms ) ) {
		return array(
			'min' => 0,
			'max' => 0,
		);
	}

	$values = array();
	foreach ( $numeric_terms as $entry ) {
		$values[] = (float) $entry['value'];
	}

	return array(
		'min' => min( $values ),
		'max' => max( $values ),
	);
}

/**
 * Choose a numeric slider step based on available values.
 *
 * @param array<int,array{slug:string,label:string,value:float}> $numeric_terms Numeric term data.
 * @return float
 */
function sawerna_shop_get_numeric_step( array $numeric_terms ) {
	foreach ( $numeric_terms as $entry ) {
		$value = (float) $entry['value'];
		if ( $value !== floor( $value ) ) {
			return 0.1;
		}
	}

	return 1;
}

/**
 * Format numeric value for range input fields.
 *
 * @param float $value Numeric value.
 * @return string
 */
function sawerna_shop_format_range_value( $value ) {
	if ( ! is_numeric( $value ) ) {
		return '0';
	}

	$formatted = rtrim( rtrim( number_format( (float) $value, 2, '.', '' ), '0' ), '.' );
	return '' === $formatted ? '0' : $formatted;
}

/**
 * Check whether a term label is a plain numeric value suitable for a range UI.
 *
 * @param string $value Term label or slug.
 * @return bool
 */
function sawerna_shop_is_range_numeric_label( $value ) {
	$value = trim( is_scalar( $value ) ? (string) $value : '' );
	if ( '' === $value ) {
		return false;
	}

	if ( preg_match( '/[x×]/i', $value ) ) {
		return false;
	}

	return (bool) preg_match( '/^[0-9]+(?:[\\.,][0-9]+)?\\s*(?:cm|mm|m|kg|g)?$/i', $value );
}

/**
 * Determine whether the current filter should use the numeric range UI.
 *
 * @param string    $key Filter key.
 * @param WP_Term[] $terms Terms available in the current scope.
 * @return bool
 */
function sawerna_shop_is_numeric_range_filter( $key, array $terms ) {
	$explicit_range_filters = array( 'sugavus', 'laius', 'korgus', 'jalgade_korgus' );

	if ( in_array( $key, $explicit_range_filters, true ) ) {
		return true;
	}

	foreach ( $terms as $term ) {
		if ( ! $term instanceof WP_Term ) {
			continue;
		}

		if ( ! sawerna_shop_is_range_numeric_label( $term->name ) ) {
			return false;
		}
	}

	$numeric_terms = sawerna_shop_get_numeric_terms_data( $terms );
	if ( count( $numeric_terms ) < 2 ) {
		return false;
	}

	$values = array_unique(
		array_map(
			static function ( $entry ) {
				return (string) $entry['value'];
			},
			$numeric_terms
		)
	);

	return count( $values ) > 1;
}

/**
 * Determine whether a resolved filter definition should render as a range.
 *
 * @param string              $key        Filter key.
 * @param array<string,mixed> $definition Resolved filter definition.
 * @param WP_Term[]           $terms      Terms available in the current scope.
 * @return bool
 */
function sawerna_shop_filter_definition_uses_range( $key, array $definition, array $terms ) {
	$type = isset( $definition['type'] ) ? sawerna_shop_normalize_filter_type( $definition['type'] ) : 'auto';

	if ( 'range' === $type ) {
		return true;
	}

	if ( 'checkbox' === $type ) {
		return false;
	}

	return sawerna_shop_is_numeric_range_filter( $key, $terms );
}

/**
 * Render filter sidebar.
 *
 * @param array<string,mixed> $state State.
 * @param array{taxonomy:string,term_id:int} $context Context.
 * @return string
 */
function sawerna_shop_render_sidebar( array $state, array $context ) {
	$definitions = sawerna_shop_get_resolved_filter_definitions( $context );
	$has_active_filters = ! empty( $state['max_price'] ) || ! empty( $state['terms'] ) || ! empty( $state['interior_images'] );
	$selected_order = isset( $state['orderby'] ) ? (string) $state['orderby'] : 'menu_order';
	$interior_enabled = ! empty( $state['interior_images'] );
	if ( 'menu_order' === $selected_order ) {
		$selected_order = 'popularity';
	}

	$count_ids = sawerna_shop_get_count_ids(
		array_merge(
			$state,
			array(
				'page'      => 1,
				'per_page'  => -1,
				'min_price' => 0,
				'max_price' => 0,
			)
		),
		$context,
		'_price'
	);
	$price_bounds = sawerna_shop_get_price_bounds_for_ids( $count_ids );

	$current_min = ! empty( $state['max_price'] ) ? (float) $state['min_price'] : $price_bounds['min'];
	$current_max = ! empty( $state['max_price'] ) ? (float) $state['max_price'] : $price_bounds['max'];

	ob_start();
	?>
	<div class="sawerna-filter" data-filter-sidebar="1">
		<div class="sawerna-filter__header">
			<div class="sawerna-filter__title">
				<span class="sawerna-filter__title-desktop"><?php esc_html_e( 'Filtreeri tooteid', 'sawerna' ); ?></span>
				<span class="sawerna-filter__title-mobile"><?php esc_html_e( 'Filtreeri / Sorteeri', 'sawerna' ); ?></span>
			</div>
			<div class="sawerna-filter__header-actions">
				<?php if ( $has_active_filters ) : ?>
					<button type="button" class="sawerna-filter__reset" data-clear-all="1">
						<span><?php esc_html_e( 'Tühista kõik', 'sawerna' ); ?></span>
						<span class="sawerna-filter__reset-icon" aria-hidden="true">
							<?php echo sawerna_get_svg_inline( 'Asset 20.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
					</button>
				<?php endif; ?>
				<button type="button" class="sawerna-filter__close" data-shop-mobile-close="1" aria-label="<?php esc_attr_e( 'Sulge filtrid', 'sawerna' ); ?>">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
		</div>

			<div class="sawerna-filter__mobile-top">
				<label class="sawerna-filter__toggle-row" for="sawerna-mobile-interior-toggle">
					<span><?php esc_html_e( 'Interjööriga pildid', 'sawerna' ); ?></span>
					<span class="sawerna-filter__toggle">
						<input type="checkbox" id="sawerna-mobile-interior-toggle" data-shop-interior-toggle="1" <?php checked( $interior_enabled ); ?>>
						<span class="sawerna-filter__toggle-slider" aria-hidden="true"></span>
					</span>
				</label>
		</div>

		<div class="sawerna-filter__group sawerna-filter__group--mobile-sort is-open" data-filter-group="mobile-sort">
			<button type="button" class="sawerna-filter__group-toggle" data-group-toggle="mobile-sort">
				<span><?php esc_html_e( 'Reasta', 'sawerna' ); ?></span>
			</button>
			<div class="sawerna-filter__group-content">
				<ul class="sawerna-filter__sort-options" role="radiogroup" aria-label="<?php esc_attr_e( 'Järjesta', 'sawerna' ); ?>">
					<?php foreach ( sawerna_shop_get_order_options() as $value => $label ) : ?>
						<?php if ( ! in_array( $value, array( 'popularity', 'rating', 'date_desc', 'price_asc', 'price_desc' ), true ) ) : ?>
							<?php continue; ?>
						<?php endif; ?>
						<li>
							<label>
								<input type="radio" name="mobile_shop_orderby" value="<?php echo esc_attr( $value ); ?>" data-mobile-sort="1" <?php checked( $selected_order, $value ); ?>>
								<span><?php echo esc_html( $label ); ?></span>
							</label>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>

		<div class="sawerna-filter__group is-open" data-filter-group="price">
			<button type="button" class="sawerna-filter__group-toggle" data-group-toggle="price">
				<span><?php esc_html_e( 'Hind', 'sawerna' ); ?></span>
			</button>
			<div class="sawerna-filter__group-content">
				<div class="sawerna-price-filter" data-price-filter="1">
					<div class="sawerna-price-filter__ranges">
						<input type="range" min="<?php echo esc_attr( (string) floor( $price_bounds['min'] ) ); ?>" max="<?php echo esc_attr( (string) ceil( $price_bounds['max'] ) ); ?>" step="1" value="<?php echo esc_attr( (string) floor( $current_min ) ); ?>" data-price-control="min">
						<input type="range" min="<?php echo esc_attr( (string) floor( $price_bounds['min'] ) ); ?>" max="<?php echo esc_attr( (string) ceil( $price_bounds['max'] ) ); ?>" step="1" value="<?php echo esc_attr( (string) ceil( $current_max ) ); ?>" data-price-control="max">
					</div>
					<div class="sawerna-price-filter__values">
						<label>
							<span><?php esc_html_e( 'Alates', 'sawerna' ); ?></span>
							<input type="number" data-price-input="min" value="<?php echo esc_attr( (string) floor( $current_min ) ); ?>" min="<?php echo esc_attr( (string) floor( $price_bounds['min'] ) ); ?>" max="<?php echo esc_attr( (string) ceil( $price_bounds['max'] ) ); ?>">
						</label>
						<label>
							<span><?php esc_html_e( 'Kuni', 'sawerna' ); ?></span>
							<input type="number" data-price-input="max" value="<?php echo esc_attr( (string) ceil( $current_max ) ); ?>" min="<?php echo esc_attr( (string) floor( $price_bounds['min'] ) ); ?>" max="<?php echo esc_attr( (string) ceil( $price_bounds['max'] ) ); ?>">
						</label>
					</div>
					<?php if ( ! empty( $state['max_price'] ) ) : ?>
						<button type="button" class="sawerna-filter__section-clear" data-clear-filter="price">
							<span><?php esc_html_e( 'Tühista filter', 'sawerna' ); ?></span>
							<span class="sawerna-filter__section-clear-icon" aria-hidden="true">
								<?php echo sawerna_get_svg_inline( 'Asset 20.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
						</button>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<?php foreach ( $definitions as $key => $definition ) : ?>
			<?php
			if ( ! taxonomy_exists( $definition['taxonomy'] ) ) {
				continue;
			}

			$ids = sawerna_shop_get_count_ids( $state, $context, $definition['taxonomy'] );
			$terms = get_terms(
				array(
					'taxonomy'   => $definition['taxonomy'],
					'hide_empty' => true,
					'object_ids' => $ids,
				)
			);

			$selected = ! empty( $state['terms'][ $key ] ) && is_array( $state['terms'][ $key ] ) ? $state['terms'][ $key ] : array();
			$is_open  = ! empty( $selected );
			$is_range = ! is_wp_error( $terms ) && ! empty( $terms ) && sawerna_shop_filter_definition_uses_range( $key, $definition, $terms );
			?>
			<div class="sawerna-filter__group<?php echo $is_open ? ' is-open' : ''; ?>" data-filter-group="<?php echo esc_attr( $key ); ?>">
				<button type="button" class="sawerna-filter__group-toggle" data-group-toggle="<?php echo esc_attr( $key ); ?>">
					<span><?php echo esc_html( $definition['label'] ); ?></span>
				</button>
				<div class="sawerna-filter__group-content">
					<?php if ( $is_range ) : ?>
						<?php
						$numeric_terms = ! is_wp_error( $terms ) && ! empty( $terms ) ? sawerna_shop_get_numeric_terms_data( $terms ) : array();
						if ( ! empty( $numeric_terms ) ) {
							$bounds = sawerna_shop_get_numeric_bounds( $numeric_terms );
							$selected_values = array();

							if ( ! empty( $selected ) ) {
								$selected_lookup = array_fill_keys( $selected, true );
								foreach ( $numeric_terms as $entry ) {
									if ( isset( $selected_lookup[ $entry['slug'] ] ) ) {
										$selected_values[] = (float) $entry['value'];
									}
								}
							}

							$current_min = ! empty( $selected_values ) ? min( $selected_values ) : $bounds['min'];
							$current_max = ! empty( $selected_values ) ? max( $selected_values ) : $bounds['max'];

							$min_bound = (float) $bounds['min'];
							$max_bound = (float) $bounds['max'];

							$current_min = max( $min_bound, min( $current_min, $max_bound ) );
							$current_max = max( $min_bound, min( $current_max, $max_bound ) );
							if ( $current_min > $current_max ) {
								$current_max = $current_min;
							}

							$step_value = sawerna_shop_get_numeric_step( $numeric_terms );
							$range_terms_payload = wp_json_encode(
								array_map(
									static function ( $entry ) {
										return array(
											'slug'  => $entry['slug'],
											'value' => $entry['value'],
										);
									},
									$numeric_terms
								)
							);
							?>
							<div
								class="sawerna-range-filter"
								data-range-filter="<?php echo esc_attr( $key ); ?>"
								data-range-terms="<?php echo esc_attr( $range_terms_payload ); ?>"
							>
								<div class="sawerna-range-filter__ranges">
									<input
										type="range"
										min="<?php echo esc_attr( sawerna_shop_format_range_value( $min_bound ) ); ?>"
										max="<?php echo esc_attr( sawerna_shop_format_range_value( $max_bound ) ); ?>"
										step="<?php echo esc_attr( sawerna_shop_format_range_value( $step_value ) ); ?>"
										value="<?php echo esc_attr( sawerna_shop_format_range_value( $current_min ) ); ?>"
										data-range-control="min"
									>
									<input
										type="range"
										min="<?php echo esc_attr( sawerna_shop_format_range_value( $min_bound ) ); ?>"
										max="<?php echo esc_attr( sawerna_shop_format_range_value( $max_bound ) ); ?>"
										step="<?php echo esc_attr( sawerna_shop_format_range_value( $step_value ) ); ?>"
										value="<?php echo esc_attr( sawerna_shop_format_range_value( $current_max ) ); ?>"
										data-range-control="max"
									>
								</div>
								<div class="sawerna-range-filter__values">
									<label>
										<span><?php esc_html_e( 'Alates', 'sawerna' ); ?></span>
										<input
											type="number"
											data-range-input="min"
											value="<?php echo esc_attr( sawerna_shop_format_range_value( $current_min ) ); ?>"
											min="<?php echo esc_attr( sawerna_shop_format_range_value( $min_bound ) ); ?>"
											max="<?php echo esc_attr( sawerna_shop_format_range_value( $max_bound ) ); ?>"
											step="<?php echo esc_attr( sawerna_shop_format_range_value( $step_value ) ); ?>"
										>
									</label>
									<label>
										<span><?php esc_html_e( 'Kuni', 'sawerna' ); ?></span>
										<input
											type="number"
											data-range-input="max"
											value="<?php echo esc_attr( sawerna_shop_format_range_value( $current_max ) ); ?>"
											min="<?php echo esc_attr( sawerna_shop_format_range_value( $min_bound ) ); ?>"
											max="<?php echo esc_attr( sawerna_shop_format_range_value( $max_bound ) ); ?>"
											step="<?php echo esc_attr( sawerna_shop_format_range_value( $step_value ) ); ?>"
										>
									</label>
								</div>
								<?php if ( ! empty( $selected ) ) : ?>
									<button type="button" class="sawerna-filter__section-clear" data-clear-filter="<?php echo esc_attr( $key ); ?>">
										<span><?php esc_html_e( 'Tühista filter', 'sawerna' ); ?></span>
										<span class="sawerna-filter__section-clear-icon" aria-hidden="true">
											<?php echo sawerna_get_svg_inline( 'Asset 20.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										</span>
									</button>
								<?php endif; ?>
							</div>
						<?php } else { ?>
							<p class="sawerna-filter__empty"><?php esc_html_e( 'Valikud puuduvad', 'sawerna' ); ?></p>
						<?php } ?>
					<?php else : ?>
						<?php if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) : ?>
							<ul class="sawerna-filter__options">
								<?php foreach ( $terms as $term ) : ?>
									<li>
										<label>
											<input type="checkbox" data-filter-key="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $term->slug ); ?>" <?php checked( in_array( $term->slug, $selected, true ) ); ?>>
											<span><?php echo esc_html( $term->name ); ?></span>
											<em>(<?php echo esc_html( (string) $term->count ); ?>)</em>
										</label>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php else : ?>
							<p class="sawerna-filter__empty"><?php esc_html_e( 'Valikud puuduvad', 'sawerna' ); ?></p>
						<?php endif; ?>

						<?php if ( ! empty( $selected ) ) : ?>
							<button type="button" class="sawerna-filter__section-clear" data-clear-filter="<?php echo esc_attr( $key ); ?>">
								<span><?php esc_html_e( 'Tühista filter', 'sawerna' ); ?></span>
								<span class="sawerna-filter__section-clear-icon" aria-hidden="true">
									<?php echo sawerna_get_svg_inline( 'Asset 20.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</span>
							</button>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>

		<div class="sawerna-filter__mobile-actions">
			<button type="button" class="sawerna-filter__mobile-secondary" data-mobile-clear-all="1">
				<?php esc_html_e( 'Tühista kõik', 'sawerna' ); ?>
			</button>
			<button type="button" class="sawerna-filter__mobile-primary" data-mobile-apply="1">
				<?php esc_html_e( 'Rakenda', 'sawerna' ); ?>
			</button>
		</div>
	</div>
	<?php

	return (string) ob_get_clean();
}

/**
 * Render custom pagination.
 *
 * @param WP_Query $query Query.
 * @param array<string,mixed> $state State.
 * @param array{taxonomy:string,term_id:int} $context Context.
 * @return string
 */
function sawerna_shop_render_pagination( WP_Query $query, array $state, array $context ) {
	if ( (int) $query->max_num_pages <= 1 ) {
		return '';
	}

	$base_url = sawerna_shop_get_context_url( $context );

	$links = paginate_links(
		array(
			'base'      => '%_%',
			'format'    => '',
			'current'   => max( 1, (int) $state['page'] ),
			'total'     => (int) $query->max_num_pages,
			'prev_text' => '&lsaquo;',
			'next_text' => '&rsaquo;',
			'mid_size'  => 2,
			'end_size'  => 1,
			'type'      => 'array',
		)
	);

	if ( empty( $links ) || ! is_array( $links ) ) {
		return '';
	}

	ob_start();
	?>
	<nav class="sawerna-shop-pagination" aria-label="<?php esc_attr_e( 'Toodete lehitsemine', 'sawerna' ); ?>">
		<ul>
			<?php foreach ( $links as $link ) : ?>
				<?php
				if ( false !== strpos( $link, 'prev page-numbers' ) ) {
					$target_page = max( 1, (int) $state['page'] - 1 );
					$url         = add_query_arg( sawerna_shop_state_to_query_args( array_merge( $state, array( 'page' => $target_page ) ) ), $base_url );
					$link        = '<a class="prev page-numbers" href="' . esc_url( $url ) . '" data-page="' . esc_attr( (string) $target_page ) . '">&lsaquo;</a>';
				} elseif ( false !== strpos( $link, 'next page-numbers' ) ) {
					$target_page = min( (int) $query->max_num_pages, (int) $state['page'] + 1 );
					$url         = add_query_arg( sawerna_shop_state_to_query_args( array_merge( $state, array( 'page' => $target_page ) ) ), $base_url );
					$link        = '<a class="next page-numbers" href="' . esc_url( $url ) . '" data-page="' . esc_attr( (string) $target_page ) . '">&rsaquo;</a>';
				} elseif ( preg_match( '/>(\d+)</', $link, $match ) ) {
					$target_page = (int) $match[1];
					$url         = add_query_arg( sawerna_shop_state_to_query_args( array_merge( $state, array( 'page' => $target_page ) ) ), $base_url );
					if ( false !== strpos( $link, 'current' ) ) {
						$link = '<span aria-current="page" class="page-numbers current">' . esc_html( (string) $target_page ) . '</span>';
					} else {
						$link = '<a class="page-numbers" href="' . esc_url( $url ) . '" data-page="' . esc_attr( (string) $target_page ) . '">' . esc_html( (string) $target_page ) . '</a>';
					}
				}
				?>
				<li><?php echo wp_kses_post( $link ); ?></li>
			<?php endforeach; ?>
		</ul>
	</nav>
	<?php

	return (string) ob_get_clean();
}

/**
 * Render products loop and pagination.
 *
 * @param WP_Query $query Query.
 * @param array<string,mixed> $state State.
 * @param array{taxonomy:string,term_id:int} $context Context.
 * @return string
 */
function sawerna_shop_render_products( WP_Query $query, array $state, array $context ) {
	ob_start();
	$GLOBALS['sawerna_shop_render_state'] = $state;

	if ( $query->have_posts() ) {
		wc_set_loop_prop( 'columns', (int) $state['columns'] );
		woocommerce_product_loop_start();

		while ( $query->have_posts() ) {
			$query->the_post();
			wc_get_template_part( 'content', 'product' );
		}

		woocommerce_product_loop_end();
		echo sawerna_shop_render_pagination( $query, $state, $context ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		wc_no_products_found();
	}

	wp_reset_postdata();
	unset( $GLOBALS['sawerna_shop_render_state'] );
	return (string) ob_get_clean();
}

/**
 * Build all dynamic HTML parts for state/context.
 *
 * @param array<string,mixed> $state State.
 * @param array{taxonomy:string,term_id:int} $context Context.
 * @return array<string,mixed>
 */
function sawerna_shop_get_dynamic_parts( array $state, array $context ) {
	$query = sawerna_shop_build_query( $state, $context );

	return array(
		'toolbar' => sawerna_shop_render_toolbar( $state, (int) $query->found_posts ),
		'active'  => sawerna_shop_render_active_filters( $state, $context ),
		'sidebar' => sawerna_shop_render_sidebar( $state, $context ),
		'products'=> sawerna_shop_render_products( $query, $state, $context ),
		'total'   => (int) $query->found_posts,
	);
}

/**
 * AJAX handler for product filters.
 *
 * @return void
 */
function sawerna_shop_ajax_filter_products() {
	check_ajax_referer( 'sawerna_shop_filters', 'nonce' );

	$context_source = isset( $_POST['context'] ) ? wp_unslash( $_POST['context'] ) : array();
	if ( is_string( $context_source ) ) {
		$decoded_context = json_decode( $context_source, true );
		$context_source  = is_array( $decoded_context ) ? $decoded_context : array();
	}
	$context = sawerna_shop_get_context_from_source( is_array( $context_source ) ? $context_source : array() );

	$source = isset( $_POST['state'] ) ? wp_unslash( $_POST['state'] ) : array();
	if ( is_string( $source ) ) {
		$decoded = json_decode( $source, true );
		$source  = is_array( $decoded ) ? $decoded : array();
	}
	$state = sawerna_shop_get_state( is_array( $source ) ? $source : array(), $context );

	$parts = sawerna_shop_get_dynamic_parts( $state, $context );
	$parts['url'] = add_query_arg( sawerna_shop_state_to_query_args( $state ), sawerna_shop_get_context_url( $context ) );

	wp_send_json_success( $parts );
}
add_action( 'wp_ajax_sawerna_shop_filter_products', 'sawerna_shop_ajax_filter_products' );
add_action( 'wp_ajax_nopriv_sawerna_shop_filter_products', 'sawerna_shop_ajax_filter_products' );

/**
 * Enqueue JS for custom shop filters.
 *
 * @return void
 */
function sawerna_shop_filter_scripts() {
	if ( ! ( is_shop() || is_product_taxonomy() ) ) {
		return;
	}

	$script_path = get_template_directory() . '/js/shop-filters.js';
	if ( ! file_exists( $script_path ) ) {
		return;
	}

	$version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $script_path ) : _S_VERSION;
	wp_enqueue_script( 'sawerna-shop-filters', get_template_directory_uri() . '/js/shop-filters.js', array(), $version, true );

	$context = sawerna_shop_get_archive_context();
	$state   = sawerna_shop_get_state( null, $context );

	wp_localize_script(
		'sawerna-shop-filters',
		'sawernaShopFilters',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'sawerna_shop_filters' ),
			'state'   => $state,
			'context' => $context,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'sawerna_shop_filter_scripts', 30 );
