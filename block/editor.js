/**
 * Editor UI for the CTA block.
 *
 * Plain JS (no build step): uses the wp.* globals WordPress already loads.
 */
( function ( wp ) {
	const { registerBlockType } = wp.blocks;
	const { createElement: el } = wp.element;
	const { useSelect } = wp.data;
	const { useBlockProps } = wp.blockEditor;
	const { SelectControl, Placeholder, Spinner } = wp.components;
	const ServerSideRender = wp.serverSideRender;
	const { __ } = wp.i18n;

	registerBlockType( 'campaign-ctas/cta', {
		edit( { attributes, setAttributes } ) {
			const blockProps = useBlockProps();

			// Fetch published CTAs over the REST API.
			const ctas = useSelect(
				( select ) =>
					select( 'core' ).getEntityRecords( 'postType', 'ccta_cta', {
						per_page: -1,
						status: 'publish',
						orderby: 'title',
						order: 'asc',
					} ),
				[]
			);

			if ( ! ctas ) {
				return el( 'div', blockProps, el( Spinner ) );
			}

			const picker = el( SelectControl, {
				label: __( 'Choose a CTA', 'campaign-ctas' ),
				value: attributes.ctaId,
				options: [
					{ label: __( '— Select —', 'campaign-ctas' ), value: 0 },
					...ctas.map( ( cta ) => ( {
						label: cta.title.rendered || __( '(no title)', 'campaign-ctas' ),
						value: cta.id,
					} ) ),
				],
				onChange: ( value ) => setAttributes( { ctaId: parseInt( value, 10 ) } ),
			} );

			// Before a CTA is chosen, show just the picker.
			if ( ! attributes.ctaId ) {
				return el( 'div', blockProps, el( Placeholder, { icon: 'megaphone', label: __( 'CTA', 'campaign-ctas' ) }, picker ) );
			}

			// Afterwards, show the picker above a live preview rendered by PHP.
			return el(
				'div',
				blockProps,
				picker,
				el( ServerSideRender, { block: 'campaign-ctas/cta', attributes } )
			);
		},

		// Dynamic block: PHP renders it, so nothing is saved in the post's HTML.
		save: () => null,
	} );
} )( window.wp );
