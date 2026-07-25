<?php
/**
 * Plugin Name: Speaker Water Ejector Tool
 * Plugin URI: https://example.com/
 * Description: A browser-based speaker water ejector, speaker sound test, and troubleshooting quiz. Adds the [speaker_water_ejector] shortcode. Built for Kadence + Rank Math + LiteSpeed Cache.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com/
 * License: GPL v2 or later
 * Text Domain: speaker-water-ejector
 *
 * ---------------------------------------------------------------------
 * SCHEMA NOTE FOR RANK MATH USERS:
 * This plugin outputs its own JSON-LD (WebApplication + HowTo, and
 * optionally FAQPage) so the tool page has structured data even if
 * Rank Math's schema generator isn't configured for this page.
 *
 * If you already build FAQ schema manually inside Rank Math's
 * "Schema" tab for this specific page, you should NOT run both --
 * duplicate FAQPage schema on one URL can confuse Google and, in some
 * cases, cause rich result eligibility to be dropped for that page.
 *
 * To disable ONLY this plugin's FAQ schema (and keep WebApplication +
 * HowTo), add this to your theme's functions.php or a site-specific
 * plugin:
 *
 *     add_filter( 'swe_tool_disable_faq_schema', '__return_true' );
 *
 * To disable ALL schema output from this plugin:
 *
 *     add_filter( 'swe_tool_disable_all_schema', '__return_true' );
 * ---------------------------------------------------------------------
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'SWE_TOOL_VERSION', '1.0.0' );
define( 'SWE_TOOL_PATH', plugin_dir_path( __FILE__ ) );
define( 'SWE_TOOL_URL', plugin_dir_url( __FILE__ ) );

/**
 * Flag so we only enqueue assets + print schema on pages that actually
 * render the shortcode (checked during 'the_content' via has_shortcode(),
 * with a safe fallback for widgets/template tags).
 */
class SWE_Water_Ejector_Tool {

	/**
	 * Whether the shortcode has been rendered on the current request.
	 *
	 * @var bool
	 */
	private static $rendered = false;

	public static function init() {
		add_shortcode( 'speaker_water_ejector', array( __CLASS__, 'render_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue_assets' ) );
		add_action( 'wp_footer', array( __CLASS__, 'maybe_print_schema' ) );
	}

	/**
	 * Only enqueue CSS/JS when the current post's content actually
	 * contains the shortcode. This keeps the tool lightweight for
	 * Core Web Vitals on every other page of the site.
	 */
	public static function maybe_enqueue_assets() {
		if ( ! is_singular() ) {
			return;
		}

		global $post;

		if ( ! $post instanceof WP_Post ) {
			return;
		}

		if ( ! has_shortcode( $post->post_content, 'speaker_water_ejector' ) ) {
			return;
		}

		wp_enqueue_style(
			'swe-tool-style',
			SWE_TOOL_URL . 'assets/speaker-water-ejector.css',
			array(),
			SWE_TOOL_VERSION
		);

		// Loaded in the footer, deferred, single file, no jQuery dependency.
		wp_enqueue_script(
			'swe-tool-script',
			SWE_TOOL_URL . 'assets/speaker-water-ejector.js',
			array(),
			SWE_TOOL_VERSION,
			true
		);

		wp_script_add_data( 'swe-tool-script', 'strategy', 'defer' );
	}

	/**
	 * Shortcode callback. Outputs the full tool markup.
	 *
	 * @param array $atts Shortcode attributes (currently unused, reserved
	 *                    for future options like default mode).
	 * @return string
	 */
	public static function render_shortcode( $atts = array() ) {
		self::$rendered = true;

		$atts = shortcode_atts(
			array(
				'default_mode' => 'water',
				'minimal'      => false,
			),
			$atts,
			'speaker_water_ejector'
		);

		ob_start();
		include SWE_TOOL_PATH . 'templates/tool-template.php';
		return ob_get_clean();
	}

