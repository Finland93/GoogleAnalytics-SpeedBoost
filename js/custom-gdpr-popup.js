/**
 * PopUp for Google Analytics — consent + deferred GA loader (vanilla JS).
 */
( function () {
	'use strict';

	var cfg = window.customGdprPopup || {};
	var COOKIE = cfg.cookieName || 'AllowAnalytics';
	var DAYS = cfg.cookieDays || 365;

	function getCookie( name ) {
		var parts = document.cookie ? document.cookie.split( ';' ) : [];
		for ( var i = 0; i < parts.length; i++ ) {
			var pair = parts[ i ].trim();
			if ( 0 === pair.indexOf( name + '=' ) ) {
				return pair.substring( name.length + 1 );
			}
		}
		return null;
	}

	function setCookie( name, value, days ) {
		var expires = new Date();
		expires.setTime( expires.getTime() + days * 24 * 60 * 60 * 1000 );
		document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/;SameSite=Lax';
	}

	function deleteCookie( name ) {
		document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/;SameSite=Lax';
	}

	var gaLoaded = false;
	function loadAnalytics() {
		if ( gaLoaded || ! cfg.analyticsScript ) {
			return;
		}
		gaLoaded = true;

		var s = document.createElement( 'script' );
		s.async = true;
		s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent( cfg.analyticsScript );
		document.head.appendChild( s );

		window.dataLayer = window.dataLayer || [];
		function gtag() {
			window.dataLayer.push( arguments );
		}
		gtag( 'js', new Date() );
		gtag( 'config', cfg.analyticsScript );
	}

	function ready( fn ) {
		if ( 'loading' === document.readyState ) {
			document.addEventListener( 'DOMContentLoaded', fn );
		} else {
			fn();
		}
	}

	ready( function () {
		var popup = document.getElementById( 'gdpr-popup' );
		var acceptBtn = document.getElementById( 'accept-btn' );
		var rejectBtn = document.getElementById( 'reject-btn' );

		function hidePopup() {
			if ( popup ) {
				popup.style.display = 'none';
			}
		}
		function showPopup() {
			if ( popup ) {
				popup.style.display = 'block';
				if ( acceptBtn ) {
					acceptBtn.focus();
				}
			}
		}

		if ( acceptBtn ) {
			acceptBtn.addEventListener( 'click', function () {
				setCookie( COOKIE, 'true', DAYS );
				hidePopup();
				loadAnalytics();
			} );
		}
		if ( rejectBtn ) {
			rejectBtn.addEventListener( 'click', function () {
				setCookie( COOKIE, 'false', DAYS );
				hidePopup();
			} );
		}

		// Withdraw-consent links ([ga_consent_reset]).
		var resets = document.querySelectorAll( '.ga-consent-reset' );
		for ( var i = 0; i < resets.length; i++ ) {
			resets[ i ].addEventListener( 'click', function ( e ) {
				e.preventDefault();
				deleteCookie( COOKIE );
				window.location.reload();
			} );
		}

		// Decide initial state.
		var choice = getCookie( COOKIE );
		if ( 'true' === choice ) {
			loadAnalytics();
		} else if ( 'false' === choice ) {
			hidePopup();
		} else {
			showPopup();
		}
	} );
} )();
