/**
 * Speaker Water Ejector Tool — vanilla JS, no dependencies.
 * Loaded with `defer` and only on pages that render the [speaker_water_ejector]
 * shortcode. Safe to run more than once if a page somehow prints the
 * shortcode twice: everything is scoped to elements found inside #swe-tool.
 */
( function () {
	'use strict';

	var ROOT_ID = 'swe-tool';
	var RING_CIRCUMFERENCE = 326.7256; // 2 * PI * r(52), matches the CSS/SVG.
	var RUN_DURATION_MS = 30000;

	/** Frequency + gain profile per mode. Frequencies pulse between low/high. */
	var MODES = {
		water: { low: 150, high: 220, gain: 0.5, pulseHz: 6 },
		deep: { low: 120, high: 180, gain: 0.55, pulseHz: 4 },
		gentle: { low: 180, high: 250, gain: 0.32, pulseHz: 7 }
	};

	var STATUS_TEXT = {
		ready: 'Ready',
		playing: 'Playing',
		completed: 'Completed',
		stopped: 'Stopped'
	};

	/** Quiz issue -> advice text + CTA label + CTA action. */
	var QUIZ_MAP = {
		muffled: {
			advice: 'Try Gentle Clean Mode first, then run the speaker test.',
			cta: 'Start Gentle Clean',
			action: function ( ctx ) {
				setMode( ctx, 'gentle' );
				scrollToId( 'swe-tool' );
			}
		},
		water: {
			advice: 'Place the speaker facing down, turn volume up, and run Water Eject Mode.',
			cta: 'Start Water Eject',
			action: function ( ctx ) {
				setMode( ctx, 'water' );
				scrollToId( 'swe-tool' );
			}
		},
		oneside: {
			advice: 'Use the Left/Right Speaker Test to check channel balance.',
			cta: 'Test Left and Right',
			action: function () {
				scrollToId( 'swe-sound-test' );
			}
		},
		lowvolume: {
			advice: 'Clean the speaker grille gently and run Both Speakers Test.',
			cta: 'Test My Speaker',
			action: function () {
				scrollToId( 'swe-sound-test' );
			}
		},
		crackling: {
			advice: 'Stop if the sound is harsh. Crackling may be caused by debris, water, or hardware damage.',
			cta: 'Run Gentle Clean',
			action: function ( ctx ) {
				setMode( ctx, 'gentle' );
				scrollToId( 'swe-tool' );
			}
		}
	};

	function scrollToId( id ) {
		var el = document.getElementById( id );
		if ( el && el.scrollIntoView ) {
			el.scrollIntoView( { behavior: 'smooth', block: 'start' } );
		}
	}

	function setMode( ctx, mode ) {
		if ( ctx.modeSelect ) {
			ctx.modeSelect.value = mode;
		}
	}

	function formatTime( ms ) {
		var totalSeconds = Math.max( 0, Math.ceil( ms / 1000 ) );
		var minutes = Math.floor( totalSeconds / 60 );
		var seconds = totalSeconds % 60;
		return minutes + ':' + ( seconds < 10 ? '0' : '' ) + seconds;
	}

	function getAudioContextClass() {
		return window.AudioContext || window.webkitAudioContext || null;
	}

	/**
	 * Build the tool controller. All state lives on `ctx`, a plain object,
	 * so we avoid classes/closures-within-closures getting hard to follow.
	 */
	function initTool( root ) {
		var AudioContextClass = getAudioContextClass();

		var ctx = {
			root: root,
			audioCtx: null,
			modeSelect: root.querySelector( '#swe-mode-select' ),
			startBtn: root.querySelector( '#swe-start-btn' ),
			startIcon: root.querySelector( '#swe-start-icon' ),
			startText: root.querySelector( '#swe-start-text' ),
			statusValue: root.querySelector( '#swe-status-value' ),
			timerEl: root.querySelector( '#swe-timer' ),
			ringFg: root.querySelector( '#swe-ring-fg' ),
			resultCard: root.querySelector( '#swe-result-card' ),
			runAgainBtn: root.querySelector( '#swe-run-again' ),
			supportNote: root.querySelector( '#swe-audio-support-note' ),
			isPlaying: false,
			rafId: null,
			runStartTime: 0,
			oscillators: [],
			gainNode: null,
			lfoNode: null
		};

		if ( ctx.ringFg ) {
			ctx.ringFg.style.strokeDasharray = RING_CIRCUMFERENCE.toFixed( 4 );
			ctx.ringFg.style.strokeDashoffset = RING_CIRCUMFERENCE.toFixed( 4 );
		}

		if ( ! AudioContextClass ) {
			if ( ctx.supportNote ) {
				ctx.supportNote.hidden = false;
			}
			if ( ctx.startBtn ) {
				ctx.startBtn.disabled = true;
			}
			return ctx;
		}

		if ( ctx.startBtn ) {
			ctx.startBtn.addEventListener( 'click', function () {
				if ( ctx.isPlaying ) {
					stopRun( ctx, 'stopped' );
				} else {
					startRun( ctx );
				}
			} );
		}

		if ( ctx.runAgainBtn ) {
			ctx.runAgainBtn.addEventListener( 'click', function () {
				if ( ctx.resultCard ) {
					ctx.resultCard.hidden = true;
				}
				startRun( ctx );
			} );
		}

		initSoundTest( root, AudioContextClass, ctx );
		initQuiz( root, ctx );

		return ctx;
	}

	function ensureAudioContext( ctx, AudioContextClass ) {
		if ( ! ctx.audioCtx ) {
			ctx.audioCtx = new AudioContextClass();
		}
		if ( ctx.audioCtx.state === 'suspended' ) {
			ctx.audioCtx.resume();
		}
		return ctx.audioCtx;
	}

	function startRun( ctx ) {
		var AudioContextClass = getAudioContextClass();
		if ( ! AudioContextClass ) {
			return;
		}

		var audioCtx = ensureAudioContext( ctx, AudioContextClass );
		var modeKey = ctx.modeSelect ? ctx.modeSelect.value : 'water';
		var profile = MODES[ modeKey ] || MODES.water;

		stopOscillators( ctx );

		var now = audioCtx.currentTime;

		// Main oscillator, frequency modulated by an LFO to create the
		// "pulsing" feel requested for each mode, instead of a flat tone.
		var osc = audioCtx.createOscillator();
		osc.type = 'sine';
		var midFreq = ( profile.low + profile.high ) / 2;
		var freqSwing = ( profile.high - profile.low ) / 2;
		osc.frequency.setValueAtTime( midFreq, now );

		var lfo = audioCtx.createOscillator();
		lfo.type = 'sine';
		lfo.frequency.setValueAtTime( profile.pulseHz, now );

		var lfoGain = audioCtx.createGain();
		lfoGain.gain.setValueAtTime( freqSwing, now );

		lfo.connect( lfoGain );
		lfoGain.connect( osc.frequency );

		var gainNode = audioCtx.createGain();
		gainNode.gain.setValueAtTime( 0, now );
		gainNode.gain.linearRampToValueAtTime( profile.gain, now + 0.15 );

		osc.connect( gainNode );
		gainNode.connect( audioCtx.destination );

		osc.start( now );
		lfo.start( now );

		ctx.oscillators = [ osc, lfo ];
		ctx.gainNode = gainNode;
		ctx.isPlaying = true;
		ctx.runStartTime = performance.now();

		updateStatus( ctx, 'playing' );

		if ( ctx.startBtn ) {
			ctx.startBtn.classList.add( 'is-playing' );
			ctx.startBtn.setAttribute( 'aria-pressed', 'true' );
		}
		if ( ctx.startIcon ) {
			ctx.startIcon.textContent = '■';
		}
		if ( ctx.startText ) {
			ctx.startText.textContent = 'Stop';
		}
		if ( ctx.resultCard ) {
			ctx.resultCard.hidden = true;
		}

		tickProgress( ctx );

		// Auto-stop after the fixed run duration.
		ctx.autoStopTimeout = window.setTimeout( function () {
			stopRun( ctx, 'completed' );
		}, RUN_DURATION_MS );
	}

	function tickProgress( ctx ) {
		if ( ! ctx.isPlaying ) {
			return;
		}

		var elapsed = performance.now() - ctx.runStartTime;
		var remaining = Math.max( 0, RUN_DURATION_MS - elapsed );
		var fraction = Math.min( 1, elapsed / RUN_DURATION_MS );

		if ( ctx.timerEl ) {
			ctx.timerEl.textContent = formatTime( remaining );
		}

		if ( ctx.ringFg ) {
			var offset = RING_CIRCUMFERENCE * ( 1 - fraction );
			ctx.ringFg.style.strokeDashoffset = offset.toFixed( 4 );
		}

		if ( remaining > 0 ) {
			ctx.rafId = window.requestAnimationFrame( function () {
				tickProgress( ctx );
			} );
		}
	}

	function stopOscillators( ctx ) {
		if ( ctx.oscillators && ctx.oscillators.length ) {
			ctx.oscillators.forEach( function ( node ) {
				try {
					node.stop();
					node.disconnect();
				} catch ( err ) {
					// Node may already be stopped; nothing to do.
				}
			} );
		}
		ctx.oscillators = [];

		if ( ctx.gainNode ) {
			try {
				ctx.gainNode.disconnect();
			} catch ( err ) {
				// Already disconnected.
			}
			ctx.gainNode = null;
		}
	}

	function stopRun( ctx, finalStatus ) {
		if ( ctx.autoStopTimeout ) {
			window.clearTimeout( ctx.autoStopTimeout );
			ctx.autoStopTimeout = null;
		}

		if ( ctx.gainNode && ctx.audioCtx ) {
			var now = ctx.audioCtx.currentTime;
			try {
				ctx.gainNode.gain.cancelScheduledValues( now );
				ctx.gainNode.gain.setValueAtTime( ctx.gainNode.gain.value, now );
				ctx.gainNode.gain.linearRampToValueAtTime( 0, now + 0.08 );
			} catch ( err ) {
				// Ignore ramp errors on already-stopped nodes.
			}
		}

		window.setTimeout( function () {
			stopOscillators( ctx );
		}, 100 );

		ctx.isPlaying = false;

		if ( ctx.rafId ) {
			window.cancelAnimationFrame( ctx.rafId );
			ctx.rafId = null;
		}

		if ( ctx.timerEl ) {
			ctx.timerEl.textContent = formatTime( RUN_DURATION_MS );
		}

		if ( ctx.ringFg ) {
			ctx.ringFg.style.strokeDashoffset = RING_CIRCUMFERENCE.toFixed( 4 );
		}

		if ( ctx.startBtn ) {
			ctx.startBtn.classList.remove( 'is-playing' );
			ctx.startBtn.setAttribute( 'aria-pressed', 'false' );
		}
		if ( ctx.startIcon ) {
			ctx.startIcon.textContent = '▶';
		}
		if ( ctx.startText ) {
			ctx.startText.textContent = 'Start';
		}

		updateStatus( ctx, finalStatus || 'stopped' );

		if ( finalStatus === 'completed' && ctx.resultCard ) {
			ctx.resultCard.hidden = false;
		}
	}

	function updateStatus( ctx, key ) {
		if ( ctx.statusValue && STATUS_TEXT[ key ] ) {
			ctx.statusValue.textContent = STATUS_TEXT[ key ];
		}
	}

	/* ------------------------------------------------------------------ */
	/* Speaker sound test                                                  */
	/* ------------------------------------------------------------------ */

	var TEST_TONES = {
		left: { freq: 440, pan: -1, label: 'Left speaker' },
		right: { freq: 440, pan: 1, label: 'Right speaker' },
		both: { freq: 440, pan: 0, label: 'Both speakers' },
		low: { freq: 150, freqEnd: 400, pan: 0, label: 'Bass range (150-400 Hz)' },
		high: { freq: 2000, freqEnd: 8000, pan: 0, label: 'Treble range (2-8 kHz)' },
		voice: { freq: 300, freqEnd: 3400, pan: 0, label: 'Voice range (300-3400 Hz)' },
		distortion: { freq: 500, freqEnd: 2000, pan: 0, label: 'Distortion sweep (500-2000 Hz)' }
	};

	function initSoundTest( root, AudioContextClass, sharedCtx ) {
		var buttons = root.querySelectorAll( '.swe-test-btn' );
		var statusEl = root.querySelector( '#swe-test-status' );

		if ( ! buttons.length ) {
			return;
		}

		var testAudioCtx = null;

		buttons.forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var testKey = btn.getAttribute( 'data-test' );
				var profile = TEST_TONES[ testKey ];
				if ( ! profile ) {
					return;
				}

				if ( ! testAudioCtx ) {
					testAudioCtx = ensureAudioContext( { audioCtx: null }, AudioContextClass );
					// ensureAudioContext expects a ctx object with an audioCtx
					// property it can mutate; reuse the shared tool context's
					// AudioContext instance if one already exists so we do not
					// spin up two separate contexts unnecessarily.
					testAudioCtx = sharedCtx.audioCtx || testAudioCtx;
					sharedCtx.audioCtx = testAudioCtx;
				}

				if ( testAudioCtx.state === 'suspended' ) {
					testAudioCtx.resume();
				}

				playTestTone( testAudioCtx, profile );

				if ( statusEl ) {
					statusEl.textContent = 'Playing: ' + profile.label;
					window.setTimeout( function () {
						if ( statusEl.textContent === 'Playing: ' + profile.label ) {
							statusEl.textContent = '';
						}
					}, 2200 );
				}
			} );
		} );
	}

	function playTestTone( audioCtx, profile ) {
		var now = audioCtx.currentTime;
		var duration = 2; // seconds

		var osc = audioCtx.createOscillator();
		osc.type = 'sine';
		osc.frequency.setValueAtTime( profile.freq, now );
		if ( profile.freqEnd ) {
			osc.frequency.exponentialRampToValueAtTime( profile.freqEnd, now + duration );
		}

		var gainNode = audioCtx.createGain();
		gainNode.gain.setValueAtTime( 0, now );
		gainNode.gain.linearRampToValueAtTime( 0.4, now + 0.05 );
		gainNode.gain.setValueAtTime( 0.4, now + duration - 0.1 );
		gainNode.gain.linearRampToValueAtTime( 0, now + duration );

		var lastNode = gainNode;

		if ( typeof audioCtx.createStereoPanner === 'function' ) {
			var panner = audioCtx.createStereoPanner();
			panner.pan.setValueAtTime( profile.pan, now );
			osc.connect( gainNode );
			gainNode.connect( panner );
			panner.connect( audioCtx.destination );
		} else {
			// Fallback: no panning support, play centered.
			osc.connect( gainNode );
			gainNode.connect( audioCtx.destination );
		}

		osc.start( now );
		osc.stop( now + duration + 0.05 );

		osc.addEventListener( 'ended', function () {
			try {
				osc.disconnect();
				gainNode.disconnect();
			} catch ( err ) {
				// Already disconnected.
			}
		} );
	}

	/* ------------------------------------------------------------------ */
	/* Troubleshooting quiz                                                */
	/* ------------------------------------------------------------------ */

	function initQuiz( root, ctx ) {
		var quizButtons = root.querySelectorAll( '.swe-quiz-btn' );
		var resultBox = root.querySelector( '#swe-quiz-result' );
		var adviceEl = root.querySelector( '#swe-quiz-advice' );
		var ctaBtn = root.querySelector( '#swe-quiz-cta' );

		if ( ! quizButtons.length ) {
			return;
		}

		quizButtons.forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var issue = btn.getAttribute( 'data-issue' );
				var mapping = QUIZ_MAP[ issue ];
				if ( ! mapping ) {
					return;
				}

				quizButtons.forEach( function ( b ) {
					b.setAttribute( 'aria-pressed', 'false' );
				} );
				btn.setAttribute( 'aria-pressed', 'true' );

				if ( adviceEl ) {
					adviceEl.textContent = mapping.advice;
				}
				if ( ctaBtn ) {
					ctaBtn.textContent = mapping.cta;
					ctaBtn.onclick = function () {
						mapping.action( ctx );
					};
				}
				if ( resultBox ) {
					resultBox.hidden = false;
				}
			} );
		} );
	}

	/* ------------------------------------------------------------------ */
	/* Boot                                                                */
	/* ------------------------------------------------------------------ */

	function boot() {
		var root = document.getElementById( ROOT_ID );
		if ( ! root ) {
			return;
		}
		initTool( root );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
} )();
