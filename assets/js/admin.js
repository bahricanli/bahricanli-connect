/* global BahriCanliConnect */
( function () {
	'use strict';

	var cfg = window.BahriCanliConnect || {};

	function post( action, data ) {
		var body = new URLSearchParams();
		body.set( 'action', action );
		body.set( 'nonce', cfg.nonce );
		Object.keys( data || {} ).forEach( function ( k ) {
			body.set( k, data[ k ] );
		} );

		return fetch( cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString(),
		} ).then( function ( r ) {
			return r.json().then( function ( json ) {
				return { ok: r.ok && json.success, json: json };
			} );
		} );
	}

	/* ----- Ayarlar: bağlantı testi ----- */
	var testBtn = document.getElementById( 'bc-test-connection' );
	if ( testBtn ) {
		testBtn.addEventListener( 'click', function () {
			var result = document.getElementById( 'bc-test-result' );
			result.textContent = '…';
			result.className = 'bc-test-result';

			post( 'bc_test_connection', {
				api_base: ( document.getElementById( 'bc_api_base' ) || {} ).value || '',
				api_key: ( document.getElementById( 'bc_api_key' ) || {} ).value || '',
			} ).then( function ( res ) {
				if ( res.ok ) {
					result.textContent = '✓ ' + 'Bağlantı başarılı';
					result.classList.add( 'is-ok' );
				} else {
					result.textContent = '✗ ' + ( res.json.data && res.json.data.message ? res.json.data.message : 'Bağlantı başarısız' );
					result.classList.add( 'is-err' );
				}
			} );
		} );
	}

	/* ----- Gelen kutusu ----- */
	var inbox = document.getElementById( 'bc-inbox' );
	if ( ! inbox ) {
		return;
	}

	var state = { status: 'open', conversationId: null };
	var listEl = document.getElementById( 'bc-conversations' );
	var threadEl = document.getElementById( 'bc-thread' );

	function esc( s ) {
		var d = document.createElement( 'div' );
		d.textContent = s == null ? '' : String( s );
		return d.innerHTML;
	}

	function loadConversations() {
		post( 'bc_conversations', { status: state.status } ).then( function ( res ) {
			if ( ! res.ok ) {
				listEl.innerHTML = '<li class="bc-inbox__empty">' + esc( res.json.data && res.json.data.message ) + '</li>';
				return;
			}
			var items = ( res.json.data && res.json.data.data ) || [];
			if ( ! items.length ) {
				listEl.innerHTML = '<li class="bc-inbox__empty">Konuşma yok.</li>';
				return;
			}
			listEl.innerHTML = items.map( function ( c ) {
				var name = ( c.contact && ( c.contact.name || c.contact.profile_name || c.contact.wa_id ) ) || '—';
				var active = c.id === state.conversationId ? ' is-active' : '';
				var badge = c.unread_count > 0 ? '<span class="bc-badge">' + c.unread_count + '</span>' : '';
				return '<li><button type="button" class="bc-conv' + active + '" data-id="' + c.id + '">' +
					'<span class="bc-conv__name">' + esc( name ) + '</span>' + badge +
					'<span class="bc-conv__wa">' + esc( c.contact && c.contact.wa_id ) + '</span>' +
					'</button></li>';
			} ).join( '' );
		} );
	}

	function loadThread( id ) {
		state.conversationId = id;
		threadEl.innerHTML = '<p class="bc-inbox__placeholder">Yükleniyor…</p>';

		post( 'bc_messages', { conversation_id: id } ).then( function ( res ) {
			if ( ! res.ok ) {
				threadEl.innerHTML = '<p class="bc-inbox__placeholder">' + esc( res.json.data && res.json.data.message ) + '</p>';
				return;
			}
			var msgs = ( res.json.data && res.json.data.data ) || [];
			var rows = msgs.map( function ( m ) {
				var dir = m.direction === 'out' ? 'out' : 'in';
				return '<div class="bc-msg bc-msg--' + dir + '"><span>' + esc( m.body || ( '[' + m.type + ']' ) ) + '</span></div>';
			} ).join( '' );

			threadEl.innerHTML =
				'<div class="bc-thread__messages" id="bc-thread-messages">' + rows + '</div>' +
				'<form class="bc-thread__composer" id="bc-composer">' +
				'<textarea id="bc-body" rows="2" placeholder="Mesaj yazın…"></textarea>' +
				'<button type="submit" class="button button-primary">Gönder</button>' +
				'</form>';

			var box = document.getElementById( 'bc-thread-messages' );
			box.scrollTop = box.scrollHeight;

			document.getElementById( 'bc-composer' ).addEventListener( 'submit', function ( e ) {
				e.preventDefault();
				var ta = document.getElementById( 'bc-body' );
				var body = ta.value.trim();
				if ( ! body ) {
					return;
				}
				ta.disabled = true;
				post( 'bc_send_message', { conversation_id: id, body: body } ).then( function ( r ) {
					ta.disabled = false;
					if ( r.ok ) {
						ta.value = '';
						loadThread( id );
						loadConversations();
					} else {
						window.alert( ( r.json.data && r.json.data.message ) || 'Gönderilemedi' );
					}
				} );
			} );
		} );
	}

	listEl.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest( '.bc-conv' );
		if ( btn ) {
			loadThread( parseInt( btn.getAttribute( 'data-id' ), 10 ) );
			loadConversations();
		}
	} );

	inbox.querySelectorAll( '.bc-filter' ).forEach( function ( b ) {
		b.addEventListener( 'click', function () {
			inbox.querySelectorAll( '.bc-filter' ).forEach( function ( x ) {
				x.classList.remove( 'is-active' );
			} );
			b.classList.add( 'is-active' );
			state.status = b.getAttribute( 'data-status' );
			loadConversations();
		} );
	} );

	loadConversations();
	setInterval( loadConversations, 8000 );
} )();
