( function ( $ ) {
	'use strict';

	function setStatus( el, msg ) {
		$( el ).text( msg );
	}

	$( document ).on( 'click', '#bj-sync-now', function () {
		var $btn = $( this );
		$btn.prop( 'disabled', true );
		setStatus( '#bj-sync-status', bjAdmin.i18n.working );
		$.post(
			bjAdmin.ajaxUrl,
			{
				action: 'bj_sync_now',
				nonce: bjAdmin.nonce,
			}
		)
			.done( function ( res ) {
				if ( res.success ) {
					setStatus( '#bj-sync-status', res.data.message || bjAdmin.i18n.done );
				} else {
					setStatus( '#bj-sync-status', res.data && res.data.message ? res.data.message : 'Error' );
				}
			} )
			.fail( function () {
				setStatus( '#bj-sync-status', 'Request failed.' );
			} )
			.always( function () {
				$btn.prop( 'disabled', false );
			} );
	} );

	$( document ).on( 'click', '#bj-discover', function () {
		var $btn = $( this );
		var username = $( '#bj_untappd_username' ).val();
		var maxPages = $( '#bj-discover-max-pages' ).val() || 10;
		$btn.prop( 'disabled', true );
		setStatus( '#bj-import-status', bjAdmin.i18n.working );
		$.post(
			bjAdmin.ajaxUrl,
			{
				action: 'bj_crawl_discover',
				nonce: bjAdmin.nonce,
				username: username,
				max_pages: maxPages,
			}
		)
			.done( function ( res ) {
				if ( res.success ) {
					setStatus( '#bj-import-status', res.data.message );
				} else {
					setStatus( '#bj-import-status', res.data && res.data.message ? res.data.message : 'Error' );
				}
			} )
			.fail( function () {
				setStatus( '#bj-import-status', 'Request failed.' );
			} )
			.always( function () {
				$btn.prop( 'disabled', false );
			} );
	} );

	$( document ).on( 'click', '#bj-import-batch', function () {
		var $btn = $( this );
		$btn.prop( 'disabled', true );
		setStatus( '#bj-import-status', bjAdmin.i18n.working );
		$.post(
			bjAdmin.ajaxUrl,
			{
				action: 'bj_crawl_batch',
				nonce: bjAdmin.nonce,
			}
		)
			.done( function ( res ) {
				if ( res.success ) {
					var d = res.data;
					setStatus(
						'#bj-import-status',
						'Imported ' +
							d.imported +
							'. Remaining: ' +
							d.remaining +
							'. Total: ' +
							d.total_imported
					);
					if ( d.done ) {
						setStatus( '#bj-import-status', 'Import complete.' );
					}
				} else {
					setStatus( '#bj-import-status', res.data && res.data.message ? res.data.message : 'Error' );
				}
			} )
			.fail( function () {
				setStatus( '#bj-import-status', 'Request failed.' );
			} )
			.always( function () {
				$btn.prop( 'disabled', false );
			} );
	} );
}( jQuery ) );
