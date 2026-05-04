( function ( $ ) {
	'use strict';

	function setStatus( el, msg ) {
		$( el ).text( msg );
	}

	/**
	 * WordPress admin-ajax may return JSON with a message on HTTP error responses.
	 *
	 * @param {JQuery.jqXHR} xhr
	 * @param {string} fallback
	 * @return {string}
	 */
	function ajaxFailMessage( xhr, fallback ) {
		var d;
		if ( xhr && xhr.responseJSON ) {
			d = xhr.responseJSON.data;
			if ( typeof d === 'string' ) {
				return d;
			}
			if ( d && d.message ) {
				return d.message;
			}
		}
		return fallback || 'Request failed.';
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
			.fail( function ( xhr ) {
				setStatus( '#jt-sync-status', ajaxFailMessage( xhr, 'Request failed.' ) );
			} )
			.always( function () {
				$btn.prop( 'disabled', false );
			} );
	} );

	$( document ).on( 'click', '#jt-import-gdpr-csv', function () {
		var $btn = $( this );
		var input = document.getElementById( 'jt-gdpr-csv-file' );
		if ( ! input || ! input.files || ! input.files.length ) {
			setStatus(
				'#jt-gdpr-csv-status',
				jtAdmin.i18n.importGdprPick || 'Choose a CSV file first.'
			);
			return;
		}
		var fd = new FormData();
		fd.append( 'action', jtAdmin.ajaxImportGdprCsv );
		fd.append( 'nonce', jtAdmin.nonce );
		fd.append( 'jt_gdpr_csv', input.files[ 0 ] );
		$btn.prop( 'disabled', true );
		setStatus(
			'#jt-gdpr-csv-status',
			jtAdmin.i18n.importGdprWorking || 'Importing…'
		);
		$.ajax( {
			url: jtAdmin.ajaxUrl,
			type: 'POST',
			data: fd,
			processData: false,
			contentType: false,
		} )
			.done( function ( res ) {
				if ( res.success ) {
					setStatus(
						'#jt-gdpr-csv-status',
						res.data.message || jtAdmin.i18n.done
					);
				} else {
					setStatus(
						'#jt-gdpr-csv-status',
						res.data && res.data.message ? res.data.message : 'Error'
					);
				}
			} )
			.fail( function ( xhr ) {
				setStatus(
					'#jt-gdpr-csv-status',
					ajaxFailMessage( xhr, 'Request failed.' )
				);
			} )
			.always( function () {
				$btn.prop( 'disabled', false );
			} );
	} );

	function setTestResult( $out, msg, ok ) {
		$out.text( msg );
		$out.removeClass( 'jt-test-ok jt-test-fail' );
		if ( ok === true ) {
			$out.addClass( 'jt-test-ok' );
		} else if ( ok === false ) {
			$out.addClass( 'jt-test-fail' );
		}
	}

	function bindConnectionTest( btnSel, outSel, actionKey ) {
		$( document ).on( 'click', btnSel, function () {
			var $btn = $( this );
			var $out = $( outSel );
			if ( ! $btn.length || ! $out.length ) {
				return;
			}
			$btn.prop( 'disabled', true );
			setTestResult( $out, jtAdmin.i18n.testing, null );
			$.post( jtAdmin.ajaxUrl, {
				action: jtAdmin[ actionKey ],
				nonce: jtAdmin.nonce,
			} )
				.done( function ( res ) {
					if ( res.success ) {
						setTestResult(
							$out,
							res.data && res.data.message ? res.data.message : jtAdmin.i18n.done,
							true
						);
					} else {
						setTestResult(
							$out,
							res.data && res.data.message ? res.data.message : 'Error',
							false
						);
					}
				} )
				.fail( function ( xhr ) {
					setTestResult( $out, ajaxFailMessage( xhr, jtAdmin.i18n.networkError ), false );
				} )
				.always( function () {
					$btn.prop( 'disabled', false );
				} );
		} );
	}

	bindConnectionTest( '#jt-test-rss', '#jt-test-rss-result', 'ajaxTestRss' );

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
