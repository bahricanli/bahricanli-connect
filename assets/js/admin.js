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

			post( 'bahrco_test_connection', {
				api_base: ( document.getElementById( 'bahrco_api_base' ) || {} ).value || '',
				api_key: ( document.getElementById( 'bahrco_api_key' ) || {} ).value || '',
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

	function esc( s ) {
		var d = document.createElement( 'div' );
		d.textContent = s == null ? '' : String( s );
		return d.innerHTML;
	}

	function errorMessage( res, fallback ) {
		return ( res.json && res.json.data && res.json.data.message ) || fallback;
	}

	/**
	 * Onaylı şablon seçme + değişken + önizleme formu.
	 * send( template, values ) bir post() promise'i döndürmeli; başarıda onSent() çağrılır.
	 */
	function renderTemplateForm( container, send, onSent, isStale ) {
		post( 'bahrco_templates', {} ).then( function ( res ) {
			if ( isStale && isStale() ) return;
			if ( ! res.ok ) {
				container.textContent = errorMessage( res, 'Şablonlar yüklenemedi. Sayfayı yenileyerek deneyin.' );
				return;
			}
			var templates = ( res.json.data && res.json.data.data ) || [];
			if ( ! templates.length ) {
				container.textContent = 'Onaylı şablon bulunamadı. Message Manager panelindeki Şablonlar sayfasından Meta ile senkronlayın.';
				return;
			}
			container.innerHTML = '<form class="bc-template-form">' +
				'<label>Onaylı şablon<select required>' +
				'<option value="">Şablon seçin</option>' + templates.map( function ( t, index ) {
					return '<option value="' + index + '">' + esc( t.name + ' · ' + t.language ) + '</option>';
				} ).join( '' ) + '</select></label>' +
				'<div class="bc-template-params"></div>' +
				'<div class="bc-template-preview" hidden></div>' +
				'<p class="bc-template-error" role="alert"></p>' +
				'<p class="bc-template-success" role="status"></p>' +
				'<button type="submit" class="button button-primary" disabled>Şablonu gönder</button></form>';
			var form = container.querySelector( 'form' );
			var select = form.querySelector( 'select' );
			var fields = form.querySelector( '.bc-template-params' );
			var preview = form.querySelector( '.bc-template-preview' );
			var error = form.querySelector( '.bc-template-error' );
			var success = form.querySelector( '.bc-template-success' );
			var button = form.querySelector( 'button' );
			var sending = false;

			function params() {
				return Array.from( fields.querySelectorAll( 'input' ) ).map( function ( input ) { return input.value; } );
			}
			function updatePreview() {
				var template = templates[ select.value ];
				var values = params();
				preview.hidden = ! template;
				preview.textContent = template ? ( template.body || '' ).replace( /\{\{\s*(\d+)\s*\}\}/g, function ( placeholder, index ) {
					return values[ Number( index ) - 1 ] || placeholder;
				} ) : '';
				button.disabled = sending || ! template || values.some( function ( value ) { return ! value.trim(); } );
			}
			select.addEventListener( 'change', function () {
				var template = templates[ select.value ];
				error.textContent = '';
				success.textContent = '';
				fields.innerHTML = '';
				for ( var i = 0; template && i < template.body_params; i++ ) {
					var label = document.createElement( 'label' );
					label.textContent = 'Değişken ' + ( i + 1 );
					var input = document.createElement( 'input' );
					input.type = 'text';
					input.required = true;
					input.maxLength = 1024;
					label.appendChild( input );
					fields.appendChild( label );
				}
				updatePreview();
			} );
			fields.addEventListener( 'input', updatePreview );
			form.addEventListener( 'submit', function ( event ) {
				event.preventDefault();
				var template = templates[ select.value ];
				if ( sending || ! template || ! form.reportValidity() ) return;
				sending = true;
				error.textContent = '';
				success.textContent = '';
				form.querySelectorAll( 'input, select, button' ).forEach( function ( el ) { el.disabled = true; } );
				button.textContent = 'Gönderiliyor…';
				send( template, params() ).then( function ( result ) {
					if ( isStale && isStale() ) return;
					if ( result.ok ) {
						onSent( form, success );
					} else {
						error.textContent = errorMessage( result, 'Şablon gönderilemedi.' );
					}
				} ).catch( function () {
					error.textContent = 'Bağlantı hatası. Gönderim durumunu kontrol edin.';
				} ).finally( function () {
					sending = false;
					form.querySelectorAll( 'input, select, button' ).forEach( function ( el ) { el.disabled = false; } );
					button.textContent = 'Şablonu gönder';
					updatePreview();
				} );
			} );
		} ).catch( function () {
			container.textContent = 'Şablonlar yüklenemedi. Sayfayı yenileyerek deneyin.';
		} );
	}

	/* ----- Mesaj Gönder: numaraya şablonla yeni mesaj ----- */
	var sendPage = document.getElementById( 'bc-send' );
	if ( sendPage ) {
		var toInput = document.getElementById( 'bc-send-to' );
		renderTemplateForm(
			document.getElementById( 'bc-send-template' ),
			function ( template, values ) {
				if ( ! toInput.reportValidity() ) {
					return Promise.resolve( { ok: false, json: { data: { message: 'Telefon numarası gerekli.' } } } );
				}
				return post( 'bahrco_send_new_template', {
					to: toInput.value.trim(), template: template.name, language: template.language, params: JSON.stringify( values ),
				} );
			},
			function ( form, success ) {
				form.reset();
				form.querySelector( 'select' ).dispatchEvent( new Event( 'change' ) );
				success.textContent = '✓ Mesaj gönderildi: ' + toInput.value.trim();
				toInput.value = '';
			}
		);
	}

	/* ----- Gelen kutusu ----- */
	var inbox = document.getElementById( 'bc-inbox' );
	if ( ! inbox ) {
		return;
	}

	var state = { status: 'open', conversationId: null, threadVersion: 0, conversations: {} };
	var listEl = document.getElementById( 'bc-conversations' );
	var threadEl = document.getElementById( 'bc-thread' );

	function loadConversations() {
		post( 'bahrco_conversations', { status: state.status } ).then( function ( res ) {
			if ( ! res.ok ) {
				listEl.innerHTML = '<li class="bc-inbox__empty">' + esc( res.json.data && res.json.data.message ) + '</li>';
				return;
			}
			var items = ( res.json.data && res.json.data.data ) || [];
			items.forEach( function ( c ) {
				state.conversations[ c.id ] = c;
			} );
			updateWindowTimer();
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

	/* 24 saatlik yanıt penceresi: window_expires_at = son gelen mesaj + 24 saat */
	function windowRemainingMs( id ) {
		var c = state.conversations[ id ];
		return c && c.window_expires_at ? new Date( c.window_expires_at ).getTime() - Date.now() : 0;
	}

	function updateWindowTimer() {
		var timer = document.getElementById( 'bc-window-timer' );
		if ( ! timer || ! state.conversationId ) return;

		var remaining = windowRemainingMs( state.conversationId );
		var open = remaining > 0;
		var body = document.getElementById( 'bc-body' );
		var sendBtn = document.getElementById( 'bc-send-btn' );

		if ( open ) {
			var total = Math.floor( remaining / 1000 );
			var pad = function ( n ) { return String( n ).padStart( 2, '0' ); };
			timer.innerHTML = 'Yanıt süresi: <strong>' + pad( Math.floor( total / 3600 ) ) + ':' +
				pad( Math.floor( ( total % 3600 ) / 60 ) ) + ':' + pad( total % 60 ) + '</strong> kaldı';
		} else {
			timer.textContent = '24 saatlik yanıt süresi doldu. Yukarıdan onaylı bir şablon gönderebilirsiniz.';
		}
		timer.classList.toggle( 'is-urgent', open && remaining < 3600000 );
		timer.classList.toggle( 'is-expired', ! open );

		if ( body && body.disabled !== ! open && ! body.dataset.sending ) {
			body.disabled = ! open;
			body.placeholder = open ? 'Mesaj yazın…' : 'Süre doldu — şablon gönderin';
		}
		if ( sendBtn ) sendBtn.disabled = ! open;
	}

	setInterval( updateWindowTimer, 1000 );

	function loadThread( id ) {
		state.conversationId = id;
		var version = ++state.threadVersion;
		threadEl.innerHTML = '<p class="bc-inbox__placeholder">Yükleniyor…</p>';

		post( 'bahrco_messages', { conversation_id: id } ).then( function ( res ) {
			if ( version !== state.threadVersion ) return;
			if ( ! res.ok ) {
				threadEl.innerHTML = '<p class="bc-inbox__placeholder">' + esc( res.json.data && res.json.data.message ) + '</p>';
				return;
			}
			var msgs = ( res.json.data && res.json.data.data ) || [];
			var rows = msgs.map( function ( m ) {
				var dir = m.direction === 'out' ? 'out' : 'in';
				return '<div class="bc-msg bc-msg--' + dir + '"><span>' + esc( m.body || ( '[' + m.type + ']' ) ) + '</span></div>';
			} ).join( '' );
			var conversation = state.conversations[ id ] || {};
			var archived = !! conversation.archived_at;

			threadEl.innerHTML =
				'<div class="bc-thread__header">' +
				'<button type="button" class="button" id="bc-archive">' + ( archived ? 'Arşivden çıkar' : 'Arşivle' ) + '</button>' +
				'</div>' +
				'<div class="bc-thread__messages" id="bc-thread-messages">' + rows + '</div>' +
				'<details class="bc-template-panel"' + ( windowRemainingMs( id ) > 0 ? '' : ' open' ) + '><summary>Şablon mesajı</summary>' +
				'<p>24 saatlik yanıt süresi dolduğunda da onaylı şablon gönderebilirsiniz.</p>' +
				'<div id="bc-template-content">Şablonlar yükleniyor…</div></details>' +
				'<div class="bc-window-timer" id="bc-window-timer" role="timer"></div>' +
				'<form class="bc-thread__composer" id="bc-composer">' +
				'<textarea id="bc-body" rows="2" placeholder="Mesaj yazın…"></textarea>' +
				'<button type="submit" class="button button-primary" id="bc-send-btn">Gönder</button>' +
				'</form>';

			updateWindowTimer();

			renderTemplateForm(
				document.getElementById( 'bc-template-content' ),
				function ( template, values ) {
					return post( 'bahrco_send_template', {
						conversation_id: id, template_id: template.id, params: JSON.stringify( values ),
					} );
				},
				function () {
					loadThread( id );
					loadConversations();
				},
				function () { return version !== state.threadVersion; }
			);

			var box = document.getElementById( 'bc-thread-messages' );
			box.scrollTop = box.scrollHeight;

			document.getElementById( 'bc-archive' ).addEventListener( 'click', function ( e ) {
				var btn = e.currentTarget;
				btn.disabled = true;
				post( 'bahrco_archive', { conversation_id: id, archived: archived ? '0' : '1' } ).then( function ( r ) {
					if ( ! r.ok ) {
						btn.disabled = false;
						window.alert( errorMessage( r, 'İşlem başarısız' ) );
						return;
					}
					state.conversations[ id ] = Object.assign( conversation, r.json.data || {} );
					state.conversationId = null;
					state.threadVersion++;
					threadEl.innerHTML = '<p class="bc-inbox__placeholder">' + ( archived ? 'Konuşma arşivden çıkarıldı.' : 'Konuşma arşivlendi.' ) + '</p>';
					loadConversations();
				} );
			} );

			document.getElementById( 'bc-composer' ).addEventListener( 'submit', function ( e ) {
				e.preventDefault();
				var ta = document.getElementById( 'bc-body' );
				var body = ta.value.trim();
				if ( ! body || windowRemainingMs( id ) <= 0 ) {
					return;
				}
				ta.disabled = true;
				ta.dataset.sending = '1';
				post( 'bahrco_send_message', { conversation_id: id, body: body } ).then( function ( r ) {
					ta.disabled = false;
					delete ta.dataset.sending;
					if ( r.ok ) {
						ta.value = '';
						loadThread( id );
						loadConversations();
					} else {
						window.alert( errorMessage( r, 'Gönderilemedi' ) );
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
