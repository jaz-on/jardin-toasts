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

	var $phToggle = $( '#bj_use_placeholder_image' );
	var $phPicker = $( '#bj-placeholder-picker' );
	var $phId = $( '#bj_placeholder_image_id' );
	var $phPreview = $( '#bj-placeholder-preview' );
	var $phSelect = $( '#bj-placeholder-select' );
	var $phClear = $( '#bj-placeholder-clear' );

	function bjTogglePlaceholderPicker() {
		if ( ! $phToggle.length || ! $phPicker.length ) {
			return;
		}
		$phPicker.toggle( $phToggle.is( ':checked' ) );
	}

	if ( $phToggle.length ) {
		$phToggle.on( 'change', bjTogglePlaceholderPicker );
		bjTogglePlaceholderPicker();
	}

	if ( typeof wp !== 'undefined' && wp.media && $phSelect.length ) {
		var frame;
		$phSelect.on( 'click', function ( e ) {
			e.preventDefault();
			if ( frame ) {
				frame.open();
				return;
			}
			frame = wp.media( {
				title: bjAdmin.i18n.chooseImage,
				button: { text: bjAdmin.i18n.chooseImage },
				multiple: false,
				library: { type: 'image' },
			} );
			frame.on( 'select', function () {
				var att = frame.state().get( 'selection' ).first().toJSON();
				$phId.val( att.id );
				var url =
					att.sizes && att.sizes.thumbnail
						? att.sizes.thumbnail.url
						: att.url;
				$phPreview.html( '<img src="' + url + '" alt="" />' );
				$phSelect.text( bjAdmin.i18n.replaceImage );
			} );
			frame.open();
		} );
	}

	$phClear.on( 'click', function ( e ) {
		e.preventDefault();
		$phId.val( '0' );
		$phPreview.empty();
		$phSelect.text( bjAdmin.i18n.chooseImage );
	} );

	if ( bjAdmin.placeholderThumb && $phPreview.length && parseInt( bjAdmin.placeholderId, 10 ) > 0 ) {
		$phPreview.html(
			'<img src="' + bjAdmin.placeholderThumb + '" alt="" />'
		);
		$phSelect.text( bjAdmin.i18n.replaceImage );
	}

	$( '#bj-use-rss-username' ).on( 'click', function ( e ) {
		e.preventDefault();
		if ( bjAdmin.rssUsername ) {
			$( '#bj_untappd_username' ).val( bjAdmin.rssUsername );
		}
	} );
}( jQuery ) );
