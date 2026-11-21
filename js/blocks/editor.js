/**
 * Sawerna blocks: editor UI (no build step, plain ES2017 + wp.element.createElement).
 *
 * Block metadata (attributes, title, category) comes from
 * plugins/sawerna-core/blocks/<name>/block.json and is bootstrapped by core,
 * so registerBlockType() here only adds edit/save. All blocks are dynamic:
 * the front end and the preview render in PHP (includes/blocks.php).
 *
 * Data: only core REST endpoints (wp/v2/product, product_cat, posts,
 * categories, media), read with context=view, so core enforces permissions.
 */
( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.blocks || ! wp.element || ! wp.blockEditor ) {
		return;
	}

	const el = wp.element.createElement;
	const Fragment = wp.element.Fragment;
	const useState = wp.element.useState;
	const useEffect = wp.element.useEffect;
	const __ = wp.i18n.__;
	const sprintf = wp.i18n.sprintf;
	const apiFetch = wp.apiFetch;
	const addQueryArgs = wp.url.addQueryArgs;
	const decodeEntities = wp.htmlEntities.decodeEntities;

	const be = wp.blockEditor;
	const InspectorControls = be.InspectorControls;
	const BlockControls = be.BlockControls;
	const useBlockProps = be.useBlockProps;
	const MediaUpload = be.MediaUpload;
	const MediaUploadCheck = be.MediaUploadCheck;
	const URLInput = be.URLInput;

	const c = wp.components;
	const PanelBody = c.PanelBody;
	const SelectControl = c.SelectControl;
	const RangeControl = c.RangeControl;
	const ToggleControl = c.ToggleControl;
	const TextControl = c.TextControl;
	const TextareaControl = c.TextareaControl;
	const Button = c.Button;
	const Placeholder = c.Placeholder;
	const Spinner = c.Spinner;
	const SearchControl = c.SearchControl;
	const BaseControl = c.BaseControl;
	const ToolbarGroup = c.ToolbarGroup;
	const ToolbarButton = c.ToolbarButton;

	const SSR = wp.serverSideRender && wp.serverSideRender.ServerSideRender ? wp.serverSideRender.ServerSideRender : wp.serverSideRender;

	const TD = 'sawerna';

	/**
	 * Image-text position: new valign/align attributes, falling back to the legacy combined "position".
	 *
	 * @param {Object} a Block attributes.
	 * @return {{valign: string, align: string}} Position.
	 */
	function imageTextPos( a ) {
		const legacy = {
			'bottom-left': [ 'bottom', 'left' ],
			'bottom-center': [ 'bottom', 'center' ],
			center: [ 'center', 'center' ],
			'top-left': [ 'top', 'left' ],
		};
		const base = legacy[ a.position ] || [ 'bottom', 'left' ];
		return { valign: a.valign || base[ 0 ], align: a.align || base[ 1 ] };
	}

	/* Props that silence the 40px / margin deprecation notices in current core. */
	const NEXT = { __nextHasNoMarginBottom: true, __next40pxDefaultSize: true };

	function withNext( props ) {
		return Object.assign( {}, NEXT, props );
	}

	/* ------------------------------------------------------------------
	 * Shared helpers
	 * ------------------------------------------------------------------ */

	const labelCache = {};

	function recordLabel( type, record ) {
		if ( 'term' === type ) {
			return decodeEntities( record.name || '' );
		}
		return decodeEntities( ( record.title && record.title.rendered ) || '' ) || '#' + record.id;
	}

	function recordMeta( type, record ) {
		if ( 'term' === type ) {
			return record.slug + ( 'number' === typeof record.count ? ' · ' + record.count : '' );
		}
		return '#' + record.id;
	}

	function fieldsFor( type ) {
		return 'term' === type ? 'id,name,slug,count' : 'id,title';
	}

	/**
	 * Searchable picker backed by a core REST collection.
	 *
	 * @param {Object}   props
	 * @param {string}   props.label    Field label.
	 * @param {string}   props.path     REST collection path, e.g. /wp/v2/product.
	 * @param {string}   props.type     'post' or 'term'.
	 * @param {number[]} props.value    Selected IDs.
	 * @param {Function} props.onChange Receives the new ID list.
	 * @param {number}   props.max      Maximum selections (1 = single select).
	 * @param {boolean}  props.sortable Allow reordering.
	 * @param {string}   props.help     Help text.
	 */
	function EntityPicker( props ) {
		const value = Array.isArray( props.value ) ? props.value : [];
		const max = props.max || 20;
		const type = props.type;
		const [ search, setSearch ] = useState( '' );
		const [ results, setResults ] = useState( [] );
		const [ loading, setLoading ] = useState( false );
		const [ , setLabelsVersion ] = useState( 0 );

		// Labels for already selected IDs.
		useEffect( function () {
			const missing = value.filter( function ( id ) {
				return ! labelCache[ props.path + ':' + id ];
			} );
			if ( ! missing.length ) {
				return;
			}
			let active = true;
			apiFetch( {
				path: addQueryArgs( props.path, {
					include: missing.join( ',' ),
					per_page: 100,
					context: 'view',
					_fields: fieldsFor( type ),
				} ),
			} ).then( function ( records ) {
				( records || [] ).forEach( function ( r ) {
					labelCache[ props.path + ':' + r.id ] = { label: recordLabel( type, r ), meta: recordMeta( type, r ) };
				} );
				if ( active ) {
					setLabelsVersion( function ( v ) {
						return v + 1;
					} );
				}
			} ).catch( function () {} );
			return function () {
				active = false;
			};
		}, [ value.join( ',' ), props.path ] );

		// Search results (debounced).
		useEffect( function () {
			let active = true;
			const query = {
				per_page: 20,
				context: 'view',
				_fields: fieldsFor( type ),
			};
			if ( search ) {
				query.search = search;
			} else if ( 'term' === type ) {
				query.orderby = 'count';
				query.order = 'desc';
			}
			setLoading( true );
			const timer = setTimeout( function () {
				apiFetch( { path: addQueryArgs( props.path, query ) } ).then( function ( records ) {
					if ( ! active ) {
						return;
					}
					( records || [] ).forEach( function ( r ) {
						labelCache[ props.path + ':' + r.id ] = { label: recordLabel( type, r ), meta: recordMeta( type, r ) };
					} );
					setResults( records || [] );
					setLoading( false );
				} ).catch( function () {
					if ( active ) {
						setResults( [] );
						setLoading( false );
					}
				} );
			}, search ? 300 : 0 );
			return function () {
				active = false;
				clearTimeout( timer );
			};
		}, [ search, props.path ] );

		function add( id ) {
			if ( value.indexOf( id ) !== -1 ) {
				return;
			}
			if ( 1 === max ) {
				props.onChange( [ id ] );
				return;
			}
			if ( value.length >= max ) {
				return;
			}
			props.onChange( value.concat( [ id ] ) );
		}

		function remove( id ) {
			props.onChange( value.filter( function ( v ) {
				return v !== id;
			} ) );
		}

		function move( index, delta ) {
			const next = value.slice();
			const target = index + delta;
			if ( target < 0 || target >= next.length ) {
				return;
			}
			const tmp = next[ target ];
			next[ target ] = next[ index ];
			next[ index ] = tmp;
			props.onChange( next );
		}

		const selected = value.map( function ( id, index ) {
			const info = labelCache[ props.path + ':' + id ];
			return el(
				'li',
				{ key: id, className: 'sawerna-picker__item' },
				el(
					'span',
					{ className: 'sawerna-picker__label' },
					info ? info.label : '#' + id,
					info ? el( 'small', null, info.meta ) : null
				),
				props.sortable && value.length > 1 ? el( Button, { icon: 'arrow-up-alt2', size: 'small', label: __( 'Liiguta üles', TD ), disabled: 0 === index, onClick: function () {
					move( index, -1 );
				} } ) : null,
				props.sortable && value.length > 1 ? el( Button, { icon: 'arrow-down-alt2', size: 'small', label: __( 'Liiguta alla', TD ), disabled: index === value.length - 1, onClick: function () {
					move( index, 1 );
				} } ) : null,
				el( Button, { icon: 'no-alt', size: 'small', isDestructive: true, label: __( 'Eemalda', TD ), onClick: function () {
					remove( id );
				} } )
			);
		} );

		const options = results.filter( function ( r ) {
			return value.indexOf( r.id ) === -1;
		} ).map( function ( r ) {
			return el(
				'li',
				{ key: r.id },
				el(
					Button,
					{
						className: 'sawerna-picker__option',
						disabled: max > 1 && value.length >= max,
						onClick: function () {
							add( r.id );
						},
					},
					el( 'span', { className: 'sawerna-picker__label' }, recordLabel( type, r ), el( 'small', null, recordMeta( type, r ) ) )
				)
			);
		} );

		return el(
			BaseControl,
			{ label: props.label, help: props.help, __nextHasNoMarginBottom: true, className: 'sawerna-picker' },
			value.length ? el( 'ul', { className: 'sawerna-picker__selected' }, selected ) : el( 'p', { className: 'sawerna-picker__empty' }, __( 'Midagi pole valitud.', TD ) ),
			el( SearchControl, {
				value: search,
				onChange: setSearch,
				placeholder: __( 'Otsi…', TD ),
				__nextHasNoMarginBottom: true,
			} ),
			loading ? el( Spinner ) : null,
			! loading && ! options.length ? el( 'p', { className: 'sawerna-picker__empty' }, __( 'Tulemusi ei leitud.', TD ) ) : null,
			options.length ? el( 'ul', { className: 'sawerna-picker__results' }, options ) : null
		);
	}

	/**
	 * Resolve a media ID to a preview URL (context=view, core permissions).
	 *
	 * @param {number} id Attachment ID.
	 * @return {string} URL or ''.
	 */
	const mediaCache = {};
	function useMediaUrl( id ) {
		const [ url, setUrl ] = useState( id && mediaCache[ id ] ? mediaCache[ id ] : '' );
		useEffect( function () {
			if ( ! id ) {
				setUrl( '' );
				return;
			}
			if ( mediaCache[ id ] ) {
				setUrl( mediaCache[ id ] );
				return;
			}
			let active = true;
			apiFetch( { path: addQueryArgs( '/wp/v2/media/' + id, { context: 'view', _fields: 'id,source_url,media_details' } ) } ).then( function ( m ) {
				const sizes = m && m.media_details && m.media_details.sizes ? m.media_details.sizes : {};
				const src = ( sizes.medium && sizes.medium.source_url ) || ( m && m.source_url ) || '';
				mediaCache[ id ] = src;
				if ( active ) {
					setUrl( src );
				}
			} ).catch( function () {} );
			return function () {
				active = false;
			};
		}, [ id ] );
		return url;
	}

	function mediaPreviewUrl( media ) {
		if ( ! media ) {
			return '';
		}
		if ( media.sizes && media.sizes.medium ) {
			return media.sizes.medium.url;
		}
		return media.url || '';
	}

	function Preview( props ) {
		return el( SSR, {
			block: props.block,
			attributes: props.attributes,
			skipBlockSupportAttributes: true,
			EmptyResponsePlaceholder: function () {
				return el( Placeholder, { className: 'sawerna-block-placeholder', icon: props.icon, label: props.title, instructions: props.empty } );
			},
		} );
	}

	function Notice( props ) {
		return el( 'p', { className: 'sawerna-block-notice' }, props.children );
	}

	/* ------------------------------------------------------------------
	 * sawerna/products
	 * ------------------------------------------------------------------ */

	function ProductsEdit( props ) {
		const a = props.attributes;
		const set = props.setAttributes;
		const blockProps = useBlockProps( { className: 'sawerna-block-preview' } );
		const fixed = 'grid' === a.layout || 'featured' === a.layout;
		const hasColumns = 'compact' === a.layout || 'cards' === a.layout;
		const hasOrder = [ 'menu', 'category', 'on_sale' ].indexOf( a.source ) !== -1;

		const inspector = el(
			InspectorControls,
			null,
			el(
				PanelBody,
				{ title: __( 'Tooted', TD ), initialOpen: true },
				el( SelectControl, withNext( {
					label: __( 'Allikas', TD ),
					value: a.source,
					options: [
						{ value: 'menu', label: __( 'Menüü kategooria (automaatne)', TD ) },
						{ value: 'category', label: __( 'Valitud kategooriad', TD ) },
						{ value: 'ids', label: __( 'Valitud tooted', TD ) },
						{ value: 'on_sale', label: __( 'Soodustooted', TD ) },
						{ value: 'bestsellers', label: __( 'Enimmüüdud', TD ) },
						{ value: 'newest', label: __( 'Uusimad', TD ) },
					],
					help: 'menu' === a.source ? __( 'Näitab selle menüüpunkti tootekategooria tooteid, kuhu plokk on lisatud.', TD ) : undefined,
					onChange: function ( v ) {
						set( { source: v } );
					},
				} ) ),
				'category' === a.source ? el( EntityPicker, {
					label: __( 'Kategooriad', TD ),
					path: '/wp/v2/product_cat',
					type: 'term',
					value: a.categories,
					max: 20,
					onChange: function ( v ) {
						set( { categories: v } );
					},
				} ) : null,
				'ids' === a.source ? el( EntityPicker, {
					label: __( 'Tooted', TD ),
					path: '/wp/v2/product',
					type: 'post',
					value: a.ids,
					max: 12,
					sortable: true,
					help: __( 'Kuni 12 toodet, näidatakse valitud järjekorras.', TD ),
					onChange: function ( v ) {
						set( { ids: v } );
					},
				} ) : null
			),
			el(
				PanelBody,
				{ title: __( 'Paigutus', TD ), initialOpen: true },
				el( SelectControl, withNext( {
					label: __( 'Paigutus', TD ),
					value: a.layout,
					options: [
						{ value: 'compact', label: __( 'Kompaktne', TD ) },
						{ value: 'grid', label: __( 'Ruudustik 2x2', TD ) },
						{ value: 'list', label: __( 'Nimekiri', TD ) },
						{ value: 'featured', label: __( 'Esiletõstetud', TD ) },
						{ value: 'cards', label: __( 'Täiskaart (poe kaart)', TD ) },
					],
					help: 'grid' === a.layout ? __( 'Alati 4 toodet.', TD ) : ( 'featured' === a.layout ? __( 'Alati 1 toode.', TD ) : undefined ),
					onChange: function ( v ) {
						set( { layout: v } );
					},
				} ) ),
				! fixed && 'ids' !== a.source ? el( RangeControl, withNext( {
					label: __( 'Toodete arv', TD ),
					value: a.limit,
					min: 1,
					max: 12,
					onChange: function ( v ) {
						set( { limit: v || 1 } );
					},
				} ) ) : null,
				hasColumns ? el( RangeControl, withNext( {
					label: __( 'Veerge', TD ),
					value: a.columns,
					min: 1,
					max: 6,
					onChange: function ( v ) {
						set( { columns: v || 1 } );
					},
				} ) ) : null,
				'cards' !== a.layout ? el( ToggleControl, {
					label: __( 'Näita hinda', TD ),
					checked: !! a.showPrice,
					__nextHasNoMarginBottom: true,
					onChange: function ( v ) {
						set( { showPrice: v } );
					},
				} ) : null,
				'cards' !== a.layout ? el( ToggleControl, {
					label: __( 'Näita tarneaega', TD ),
					checked: !! a.showDelivery,
					__nextHasNoMarginBottom: true,
					onChange: function ( v ) {
						set( { showDelivery: v } );
					},
				} ) : null
			),
			hasOrder ? el(
				PanelBody,
				{ title: __( 'Järjestus', TD ), initialOpen: false },
				el( SelectControl, withNext( {
					label: __( 'Järjesta', TD ),
					value: a.orderby,
					options: [
						{ value: 'date', label: __( 'Lisamise kuupäev', TD ) },
						{ value: 'popularity', label: __( 'Populaarsus', TD ) },
						{ value: 'rating', label: __( 'Hinnang', TD ) },
						{ value: 'price', label: __( 'Hind', TD ) },
						{ value: 'title', label: __( 'Nimi', TD ) },
						{ value: 'menu_order', label: __( 'Käsitsi järjestus', TD ) },
						{ value: 'rand', label: __( 'Juhuslik', TD ) },
					],
					onChange: function ( v ) {
						set( { orderby: v } );
					},
				} ) ),
				el( SelectControl, withNext( {
					label: __( 'Suund', TD ),
					value: a.order,
					options: [
						{ value: 'DESC', label: __( 'Kahanev', TD ) },
						{ value: 'ASC', label: __( 'Kasvav', TD ) },
					],
					onChange: function ( v ) {
						set( { order: v } );
					},
				} ) )
			) : null
		);

		return el(
			Fragment,
			null,
			inspector,
			el(
				'div',
				blockProps,
				'menu' === a.source ? el( Notice, null, __( 'Eelvaade: menüüs näidatakse menüüpunkti kategooria tooteid', TD ) ) : null,
				el( Preview, {
					block: 'sawerna/products',
					attributes: a,
					icon: 'products',
					title: __( 'Tooted', TD ),
					empty: __( 'Valitud seadetega tooteid ei leitud. Muuda allikat või vali tooted külgpaneelist.', TD ),
				} )
			)
		);
	}

	/* ------------------------------------------------------------------
	 * sawerna/blog-posts
	 * ------------------------------------------------------------------ */

	function BlogEdit( props ) {
		const a = props.attributes;
		const set = props.setAttributes;
		const blockProps = useBlockProps( { className: 'sawerna-block-preview' } );
		const single = 'single' === a.layout;
		const pickOne = 'id' === a.source || ( single && 'ids' === a.source );
		const pickMany = ! single && 'ids' === a.source;

		const inspector = el(
			InspectorControls,
			null,
			el(
				PanelBody,
				{ title: __( 'Postitused', TD ), initialOpen: true },
				el( SelectControl, withNext( {
					label: __( 'Paigutus', TD ),
					value: a.layout || 'grid',
					options: [
						{ value: 'grid', label: __( 'Ruudustik', TD ) },
						{ value: 'single', label: __( 'Üksik postitus', TD ) },
					],
					onChange: function ( v ) {
						const patch = { layout: v };
						if ( 'single' === v && 'ids' === a.source ) {
							patch.source = 'id';
							patch.postId = a.postId || ( a.ids && a.ids.length ? a.ids[ 0 ] : 0 );
						} else if ( 'grid' === v && 'id' === a.source ) {
							patch.source = 'ids';
							patch.ids = a.postId ? [ a.postId ] : a.ids;
						}
						set( patch );
					},
				} ) ),
				el( SelectControl, withNext( {
					label: __( 'Allikas', TD ),
					value: single && 'ids' === a.source ? 'id' : a.source,
					options: single ? [
						{ value: 'latest', label: __( 'Viimane postitus', TD ) },
						{ value: 'category', label: __( 'Viimane kategooriast', TD ) },
						{ value: 'id', label: __( 'Valitud postitus', TD ) },
					] : [
						{ value: 'latest', label: __( 'Viimased postitused', TD ) },
						{ value: 'category', label: __( 'Kategooria', TD ) },
						{ value: 'ids', label: __( 'Valitud postitused', TD ) },
					],
					onChange: function ( v ) {
						set( { source: v } );
					},
				} ) ),
				pickOne ? el( EntityPicker, {
					label: __( 'Postitus', TD ),
					path: '/wp/v2/posts',
					type: 'post',
					value: a.postId ? [ a.postId ] : [],
					max: 1,
					onChange: function ( v ) {
						set( { postId: v.length ? v[ 0 ] : 0 } );
					},
				} ) : null,
				'category' === a.source ? el( EntityPicker, {
					label: __( 'Kategooria', TD ),
					path: '/wp/v2/categories',
					type: 'term',
					value: a.category ? [ a.category ] : [],
					max: 1,
					onChange: function ( v ) {
						set( { category: v.length ? v[ 0 ] : 0 } );
					},
				} ) : null,
				pickMany ? el( EntityPicker, {
					label: __( 'Postitused', TD ),
					path: '/wp/v2/posts',
					type: 'post',
					value: a.ids,
					max: 12,
					sortable: true,
					onChange: function ( v ) {
						set( { ids: v } );
					},
				} ) : null,
				! single && 'ids' !== a.source ? el( RangeControl, withNext( {
					label: __( 'Postituste arv', TD ),
					value: a.limit,
					min: 1,
					max: 12,
					onChange: function ( v ) {
						set( { limit: v || 1 } );
					},
				} ) ) : null,
				! single ? el( RangeControl, withNext( {
					label: __( 'Veerge', TD ),
					value: a.columns,
					min: 1,
					max: 4,
					onChange: function ( v ) {
						set( { columns: v || 1 } );
					},
				} ) ) : null,
				el( ToggleControl, {
					label: __( 'Näita lühikirjeldust', TD ),
					checked: !! a.showExcerpt,
					__nextHasNoMarginBottom: true,
					onChange: function ( v ) {
						set( { showExcerpt: v } );
					},
				} ),
				el( TextControl, withNext( {
					label: __( 'Lingi tekst', TD ),
					value: a.readMore,
					placeholder: __( 'Loe lähemalt', TD ),
					onChange: function ( v ) {
						set( { readMore: v } );
					},
				} ) )
			)
		);

		return el(
			Fragment,
			null,
			inspector,
			el(
				'div',
				blockProps,
				el( Preview, {
					block: 'sawerna/blog-posts',
					attributes: a,
					icon: 'admin-post',
					title: __( 'Blogipostitused', TD ),
					empty: __( 'Postitusi ei leitud. Muuda allikat külgpaneelist.', TD ),
				} )
			)
		);
	}

	/* ------------------------------------------------------------------
	 * sawerna/banner
	 * ------------------------------------------------------------------ */

	function ImagePicker( props ) {
		const url = useMediaUrl( props.value );
		return el(
			MediaUploadCheck,
			null,
			el( MediaUpload, {
				allowedTypes: [ 'image' ],
				value: props.value || undefined,
				onSelect: function ( media ) {
					if ( media && media.id ) {
						mediaCache[ media.id ] = mediaPreviewUrl( media );
						props.onChange( media.id );
					}
				},
				render: function ( obj ) {
					return el(
						'div',
						{ className: 'sawerna-image-picker' },
						url ? el( 'img', { src: url, alt: '', className: 'sawerna-image-picker__thumb' } ) : null,
						el( Button, { variant: props.value ? 'secondary' : 'primary', onClick: obj.open, __next40pxDefaultSize: true }, props.value ? __( 'Vaheta pilt', TD ) : __( 'Vali pilt', TD ) ),
						props.value && props.onRemove ? el( Button, { variant: 'tertiary', isDestructive: true, onClick: props.onRemove, __next40pxDefaultSize: true }, __( 'Eemalda', TD ) ) : null
					);
				},
			} )
		);
	}

	function BannerEdit( props ) {
		const a = props.attributes;
		const set = props.setAttributes;
		const blockProps = useBlockProps( { className: 'sawerna-block-preview' } );
		const hasText = !! ( a.title || a.text || a.buttonText );

		const inspector = el(
			InspectorControls,
			null,
			el(
				PanelBody,
				{ title: __( 'Pilt', TD ), initialOpen: true },
				el( ImagePicker, {
					value: a.imageId,
					onChange: function ( id ) {
						set( { imageId: id } );
					},
					onRemove: function () {
						set( { imageId: 0 } );
					},
				} ),
				el( RangeControl, withNext( {
					label: __( 'Kõrgus (px)', TD ),
					value: a.height,
					min: 120,
					max: 800,
					step: 10,
					onChange: function ( v ) {
						set( { height: v || 360 } );
					},
				} ) )
			),
			el(
				PanelBody,
				{ title: __( 'Sisu', TD ), initialOpen: true },
				el( TextControl, withNext( {
					label: __( 'Pealkiri', TD ),
					value: a.title,
					onChange: function ( v ) {
						set( { title: v } );
					},
				} ) ),
				el( TextareaControl, {
					label: __( 'Tekst', TD ),
					value: a.text,
					rows: 3,
					__nextHasNoMarginBottom: true,
					onChange: function ( v ) {
						set( { text: v } );
					},
				} ),
				el( TextControl, withNext( {
					label: __( 'Nupu tekst', TD ),
					value: a.buttonText,
					onChange: function ( v ) {
						set( { buttonText: v } );
					},
				} ) ),
				el(
					BaseControl,
					{ label: __( 'Link', TD ), help: __( 'Otsi lehte/toodet või kleebi aadress.', TD ), __nextHasNoMarginBottom: true },
					el( URLInput, {
						value: a.url,
						onChange: function ( v ) {
							set( { url: v } );
						},
					} )
				),
				el( SelectControl, withNext( {
					label: __( 'Joondus', TD ),
					value: a.align,
					options: [
						{ value: 'left', label: __( 'Vasakul', TD ) },
						{ value: 'center', label: __( 'Keskel', TD ) },
						{ value: 'right', label: __( 'Paremal', TD ) },
					],
					onChange: function ( v ) {
						set( { align: v } );
					},
				} ) ),
				el( SelectControl, withNext( {
					label: __( 'Vertikaalne asend', TD ),
					value: a.valign || 'bottom',
					options: [
						{ value: 'top', label: __( 'Üleval', TD ) },
						{ value: 'center', label: __( 'Keskel', TD ) },
						{ value: 'bottom', label: __( 'All', TD ) },
					],
					onChange: function ( v ) {
						set( { valign: v } );
					},
				} ) ),
				el( SelectControl, withNext( {
					label: __( 'Nupu stiil', TD ),
					value: a.darkButton && 'primary' === a.buttonStyle ? 'light' : ( a.buttonStyle || 'primary' ),
					options: [
						{ value: 'primary', label: __( 'Põhinupp', TD ) },
						{ value: 'light', label: __( 'Hele', TD ) },
						{ value: 'outline', label: __( 'Kontuur', TD ) },
					],
					onChange: function ( v ) {
						set( { buttonStyle: v, darkButton: false } );
					},
				} ) ),
				el( RangeControl, withNext( {
					label: __( 'Tausta tumedus (%)', TD ),
					value: 'number' === typeof a.overlay ? a.overlay : 35,
					min: 0,
					max: 80,
					step: 5,
					onChange: function ( v ) {
						set( { overlay: 'number' === typeof v ? v : 35 } );
					},
				} ) )
			)
		);

		const toolbar = a.imageId ? el(
			BlockControls,
			{ group: 'other' },
			el(
				MediaUploadCheck,
				null,
				el( MediaUpload, {
					allowedTypes: [ 'image' ],
					value: a.imageId,
					onSelect: function ( media ) {
						if ( media && media.id ) {
							set( { imageId: media.id } );
						}
					},
					render: function ( obj ) {
						return el( ToolbarGroup, null, el( ToolbarButton, { onClick: obj.open }, __( 'Vaheta pilt', TD ) ) );
					},
				} )
			)
		) : null;

		let body;
		if ( ! a.imageId ) {
			body = el(
				Placeholder,
				{ className: 'sawerna-block-placeholder', icon: 'format-image', label: __( 'Bänner', TD ), instructions: __( 'Vali bänneri pilt. Pealkirja, teksti ja lingi saad lisada külgpaneelis.', TD ) },
				el( ImagePicker, {
					value: 0,
					onChange: function ( id ) {
						set( { imageId: id } );
					},
				} )
			);
		} else if ( ! hasText ) {
			body = el( Placeholder, { className: 'sawerna-block-placeholder', icon: 'format-image', label: __( 'Bänner', TD ), instructions: __( 'Lisa külgpaneelis pealkiri, tekst või nupu tekst, muidu bännerit ei näidata.', TD ) } );
		} else {
			body = el( Preview, {
				block: 'sawerna/banner',
				attributes: a,
				icon: 'format-image',
				title: __( 'Bänner', TD ),
				empty: __( 'Bännerit ei saa kuvada. Kontrolli pilti.', TD ),
			} );
		}

		return el( Fragment, null, inspector, toolbar, el( 'div', blockProps, body ) );
	}

	/* ------------------------------------------------------------------
	 * sawerna/image-text
	 * ------------------------------------------------------------------ */

	function ImageTextEdit( props ) {
		const a = props.attributes;
		const set = props.setAttributes;
		const blockProps = useBlockProps( { className: 'sawerna-block-preview' } );

		const inspector = el(
			InspectorControls,
			null,
			el(
				PanelBody,
				{ title: __( 'Pilt', TD ), initialOpen: true },
				el( ImagePicker, {
					value: a.imageId,
					onChange: function ( id ) {
						set( { imageId: id } );
					},
					onRemove: function () {
						set( { imageId: 0 } );
					},
				} ),
				el( SelectControl, withNext( {
					label: __( 'Kuvasuhe', TD ),
					value: a.ratio,
					options: [
						{ value: '4/5', label: __( 'Püstine 4:5', TD ) },
						{ value: '1/1', label: __( 'Ruut 1:1', TD ) },
						{ value: '3/2', label: '3:2' },
						{ value: '16/9', label: '16:9' },
					],
					onChange: function ( v ) {
						set( { ratio: v } );
					},
				} ) ),
				el( RangeControl, withNext( {
					label: __( 'Tausta tumedus (%)', TD ),
					value: 'number' === typeof a.overlay ? a.overlay : 25,
					min: 0,
					max: 80,
					step: 5,
					onChange: function ( v ) {
						set( { overlay: 'number' === typeof v ? v : 25 } );
					},
				} ) )
			),
			el(
				PanelBody,
				{ title: __( 'Sisu', TD ), initialOpen: true },
				el( TextControl, withNext( {
					label: __( 'Pealkiri', TD ),
					value: a.title,
					onChange: function ( v ) {
						set( { title: v } );
					},
				} ) ),
				el( TextareaControl, {
					label: __( 'Tekst', TD ),
					value: a.text,
					rows: 3,
					__nextHasNoMarginBottom: true,
					onChange: function ( v ) {
						set( { text: v } );
					},
				} ),
				el(
					BaseControl,
					{ label: __( 'Link', TD ), help: __( 'Otsi lehte/toodet või kleebi aadress.', TD ), __nextHasNoMarginBottom: true },
					el( URLInput, {
						value: a.url,
						onChange: function ( v ) {
							set( { url: v } );
						},
					} )
				),
				el( SelectControl, withNext( {
					label: __( 'Teksti asend (vertikaalne)', TD ),
					value: imageTextPos( a ).valign,
					options: [
						{ value: 'top', label: __( 'Üleval', TD ) },
						{ value: 'center', label: __( 'Keskel', TD ) },
						{ value: 'bottom', label: __( 'All', TD ) },
					],
					onChange: function ( v ) {
						set( { valign: v, align: imageTextPos( a ).align } );
					},
				} ) ),
				el( SelectControl, withNext( {
					label: __( 'Teksti asend (horisontaalne)', TD ),
					value: imageTextPos( a ).align,
					options: [
						{ value: 'left', label: __( 'Vasakul', TD ) },
						{ value: 'center', label: __( 'Keskel', TD ) },
						{ value: 'right', label: __( 'Paremal', TD ) },
					],
					onChange: function ( v ) {
						set( { align: v, valign: imageTextPos( a ).valign } );
					},
				} ) )
			)
		);

		const body = a.imageId ? el( Preview, {
			block: 'sawerna/image-text',
			attributes: a,
			icon: 'cover-image',
			title: __( 'Pilt tekstiga', TD ),
			empty: __( 'Pilti ei saa kuvada. Vali pilt uuesti.', TD ),
		} ) : el(
			Placeholder,
			{ className: 'sawerna-block-placeholder', icon: 'cover-image', label: __( 'Pilt tekstiga', TD ), instructions: __( 'Vali pilt. Pealkirja, teksti ja lingi saad lisada külgpaneelis.', TD ) },
			el( ImagePicker, {
				value: 0,
				onChange: function ( id ) {
					set( { imageId: id } );
				},
			} )
		);

		return el( Fragment, null, inspector, el( 'div', blockProps, body ) );
	}

	/* ------------------------------------------------------------------
	 * sawerna/image-grid
	 * ------------------------------------------------------------------ */

	const MAX_GRID_ITEMS = 12;

	function GridItemControls( props ) {
		const item = props.item;
		const url = useMediaUrl( item.id );
		return el(
			'div',
			{ className: 'sawerna-grid-item' },
			el(
				'div',
				{ className: 'sawerna-grid-item__head' },
				url ? el( 'img', { src: url, alt: '', className: 'sawerna-grid-item__thumb' } ) : el( 'span', { className: 'sawerna-grid-item__thumb' } ),
				el( 'strong', null, sprintf( __( 'Pilt %d', TD ), props.index + 1 ) ),
				el( Button, { icon: 'arrow-up-alt2', size: 'small', label: __( 'Liiguta üles', TD ), disabled: 0 === props.index, onClick: function () {
					props.onMove( -1 );
				} } ),
				el( Button, { icon: 'arrow-down-alt2', size: 'small', label: __( 'Liiguta alla', TD ), disabled: props.isLast, onClick: function () {
					props.onMove( 1 );
				} } ),
				el( Button, { icon: 'trash', size: 'small', isDestructive: true, label: __( 'Eemalda', TD ), onClick: props.onRemove } )
			),
			el(
				MediaUploadCheck,
				null,
				el( MediaUpload, {
					allowedTypes: [ 'image' ],
					value: item.id,
					onSelect: function ( media ) {
						if ( media && media.id ) {
							mediaCache[ media.id ] = mediaPreviewUrl( media );
							props.onChange( { id: media.id } );
						}
					},
					render: function ( obj ) {
						return el( Button, { variant: 'secondary', size: 'small', onClick: obj.open }, __( 'Vaheta pilt', TD ) );
					},
				} )
			),
			el( TextControl, withNext( {
				label: __( 'Pealdis', TD ),
				value: item.caption || '',
				onChange: function ( v ) {
					props.onChange( { caption: v } );
				},
			} ) ),
			el(
				BaseControl,
				{ label: __( 'Link', TD ), __nextHasNoMarginBottom: true },
				el( URLInput, {
					value: item.link || '',
					onChange: function ( v ) {
						props.onChange( { link: v } );
					},
				} )
			)
		);
	}

	function ImageGridEdit( props ) {
		const a = props.attributes;
		const set = props.setAttributes;
		const items = Array.isArray( a.items ) ? a.items : [];
		const blockProps = useBlockProps( { className: 'sawerna-block-preview' } );

		function addMedia( media ) {
			const list = Array.isArray( media ) ? media : [ media ];
			const next = items.slice();
			list.forEach( function ( m ) {
				if ( m && m.id && next.length < MAX_GRID_ITEMS ) {
					mediaCache[ m.id ] = mediaPreviewUrl( m );
					next.push( { id: m.id, link: '', caption: m.caption ? String( m.caption ).replace( /<[^>]*>/g, '' ) : '' } );
				}
			} );
			set( { items: next } );
		}

		function update( index, patch ) {
			const next = items.slice();
			next[ index ] = Object.assign( {}, next[ index ], patch );
			set( { items: next } );
		}

		function remove( index ) {
			const next = items.slice();
			next.splice( index, 1 );
			set( { items: next } );
		}

		function move( index, delta ) {
			const target = index + delta;
			if ( target < 0 || target >= items.length ) {
				return;
			}
			const next = items.slice();
			const tmp = next[ target ];
			next[ target ] = next[ index ];
			next[ index ] = tmp;
			set( { items: next } );
		}

		function addButton( variant ) {
			return el(
				MediaUploadCheck,
				null,
				el( MediaUpload, {
					allowedTypes: [ 'image' ],
					multiple: true,
					onSelect: addMedia,
					render: function ( obj ) {
						return el( Button, { variant: variant, onClick: obj.open, disabled: items.length >= MAX_GRID_ITEMS, __next40pxDefaultSize: true }, items.length ? __( 'Lisa pilte', TD ) : __( 'Vali pildid', TD ) );
					},
				} )
			);
		}

		const inspector = el(
			InspectorControls,
			null,
			el(
				PanelBody,
				{ title: __( 'Ruudustik', TD ), initialOpen: true },
				el( RangeControl, withNext( {
					label: __( 'Veerge', TD ),
					value: a.columns,
					min: 1,
					max: 4,
					onChange: function ( v ) {
						set( { columns: v || 1 } );
					},
				} ) ),
				el( SelectControl, withNext( {
					label: __( 'Pildi kuvasuhe', TD ),
					value: a.ratio,
					options: [
						{ value: '1/1', label: __( 'Ruut (1:1)', TD ) },
						{ value: '4/3', label: '4:3' },
						{ value: '3/2', label: '3:2' },
						{ value: '16/9', label: '16:9' },
						{ value: '3/4', label: __( 'Püstine 3:4', TD ) },
						{ value: '2/3', label: __( 'Püstine 2:3', TD ) },
					],
					onChange: function ( v ) {
						set( { ratio: v } );
					},
				} ) ),
				el( RangeControl, withNext( {
					label: __( 'Vahe (px)', TD ),
					value: a.gap,
					min: 0,
					max: 48,
					onChange: function ( v ) {
						set( { gap: 'number' === typeof v ? v : 12 } );
					},
				} ) )
			),
			el(
				PanelBody,
				{ title: sprintf( __( 'Pildid (%1$d/%2$d)', TD ), items.length, MAX_GRID_ITEMS ), initialOpen: true },
				items.map( function ( item, index ) {
					return el( GridItemControls, {
						key: index + '-' + item.id,
						item: item,
						index: index,
						isLast: index === items.length - 1,
						onChange: function ( patch ) {
							update( index, patch );
						},
						onRemove: function () {
							remove( index );
						},
						onMove: function ( delta ) {
							move( index, delta );
						},
					} );
				} ),
				addButton( 'secondary' )
			)
		);

		const body = items.length ? el( Preview, {
			block: 'sawerna/image-grid',
			attributes: a,
			icon: 'grid-view',
			title: __( 'Pildiruudustik', TD ),
			empty: __( 'Pilte ei saa kuvada. Vali pildid uuesti.', TD ),
		} ) : el(
			Placeholder,
			{ className: 'sawerna-block-placeholder', icon: 'grid-view', label: __( 'Pildiruudustik', TD ), instructions: __( 'Vali pildid. Lingid ja pealdised saad lisada külgpaneelis.', TD ) },
			addButton( 'primary' )
		);

		return el( Fragment, null, inspector, el( 'div', blockProps, body ) );
	}

	/* ------------------------------------------------------------------
	 * Registration (metadata comes from block.json via the server)
	 * ------------------------------------------------------------------ */

	const edits = {
		'sawerna/products': ProductsEdit,
		'sawerna/blog-posts': BlogEdit,
		'sawerna/banner': BannerEdit,
		'sawerna/image-grid': ImageGridEdit,
		'sawerna/image-text': ImageTextEdit,
	};

	Object.keys( edits ).forEach( function ( name ) {
		if ( wp.blocks.getBlockType( name ) && wp.blocks.getBlockType( name ).edit ) {
			return;
		}
		wp.blocks.registerBlockType( name, {
			edit: edits[ name ],
			save: function () {
				return null;
			},
		} );
	} );

	/* Own inserter item for the 2x2 product grid (same block, layout preset). */
	if ( wp.blocks.registerBlockVariation && wp.blocks.getBlockType( 'sawerna/products' ) ) {
		wp.blocks.registerBlockVariation( 'sawerna/products', {
			name: 'products-grid-2x2',
			title: __( 'Tooted 2x2', TD ),
			description: __( 'Neli toodet 2x2 ruudustikus.', TD ),
			icon: 'grid-view',
			category: 'sawerna',
			keywords: [ 'tooted', '2x2', 'ruudustik', 'grid' ],
			attributes: { layout: 'grid' },
			isDefault: false,
			scope: [ 'inserter' ],
			isActive: [ 'layout' ],
		} );
	}
}( window.wp ) );
