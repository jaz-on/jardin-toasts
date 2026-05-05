/**
 * Extra entry for admin DataViews spike (Jardin Toasts settings → Sync tab).
 *
 * @type {import('webpack').Configuration|Function}
 */
const path = require( 'path' );
const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );
const config = require( '@wordpress/scripts/config/webpack.config' );

const extra = path.resolve( __dirname, 'admin', 'src', 'dataviews-sync.js' );

if ( typeof config.entry === 'function' ) {
	const orig = config.entry;
	config.entry = ( env ) => {
		const base = orig( env );
		if ( base && typeof base === 'object' && ! Array.isArray( base ) ) {
			return { ...base, 'admin-dataviews': extra };
		}
		return { 'admin-dataviews': extra };
	};
} else if ( config.entry && typeof config.entry === 'object' && ! Array.isArray( config.entry ) ) {
	config.entry = { ...config.entry, 'admin-dataviews': extra };
} else {
	config.entry = { 'admin-dataviews': extra };
}

/*
 * Replace the default DependencyExtractionWebpackPlugin so we can map
 * `@wordpress/dataviews/wp` → script handle `wp-dataviews` (WP only registers
 * `wp-dataviews`, not `wp-dataviews/wp`; the default plugin produces an invalid
 * handle that triggers a `WP_Scripts::add was called incorrectly` notice).
 */
config.plugins = ( config.plugins || [] ).filter(
	( plugin ) => ! ( plugin instanceof DependencyExtractionWebpackPlugin )
);
config.plugins.push(
	new DependencyExtractionWebpackPlugin( {
		injectPolyfill: true,
		requestToHandle( request ) {
			if ( '@wordpress/dataviews/wp' === request ) {
				return 'wp-dataviews';
			}
			return undefined; // Fall back to default mapping.
		},
	} )
);

module.exports = config;
