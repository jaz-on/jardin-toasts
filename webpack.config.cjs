/**
 * Extra entry for admin DataViews spike (Jardin Toasts settings → Sync tab).
 *
 * @type {import('webpack').Configuration|Function}
 */
const path = require( 'path' );
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

module.exports = config;