	/**
	 * Prints JSON-LD schema in the footer, only if the shortcode rendered
	 * and the site owner hasn't disabled it via filters.
	 */
	public static function maybe_print_schema() {
		if ( ! self::$rendered ) {
			return;
		}

		if ( apply_filters( 'swe_tool_disable_all_schema', false ) ) {
			return;
		}

		$page_url  = esc_url( get_permalink() );
		$site_name = esc_html( get_bloginfo( 'name' ) );

		$schema_graph = array();

		// 1. WebApplication schema.
		$schema_graph[] = array(
			'@type'                 => 'WebApplication',
			'name'                  => 'Speaker Water Ejector Tool',
			'url'                   => $page_url,
			'applicationCategory'   => 'UtilitiesApplication',
			'operatingSystem'       => 'Any (runs in browser)',
			'browserRequirements'   => 'Requires JavaScript and Web Audio API support.',
			'isAccessibleForFree'   => true,
			'offers'                => array(
				'@type' => 'Offer',
				'price' => '0',
				'priceCurrency' => 'USD',
			),
			'description'           => 'A free browser-based tool that plays controlled sound vibrations that may help remove trapped water from a phone speaker, plus a speaker sound test and troubleshooting quiz.',
			'publisher'             => array(
				'@type' => 'Organization',
				'name'  => $site_name,
			),
		);

		// 2. HowTo schema.
		$schema_graph[] = array(
			'@type'            => 'HowTo',
			'name'             => 'How to Use the Speaker Water Ejector Tool',
			'description'      => 'Steps to run the browser-based speaker water ejector tool.',
			'totalTime'        => 'PT1M',
			'step'             => array(
				array(
					'@type'    => 'HowToStep',
					'position' => 1,
					'text'     => 'Turn your phone volume up.',
				),
				array(
					'@type'    => 'HowToStep',
					'position' => 2,
					'text'     => 'Remove the phone case if it blocks the speaker.',
				),
				array(
					'@type'    => 'HowToStep',
					'position' => 3,
					'text'     => 'Place the phone speaker facing down.',
				),
				array(
					'@type'    => 'HowToStep',
					'position' => 4,
					'text'     => 'Choose a sound mode.',
				),
				array(
					'@type'    => 'HowToStep',
					'position' => 5,
					'text'     => 'Tap Start.',
				),
				array(
					'@type'    => 'HowToStep',
					'position' => 6,
					'text'     => 'Let the sound play for up to 30 seconds.',
				),
				array(
					'@type'    => 'HowToStep',
					'position' => 7,
					'text'     => 'Wipe away any visible water gently.',
				),
				array(
					'@type'    => 'HowToStep',
					'position' => 8,
					'text'     => 'Run the speaker test.',
				),
			),
		);

		// 3. FAQPage schema (skippable via filter if Rank Math handles FAQ for this page).
		if ( ! apply_filters( 'swe_tool_disable_faq_schema', false ) ) {
			$faqs = self::get_faq_items();

			$faq_entities = array();
			foreach ( $faqs as $faq ) {
				$faq_entities[] = array(
					'@type'          => 'Question',
					'name'           => $faq['question'],
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => $faq['answer'],
					),
				);
			}

			$schema_graph[] = array(
				'@type'      => 'FAQPage',
				'mainEntity' => $faq_entities,
			);
		}

		$schema = array(
			'@context' => 'https://schema.org',
			'@graph'   => $schema_graph,
		);

		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Central FAQ list. Shared between the visible FAQ markup and the
	 * FAQPage schema so the two never drift out of sync.
	 *
	 * @return array[]
	 */
	public static function get_faq_items() {
		return array(
			array(
				'question' => 'Does this speaker water ejector really work?',
				'answer'   => 'It may help move small amounts of trapped water out of the speaker mesh using sound vibration. It is not guaranteed to work every time, and it will not fix corrosion or hardware damage.',
			),
			array(
				'question' => 'Is this tool safe for my phone?',
				'answer'   => 'The tool uses normal audio playback at a safe volume, the same as playing music or a video. Stop right away if the sound feels uncomfortable or your device gets warm.',
			),
			array(
				'question' => 'How long should I run the sound?',
				'answer'   => 'Each mode runs for about 30 seconds and then stops automatically. You can run it again if needed, with a short break in between.',
			),
			array(
				'question' => 'Can this fix a damaged speaker?',
				'answer'   => 'No. This tool only targets minor trapped water or dust. It cannot repair a physically damaged, corroded, or internally water-damaged speaker.',
			),
			array(
				'question' => 'Should I use it after dropping my phone in water?',
				'answer'   => 'First dry the outside of the phone and avoid charging it. Once you are sure the phone is otherwise safe to use, you can try this tool for minor speaker water. If the phone was submerged for a long time, get it checked by a repair technician first.',
			),
			array(
				'question' => 'Why does my speaker still sound muffled?',
				'answer'   => 'Muffled sound can also be caused by dust, lint, a stuck speaker grille, or software volume limits, not just water. Try the Gentle Clean mode and check your phone case for blocked speaker holes.',
			),
			array(
				'question' => 'Does it work on iPhone and Android?',
				'answer'   => 'Yes. It runs in your mobile browser using the Web Audio API, which works on modern versions of Chrome, Safari, and other major mobile browsers.',
			),
			array(
				'question' => 'Do I need to install an app?',
				'answer'   => 'No. The tool works directly on this page. There is nothing to download and no account to create.',
			),
			array(
				'question' => 'What volume should I use?',
				'answer'   => 'Turn your volume up to a comfortable but strong level before starting. Very low volume may not create enough vibration to help move water.',
			),
			array(
				'question' => 'Can I run the sound more than once?',
				'answer'   => 'Yes. You can run any mode multiple times. If you do not see improvement after a few tries, the issue may not be water, and you should try the speaker test or contact a repair technician.',
			),
		);
	}
}

add_action( 'plugins_loaded', array( 'SWE_Water_Ejector_Tool', 'init' ) );
