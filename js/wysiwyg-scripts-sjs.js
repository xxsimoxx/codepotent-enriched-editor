/**
 * This file is part of the Enriched Editor ClassicPress plugin and is released under the same license.
 * For more information please see codepotent-enriched-editor.php.
 *
 * Copyright (c) 2007-2016 Andrew Ozz. All rights reserved.
 */

jQuery( document ).ready( function( $ ) {
	var $importElement = $('#tadv-import'),
		$importError = $('#tadv-import-error');

	const sortables = [
		'toolbar_1',
		'toolbar_2',
		'toolbar_3',
		'toolbar_4',
		'unused',
	]

	let i = 0;
	while (i < sortables.length) {
		new Sortable(document.getElementById(sortables[i]), {
			group: 'all',
			animation: 150,
			sort: sortables[i] !== 'unused',
			dragClass: 'highlighted',
			onEnd: function (evt) {
				$('#'+evt.item.id).find('input.tadv-button').attr('name', $('#'+evt.item.id).parent().attr('id') + '[]');
			}
		});
		i++;
	}

	$( '#menubar' ).on( 'change', function() {
		$( '#tadv-mce-menu' ).toggleClass( 'enabled', $(this).prop('checked') );
	});

	$( '#tadvadmins' ).on( 'submit', function() {
		$( 'ul.container' ).each( function( i, node ) {
			$( node ).find( '.tadv-button' ).attr( 'name', node.id ? node.id + '[]' : '' );
		});
	});

	$('#tadv-export-select').on( 'click', function() {
		$('#tadv-export').focus().select();
	});

	$importElement.on( 'change', function() {
		$importError.empty();
	});

	$('#tadv-import-verify').on( 'click', function() {
		var string;

		string = ( $importElement.val() || '' ).replace( /^[^{]*/, '' ).replace( /[^}]*$/, '' );
		$importElement.val( string );

		try {
			JSON.parse( string );
			$importError.text( 'No errors.' );
		} catch( error ) {
			$importError.text( error );
		}
	});

	function translate( str ) {
		if ( window.tadvTranslation.hasOwnProperty( str ) ) {
			return window.tadvTranslation[str];
		}
		return str;
	}

	if ( typeof window.tadvTranslation === 'object' ) {
		$( '.tadvitem' ).each( function( i, element ) {
			var $element = $( element ),
				$descr = $element.find( '.descr' ),
				text = $descr.text();

			if ( text ) {
				text = translate( text );
				$descr.text( text );
				$element.find( '.mce-ico' ).attr( 'title', text );
			}
		});

		$( '#tadv-mce-menu .tadv-translate' ).each( function( i, element ) {
			var $element = $( element ),
				text = $element.text();

			if ( text ) {
				$element.text( translate( text ) );
			}
		});
	}
});
