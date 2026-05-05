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

	$( document ).on( 'click', '#jardin-toasts-sync-now', function () {
		var $btn = $( this );
		$btn.prop( 'disabled', true );
		setStatus( '#jardin-toasts-sync-status', jardinToastsAdmin.i18n.working );
		$.post(
			jardinToastsAdmin.ajaxUrl,
			{
				action: jardinToastsAdmin.ajaxSyncNow,
				nonce: jardinToastsAdmin.nonce,
			}
		)
			.done( function ( res ) {
				if ( res.success ) {
					setStatus( '#jardin-toasts-sync-status', res.data.message || jardinToastsAdmin.i18n.done );
				} else {
					setStatus( '#jardin-toasts-sync-status', res.data && res.data.message ? res.data.message : 'Error' );
				}
			} )
			.fail( function ( xhr ) {
				setStatus( '#jardin-toasts-sync-status', ajaxFailMessage( xhr, 'Request failed.' ) );
			} )
			.always( function () {
				$btn.prop( 'disabled', false );
			} );
	} );

	$( document ).on( 'click', '#jardin-toasts-import-gdpr-csv', function () {
		var $btn = $( this );
		var input = document.getElementById( 'jardin-toasts-gdpr-csv-file' );
		if ( ! input || ! input.files || ! input.files.length ) {
			setStatus(
				'#jardin-toasts-gdpr-csv-status',
				jardinToastsAdmin.i18n.importGdprPick || 'Choose a CSV file first.'
			);
			return;
		}
		var fd = new FormData();
		fd.append( 'action', jardinToastsAdmin.ajaxImportGdprCsv );
		fd.append( 'nonce', jardinToastsAdmin.nonce );
		fd.append( 'jardin_toasts_gdpr_csv', input.files[ 0 ] );
		$btn.prop( 'disabled', true );
		setStatus(
			'#jardin-toasts-gdpr-csv-status',
			jardinToastsAdmin.i18n.importGdprWorking || 'Importing…'
		);
		$.ajax( {
			url: jardinToastsAdmin.ajaxUrl,
			type: 'POST',
			data: fd,
			processData: false,
			contentType: false,
		} )
			.done( function ( res ) {
				if ( res.success ) {
					setStatus(
						'#jardin-toasts-gdpr-csv-status',
						res.data.message || jardinToastsAdmin.i18n.done
					);
				} else {
					setStatus(
						'#jardin-toasts-gdpr-csv-status',
						res.data && res.data.message ? res.data.message : 'Error'
					);
				}
			} )
			.fail( function ( xhr ) {
				setStatus(
					'#jardin-toasts-gdpr-csv-status',
					ajaxFailMessage( xhr, 'Request failed.' )
				);
			} )
			.always( function () {
				$btn.prop( 'disabled', false );
			} );
	} );

	function setTestResult( $out, msg, ok ) {
		$out.text( msg );
		$out.removeClass( 'jardin-toasts-test-ok jardin-toasts-test-fail' );
		if ( ok === true ) {
			$out.addClass( 'jardin-toasts-test-ok' );
		} else if ( ok === false ) {
			$out.addClass( 'jardin-toasts-test-fail' );
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
			setTestResult( $out, jardinToastsAdmin.i18n.testing, null );
			$.post( jardinToastsAdmin.ajaxUrl, {
				action: jardinToastsAdmin[ actionKey ],
				nonce: jardinToastsAdmin.nonce,
			} )
				.done( function ( res ) {
					if ( res.success ) {
						setTestResult(
							$out,
							res.data && res.data.message ? res.data.message : jardinToastsAdmin.i18n.done,
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
					setTestResult( $out, ajaxFailMessage( xhr, jardinToastsAdmin.i18n.networkError ), false );
				} )
				.always( function () {
					$btn.prop( 'disabled', false );
				} );
		} );
	}

	bindConnectionTest( '#jardin-toasts-test-rss', '#jardin-toasts-test-rss-result', 'ajaxTestRss' );

	var $phToggle = $( '#jardin_toasts_use_placeholder_image' );
	var $phPicker = $( '#jardin-toasts-placeholder-picker' );
	var $phId = $( '#jardin_toasts_placeholder_image_id' );
	var $phPreview = $( '#jardin-toasts-placeholder-preview' );
	var $phSelect = $( '#jardin-toasts-placeholder-select' );
	var $phClear = $( '#jardin-toasts-placeholder-clear' );

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
				title: jardinToastsAdmin.i18n.chooseImage,
				button: { text: jardinToastsAdmin.i18n.chooseImage },
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
				$phSelect.text( jardinToastsAdmin.i18n.replaceImage );
			} );
			frame.open();
		} );
	}

	$phClear.on( 'click', function ( e ) {
		e.preventDefault();
		$phId.val( '0' );
		$phPreview.empty();
		$phSelect.text( jardinToastsAdmin.i18n.chooseImage );
	} );

	if ( jardinToastsAdmin.placeholderThumb && $phPreview.length && parseInt( jardinToastsAdmin.placeholderId, 10 ) > 0 ) {
		$phPreview.html(
			'<img src="' + jardinToastsAdmin.placeholderThumb + '" alt="" />'
		);
		$phSelect.text( jardinToastsAdmin.i18n.replaceImage );
	}

	$( '#jardin-toasts-use-rss-username' ).on( 'click', function ( e ) {
		e.preventDefault();
		if ( jardinToastsAdmin.rssUsername ) {
			$( '#jardin_toasts_untappd_username' ).val( jardinToastsAdmin.rssUsername );
		}
	} );
}( jQuery ) );
