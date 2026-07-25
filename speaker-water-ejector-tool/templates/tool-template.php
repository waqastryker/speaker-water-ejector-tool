<?php
/**
 * Template for the [speaker_water_ejector] shortcode.
 * Available: $atts (array) from the shortcode call.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$default_mode = isset( $atts['default_mode'] ) ? sanitize_key( $atts['default_mode'] ) : 'water';
$faqs         = SWE_Water_Ejector_Tool::get_faq_items();
?>
<div class="swe-wrap" id="swe-tool">

	<!-- ============ HERO ============ -->
	<section class="swe-hero">
		<div class="swe-hero-inner">
			<?php $swe_h = empty( $atts['minimal'] ) ? 'h1' : 'h2'; ?><<?php echo esc_attr( $swe_h ); ?> class="swe-h1"><?php esc_html_e( 'Speaker Water Ejector Tool', 'speaker-water-ejector' ); ?></<?php echo esc_attr( $swe_h ); ?>>
			<p class="swe-sub"><?php esc_html_e( 'Play a safe browser sound that may help remove trapped water from your phone speaker.', 'speaker-water-ejector' ); ?></p>

			<ul class="swe-badges" aria-label="<?php esc_attr_e( 'Tool highlights', 'speaker-water-ejector' ); ?>">
				<li><span class="swe-badge-icon" aria-hidden="true">✓</span><?php esc_html_e( 'No app needed', 'speaker-water-ejector' ); ?></li>
				<li><span class="swe-badge-icon" aria-hidden="true">✓</span><?php esc_html_e( 'Works in browser', 'speaker-water-ejector' ); ?></li>
				<li><span class="swe-badge-icon" aria-hidden="true">✓</span><?php esc_html_e( 'No signup', 'speaker-water-ejector' ); ?></li>
				<li><span class="swe-badge-icon" aria-hidden="true">✓</span><?php esc_html_e( 'Auto-stop timer', 'speaker-water-ejector' ); ?></li>
			</ul>

			<!-- ============ HERO TOOL CARD ============ -->
			<div class="swe-card swe-tool-card">

				<div class="swe-field-row">
					<label for="swe-mode-select" class="swe-label"><?php esc_html_e( 'Choose a mode', 'speaker-water-ejector' ); ?></label>
					<select id="swe-mode-select" class="swe-select" data-default="<?php echo esc_attr( $default_mode ); ?>">
						<option value="water"><?php esc_html_e( 'Water Eject Mode', 'speaker-water-ejector' ); ?></option>
						<option value="deep"><?php esc_html_e( 'Deep Vibration Mode', 'speaker-water-ejector' ); ?></option>
						<option value="gentle"><?php esc_html_e( 'Gentle Clean Mode', 'speaker-water-ejector' ); ?></option>
					</select>
				</div>

				<p class="swe-instructions">
					<?php esc_html_e( 'Turn your volume up, keep the speaker facing down, and remove your phone case if needed.', 'speaker-water-ejector' ); ?>
				</p>

				<div class="swe-dial-wrap">
					<div class="swe-progress-ring" aria-hidden="true">
						<svg viewBox="0 0 120 120" class="swe-ring-svg">
							<circle class="swe-ring-bg" cx="60" cy="60" r="52"></circle>
							<circle class="swe-ring-fg" id="swe-ring-fg" cx="60" cy="60" r="52"></circle>
						</svg>
					</div>
					<button
						type="button"
						id="swe-start-btn"
						class="swe-start-btn"
						aria-pressed="false"
						aria-label="<?php esc_attr_e( 'Start speaker water ejector sound', 'speaker-water-ejector' ); ?>"
					>
						<span class="swe-start-icon" id="swe-start-icon" aria-hidden="true">▶</span>
						<span class="swe-start-text" id="swe-start-text"><?php esc_html_e( 'Start', 'speaker-water-ejector' ); ?></span>
					</button>
				</div>

				<div class="swe-status-row">
					<span class="swe-status-label"><?php esc_html_e( 'Status:', 'speaker-water-ejector' ); ?></span>
					<span class="swe-status-value" id="swe-status-value"><?php esc_html_e( 'Ready', 'speaker-water-ejector' ); ?></span>
					<span class="swe-timer" id="swe-timer">0:30</span>
				</div>

				<p class="swe-warning" role="note">
					<strong><?php esc_html_e( 'Stop if the sound feels uncomfortable.', 'speaker-water-ejector' ); ?></strong>
				</p>

				<p class="swe-disclaimer">
					<?php esc_html_e( 'This browser tool is for basic speaker cleaning support only. It may help remove small amounts of trapped water using sound vibration. It does not repair physical damage, corrosion, or water damage inside the device.', 'speaker-water-ejector' ); ?>
				</p>

				<!-- Result card, hidden until a run completes -->
				<div class="swe-result-card" id="swe-result-card" hidden>
					<p class="swe-result-title"><?php esc_html_e( 'Sound completed', 'speaker-water-ejector' ); ?></p>
					<div class="swe-result-actions">
						<button type="button" class="swe-btn-secondary" id="swe-run-again"><?php esc_html_e( 'Run Again', 'speaker-water-ejector' ); ?></button>
						<a href="#swe-sound-test" class="swe-btn-secondary"><?php esc_html_e( 'Test My Speaker', 'speaker-water-ejector' ); ?></a>
						<a href="#swe-fix-tips" class="swe-btn-secondary"><?php esc_html_e( 'Read Fix Tips', 'speaker-water-ejector' ); ?></a>
					</div>
				</div>

				<p class="swe-audio-support-note" id="swe-audio-support-note" hidden>
					<?php esc_html_e( 'Your browser does not support the Web Audio API needed for this tool. Please try updating your browser or using a different one.', 'speaker-water-ejector' ); ?>
				</p>
			</div>
		</div>
	</section>

	<!-- ============ SPEAKER SOUND TEST ============ -->
	<section class="swe-section swe-card" id="swe-sound-test">
		<h2 class="swe-h2"><?php esc_html_e( 'Speaker Sound Test', 'speaker-water-ejector' ); ?></h2>
		<p class="swe-p"><?php esc_html_e( 'Check that both speaker channels are working before or after running the water ejector.', 'speaker-water-ejector' ); ?></p>

		<div class="swe-test-grid">
			<button type="button" class="swe-test-btn" data-test="left"><?php esc_html_e( 'Left Speaker', 'speaker-water-ejector' ); ?></button>
			<button type="button" class="swe-test-btn" data-test="right"><?php esc_html_e( 'Right Speaker', 'speaker-water-ejector' ); ?></button>
			<button type="button" class="swe-test-btn" data-test="both"><?php esc_html_e( 'Both Speakers', 'speaker-water-ejector' ); ?></button>
			<button type="button" class="swe-test-btn" data-test="high"><?php esc_html_e( 'High Sound', 'speaker-water-ejector' ); ?></button>
			<button type="button" class="swe-test-btn" data-test="low"><?php esc_html_e( 'Low Sound', 'speaker-water-ejector' ); ?></button>
		</div>

		<p class="swe-test-status" id="swe-test-status" aria-live="polite"></p>
	</section>

	<!-- ============ TROUBLESHOOTING QUIZ ============ -->
	<section class="swe-section swe-card" id="swe-quiz">
		<h2 class="swe-h2"><?php esc_html_e( 'What problem are you facing?', 'speaker-water-ejector' ); ?></h2>

		<div class="swe-quiz-options" role="radiogroup" aria-label="<?php esc_attr_e( 'Speaker problem', 'speaker-water-ejector' ); ?>">
			<button type="button" class="swe-quiz-btn" data-issue="muffled"><?php esc_html_e( 'Speaker sounds muffled', 'speaker-water-ejector' ); ?></button>
			<button type="button" class="swe-quiz-btn" data-issue="water"><?php esc_html_e( 'Speaker has water inside', 'speaker-water-ejector' ); ?></button>
			<button type="button" class="swe-quiz-btn" data-issue="oneside"><?php esc_html_e( 'One side is not working', 'speaker-water-ejector' ); ?></button>
			<button type="button" class="swe-quiz-btn" data-issue="lowvolume"><?php esc_html_e( 'Sound is too low', 'speaker-water-ejector' ); ?></button>
			<button type="button" class="swe-quiz-btn" data-issue="crackling"><?php esc_html_e( 'Crackling sound', 'speaker-water-ejector' ); ?></button>
		</div>

		<div class="swe-quiz-result" id="swe-quiz-result" hidden>
			<p class="swe-quiz-advice" id="swe-quiz-advice"></p>
			<button type="button" class="swe-btn-primary" id="swe-quiz-cta"></button>
		</div>
	</section>

	<?php if ( empty( $atts['minimal'] ) ) : ?>
<!-- ============ SEO CONTENT ============ -->
	<section class="swe-section swe-content" id="swe-fix-tips">
		<h2 class="swe-h2"><?php esc_html_e( 'How to Use the Speaker Water Ejector', 'speaker-water-ejector' ); ?></h2>
		<p class="swe-p">
			<?php esc_html_e( 'This tool plays a short, controlled sound through your phone speaker. The vibration from the sound may help push small drops of trapped water out of the speaker mesh. It works right in your browser, so there is nothing to install.', 'speaker-water-ejector' ); ?>
		</p>
		<ol class="swe-steps">
			<li><?php esc_html_e( 'Turn your phone volume up.', 'speaker-water-ejector' ); ?></li>
			<li><?php esc_html_e( 'Remove the phone case if it blocks the speaker.', 'speaker-water-ejector' ); ?></li>
			<li><?php esc_html_e( 'Place the phone speaker facing down.', 'speaker-water-ejector' ); ?></li>
			<li><?php esc_html_e( 'Choose a sound mode.', 'speaker-water-ejector' ); ?></li>
			<li><?php esc_html_e( 'Tap Start.', 'speaker-water-ejector' ); ?></li>
			<li><?php esc_html_e( 'Let the sound play for up to 30 seconds.', 'speaker-water-ejector' ); ?></li>
			<li><?php esc_html_e( 'Wipe away any visible water gently.', 'speaker-water-ejector' ); ?></li>
			<li><?php esc_html_e( 'Run the speaker test.', 'speaker-water-ejector' ); ?></li>
		</ol>

		<h2 class="swe-h2"><?php esc_html_e( 'When This Tool Can Help', 'speaker-water-ejector' ); ?></h2>
		<p class="swe-p"><?php esc_html_e( 'This tool is best for small, recent water exposure, such as a splash, light rain, or a quick drop in a puddle. It may also help loosen light dust after normal daily use.', 'speaker-water-ejector' ); ?></p>

		<h2 class="swe-h2"><?php esc_html_e( 'When It May Not Work', 'speaker-water-ejector' ); ?></h2>
		<p class="swe-p"><?php esc_html_e( 'If your phone was fully submerged for a long time, or if the sound has been muffled for weeks, the cause may be corrosion or internal hardware damage. In that case, this tool will not help, and you should speak with a repair technician.', 'speaker-water-ejector' ); ?></p>

		<h2 class="swe-h2"><?php esc_html_e( 'Tips to Fix a Muffled Phone Speaker', 'speaker-water-ejector' ); ?></h2>
		<ul class="swe-tips-list">
			<li><?php esc_html_e( 'Gently brush the speaker grille with a soft, dry brush.', 'speaker-water-ejector' ); ?></li>
			<li><?php esc_html_e( 'Check that your phone case is not covering the speaker holes.', 'speaker-water-ejector' ); ?></li>
			<li><?php esc_html_e( 'Restart your phone to rule out a software glitch.', 'speaker-water-ejector' ); ?></li>
			<li><?php esc_html_e( 'Run the Gentle Clean mode, then test both speaker channels.', 'speaker-water-ejector' ); ?></li>
		</ul>

		<!-- ============ FAQ ============ -->
		<h2 class="swe-h2"><?php esc_html_e( 'Frequently Asked Questions', 'speaker-water-ejector' ); ?></h2>
		<div class="swe-faq-list">
			<?php foreach ( $faqs as $faq ) : ?>
				<details class="swe-faq-item">
					<summary class="swe-faq-question"><?php echo esc_html( $faq['question'] ); ?></summary>
					<p class="swe-faq-answer"><?php echo esc_html( $faq['answer'] ); ?></p>
				</details>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- ============ RELATED INTERNAL LINKS ============ -->
	<section class="swe-section swe-related">
		<h2 class="swe-h2"><?php esc_html_e( 'Related Speaker Tools and Guides', 'speaker-water-ejector' ); ?></h2>
		<div class="swe-related-grid">
			<a class="swe-related-card" href="/speaker-sound-test/">
				<span class="swe-related-title"><?php esc_html_e( 'Speaker Sound Test', 'speaker-water-ejector' ); ?></span>
			</a>
			<a class="swe-related-card" href="/left-right-speaker-test/">
				<span class="swe-related-title"><?php esc_html_e( 'Left Right Speaker Test', 'speaker-water-ejector' ); ?></span>
			</a>
			<a class="swe-related-card" href="/why-is-my-phone-speaker-muffled/">
				<span class="swe-related-title"><?php esc_html_e( 'Why Is My Phone Speaker Muffled?', 'speaker-water-ejector' ); ?></span>
			</a>
			<a class="swe-related-card" href="/how-to-remove-water-from-phone-speaker/">
				<span class="swe-related-title"><?php esc_html_e( 'How to Remove Water From Phone Speaker', 'speaker-water-ejector' ); ?></span>
			</a>
			<a class="swe-related-card" href="/phone-speaker-cleaner/">
				<span class="swe-related-title"><?php esc_html_e( 'Phone Speaker Cleaner', 'speaker-water-ejector' ); ?></span>
			</a>
		</div>
	</section>

	<?php endif; ?>

</div>
