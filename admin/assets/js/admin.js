( function ( $ ) {
	'use strict';

	function setStatus( el, msg ) {
		$( el ).text( msg );
	}

	$( document ).on( 'click', '#jt-sync-now', function () {
		var $btn = $( this );
		$btn.prop( 'disabled', true );
		setStatus( '#jt-sync-status', jtAdmin.i18n.working );
		$.post(
			jtAdmin.ajaxUrl,
			{
				action: jtAdmin.ajaxSyncNow,
				nonce: jtAdmin.nonce,
			}
		)
			.done( function ( res ) {
				if ( res.success ) {
					setStatus( '#jt-sync-status', res.data.message || jtAdmin.i18n.done );
				} else {
					setStatus( '#jt-sync-status', res.data && res.data.message ? res.data.message : 'Error' );
				}
			} )
			.fail( function () {
				setStatus( '#jt-sync-status', 'Request failed.' );
			} )
			.always( function () {
				$btn.prop( 'disabled', false );
			} );
	} );

	$( document ).on( 'click', '#jt-discover', function () {
		var $btn = $( this );
		var username = $( '#jt_untappd_username' ).val();
		var maxPages = $( '#jt-discover-max-pages' ).val() || 10;
		$btn.prop( 'disabled', true );
		setStatus( '#jt-import-status', jtAdmin.i18n.working );
		$.post(
			jtAdmin.ajaxUrl,
			{
				action: jtAdmin.ajaxCrawlDiscover,
				nonce: jtAdmin.nonce,
				username: username,
				max_pages: maxPages,
			}
		)
			.done( function ( res ) {
				if ( res.success ) {
					setStatus( '#jt-import-status', res.data.message );
				} else {
					setStatus( '#jt-import-status', res.data && res.data.message ? res.data.message : 'Error' );
				}
			} )
			.fail( function () {
				setStatus( '#jt-import-status', 'Request failed.' );
			} )
			.always( function () {
				$btn.prop( 'disabled', false );
			} );
	} );

	$( document ).on( 'click', '#jt-import-batch', function () {
		var $btn = $( this );
		$btn.prop( 'disabled', true );
		setStatus( '#jt-import-status', jtAdmin.i18n.working );
		$.post(
			jtAdmin.ajaxUrl,
			{
				action: jtAdmin.ajaxCrawlBatch,
				nonce: jtAdmin.nonce,
			}
		)
			.done( function ( res ) {
				if ( res.success ) {
					var d = res.data;
					setStatus(
						'#jt-import-status',
						'Imported ' +
							d.imported +
							'. Remaining: ' +
							d.remaining +
							'. Total: ' +
							d.total_imported
					);
					if ( d.done ) {
						setStatus( '#jt-import-status', 'Import complete.' );
					}
				} else {
					setStatus( '#jt-import-status', res.data && res.data.message ? res.data.message : 'Error' );
				}
			} )
			.fail( function () {
				setStatus( '#jt-import-status', 'Request failed.' );
			} )
			.always( function () {
				$btn.prop( 'disabled', false );
			} );
	} );

	var $phToggle = $( '#jt_use_placeholder_image' );
	var $phPicker = $( '#jt-placeholder-picker' );
	var $phId = $( '#jt_placeholder_image_id' );
	var $phPreview = $( '#jt-placeholder-preview' );
	var $phSelect = $( '#jt-placeholder-select' );
	var $phClear = $( '#jt-placeholder-clear' );

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
				title: jtAdmin.i18n.chooseImage,
				button: { text: jtAdmin.i18n.chooseImage },
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
				$phSelect.text( jtAdmin.i18n.replaceImage );
			} );
			frame.open();
		} );
	}

	$phClear.on( 'click', function ( e ) {
		e.preventDefault();
		$phId.val( '0' );
		$phPreview.empty();
		$phSelect.text( jtAdmin.i18n.chooseImage );
	} );

	if ( jtAdmin.placeholderThumb && $phPreview.length && parseInt( jtAdmin.placeholderId, 10 ) > 0 ) {
		$phPreview.html(
			'<img src="' + jtAdmin.placeholderThumb + '" alt="" />'
		);
		$phSelect.text( jtAdmin.i18n.replaceImage );
	}

	$( '#jt-use-rss-username' ).on( 'click', function ( e ) {
		e.preventDefault();
		if ( jtAdmin.rssUsername ) {
			$( '#jt_untappd_username' ).val( jtAdmin.rssUsername );
		}
	} );
}( jQuery ) );
