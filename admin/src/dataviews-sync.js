/**
 * Admin spike: DataViews snapshot on Sync tab (built to build/admin-dataviews.js).
 */
import './dataviews-sync.scss';

import domReady from '@wordpress/dom-ready';
import { createRoot, useMemo, useState } from '@wordpress/element';
import { DataViews, filterSortAndPaginate } from '@wordpress/dataviews/wp';

const fields = [
	{
		id: 'label',
		label: 'Item',
		enableSorting: true,
	},
	{
		id: 'value',
		label: 'Value',
		enableSorting: false,
	},
];

const defaultLayouts = {
	table: {
		layout: {
			primaryField: 'label',
		},
	},
};

function SyncSnapshot( { rows } ) {
	const [ view, setView ] = useState( () => ( {
		type: 'table',
		perPage: 10,
		page: 1,
		layout: defaultLayouts.table.layout,
		fields: [ 'label', 'value' ],
	} ) );

	const { data: processedData, paginationInfo } = useMemo(
		() => filterSortAndPaginate( rows, view, fields ),
		[ rows, view, fields ]
	);

	return (
		<DataViews
			data={ processedData }
			fields={ fields }
			view={ view }
			onChangeView={ setView }
			defaultLayouts={ defaultLayouts }
			paginationInfo={ paginationInfo }
			actions={ [] }
		/>
	);
}

domReady( () => {
	const el = document.getElementById( 'jt-sync-dataviews-root' );
	if ( ! el || typeof window.jtDataviewsSync === 'undefined' ) {
		return;
	}
	const rows = Array.isArray( window.jtDataviewsSync.rows )
		? window.jtDataviewsSync.rows
		: [];
	createRoot( el ).render( <SyncSnapshot rows={ rows } /> );
} );
