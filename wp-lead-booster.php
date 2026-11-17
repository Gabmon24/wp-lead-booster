<?php
/**
 * Plugin Name:       WP Lead Booster — WhatsApp & Conversion Suite
 * Plugin URI:        https://github.com/Gabmon24/wp-lead-booster
 * Description:       Suite ultraligera y modular de alta conversión para WordPress. Integra botón flotante multi-agente de WhatsApp, barra de anuncios con temporizador de urgencia y tracking automático de eventos para Google Analytics 4 y Meta Pixel con cero dependencias externas y optimizado para Core Web Vitals.
 * Version:           1.0.0
 * Author:            Gabriel Montiel Chamorro (@Gabmon24)
 * Author URI:        https://github.com/Gabmon24
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-lead-booster
 * Requires at least: 5.8
 * Requires PHP:      7.4
 */

defined('ABSPATH') || exit;

define('WPLB_VERSION', '1.0.0');
define('WPLB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPLB_PLUGIN_URL', plugin_dir_url(__FILE__));

final class WP_Lead_Booster {

    private static $instance = null;
    private $options;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->options = get_option('wplb_settings', $this->default_options());
        
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);

        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('wp_footer', [$this, 'render_frontend_elements']);
    }

    public function default_options() {
        return [
            'enabled_whatsapp'   => 1,
            'whatsapp_phone'     => '+573207132977',
            'agent_name'         => 'Atención al Cliente',
            'agent_title'        => 'En línea • Respuesta en minutos',
            'welcome_message'    => '¡Hola! 👋 ¿En qué podemos ayudarte hoy?',
            'preset_message'     => 'Hola, me gustaría recibir más información sobre sus servicios.',
            'button_position'    => 'right', // 'right' or 'left'
            'enabled_banner'     => 1,
            'banner_text'        => '🚀 ¡Oferta Especial por Tiempo Limitado! 20% de Descuento en tu primer proyecto.',
            'banner_btn_text'    => 'Hablar con un Asesor',
            'banner_countdown'   => 1,
            'banner_hours'       => 24,
            'track_ga4'          => 1,
            'track_meta'         => 1,
        ];
    }

    /* -------------------------------------------------------------------------
     * ADMIN DASHBOARD
     * ------------------------------------------------------------------------- */

    public function add_admin_menu() {
        add_options_page(
            __('WP Lead Booster', 'wp-lead-booster'),
            __('Lead Booster 🚀', 'wp-lead-booster'),
            'manage_options',
            'wp-lead-booster',
            [$this, 'render_admin_page']
        );
    }

    public function register_settings() {
        register_setting('wplb_settings_group', 'wplb_settings', [
            'type'              => 'array',
            'sanitize_callback' => [$this, 'sanitize_settings'],
            'default'           => $this->default_options()
        ]);
    }

    public function sanitize_settings($input) {
        $sanitized = [];
        $sanitized['enabled_whatsapp'] = isset($input['enabled_whatsapp']) ? 1 : 0;
        $sanitized['whatsapp_phone']   = preg_replace('/[^0-9+]/', '', $input['whatsapp_phone'] ?? '');
        $sanitized['agent_name']       = sanitize_text_field($input['agent_name'] ?? '');
        $sanitized['agent_title']      = sanitize_text_field($input['agent_title'] ?? '');
        $sanitized['welcome_message']  = sanitize_textarea_field($input['welcome_message'] ?? '');
        $sanitized['preset_message']   = sanitize_text_field($input['preset_message'] ?? '');
        $sanitized['button_position']  = in_array($input['button_position'] ?? '', ['right', 'left'], true) ? $input['button_position'] : 'right';

        $sanitized['enabled_banner']   = isset($input['enabled_banner']) ? 1 : 0;
        $sanitized['banner_text']      = sanitize_text_field($input['banner_text'] ?? '');
        $sanitized['banner_btn_text']  = sanitize_text_field($input['banner_btn_text'] ?? '');
        $sanitized['banner_countdown'] = isset($input['banner_countdown']) ? 1 : 0;
        $sanitized['banner_hours']     = absint($input['banner_hours'] ?? 24);

        $sanitized['track_ga4']        = isset($input['track_ga4']) ? 1 : 0;
        $sanitized['track_meta']       = isset($input['track_meta']) ? 1 : 0;

        return $sanitized;
    }

    public function enqueue_admin_assets($hook) {
        if ($hook !== 'settings_page_wp-lead-booster') {
            return;
        }
        wp_enqueue_style('wplb-admin-css', WPLB_PLUGIN_URL . 'assets/css/admin.css', [], WPLB_VERSION);
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $opts = get_option('wplb_settings', $this->default_options());
        ?>
        <div class="wrap wplb-admin-container">
            <div class="wplb-header">
                <h1>🚀 WP Lead Booster <span class="wplb-version">v<?php echo esc_html(WPLB_VERSION); ?></span></h1>
                <p><?php esc_html_e('Optimización de conversiones, WhatsApp directo y avisos de urgencia sin ralentizar tu web.', 'wp-lead-booster'); ?></p>
            </div>

            <form method="post" action="options.php" class="wplb-form">
                <?php
                settings_fields('wplb_settings_group');
                ?>

                <!-- SECCIÓN 1: WHATSAPP FLOTANTE -->
                <div class="wplb-card">
                    <div class="wplb-card-header">
                        <h2>💬 Botón Flotante de WhatsApp</h2>
                        <label class="wplb-switch">
                            <input type="checkbox" name="wplb_settings[enabled_whatsapp]" value="1" <?php checked(1, $opts['enabled_whatsapp'] ?? 1); ?>>
                            <span class="wplb-slider"></span>
                        </label>
                    </div>
                    <div class="wplb-card-body">
                        <div class="wplb-grid-2">
                            <div class="wplb-field">
                                <label for="wplb_phone"><?php esc_html_e('Número de WhatsApp (con código de país):', 'wp-lead-booster'); ?></label>
                                <input type="text" id="wplb_phone" name="wplb_settings[whatsapp_phone]" value="<?php echo esc_attr($opts['whatsapp_phone'] ?? ''); ?>" placeholder="+573207132977" class="regular-text">
                                <small><?php esc_html_e('Ejemplo: +573207132977 (Colombia)', 'wp-lead-booster'); ?></small>
                            </div>
                            <div class="wplb-field">
                                <label for="wplb_agent_name"><?php esc_html_e('Nombre del Asesor o Equipo:', 'wp-lead-booster'); ?></label>
                                <input type="text" id="wplb_agent_name" name="wplb_settings[agent_name]" value="<?php echo esc_attr($opts['agent_name'] ?? ''); ?>" class="regular-text">
                            </div>
                        </div>

                        <div class="wplb-grid-2">
                            <div class="wplb-field">
                                <label for="wplb_agent_title"><?php esc_html_e('Subtítulo del Asesor:', 'wp-lead-booster'); ?></label>
                                <input type="text" id="wplb_agent_title" name="wplb_settings[agent_title]" value="<?php echo esc_attr($opts['agent_title'] ?? ''); ?>" class="regular-text">
                            </div>
                            <div class="wplb-field">
                                <label for="wplb_position"><?php esc_html_e('Posición en Pantalla:', 'wp-lead-booster'); ?></label>
                                <select id="wplb_position" name="wplb_settings[button_position]">
                                    <option value="right" <?php selected('right', $opts['button_position'] ?? 'right'); ?>><?php esc_html_e('Inferior Derecha (Recomendado)', 'wp-lead-booster'); ?></option>
                                    <option value="left" <?php selected('left', $opts['button_position'] ?? 'right'); ?>><?php esc_html_e('Inferior Izquierda', 'wp-lead-booster'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="wplb-field">
                            <label for="wplb_preset"><?php esc_html_e('Mensaje Predeterminado del Usuario:', 'wp-lead-booster'); ?></label>
                            <input type="text" id="wplb_preset" name="wplb_settings[preset_message]" value="<?php echo esc_attr($opts['preset_message'] ?? ''); ?>" class="large-text">
                            <small><?php esc_html_e('El texto con el que iniciará el chat en WhatsApp al hacer clic.', 'wp-lead-booster'); ?></small>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2: BANNER DE URGENCIA -->
                <div class="wplb-card">
                    <div class="wplb-card-header">
                        <h2>⚡ Barra de Anuncios & Conversión</h2>
                        <label class="wplb-switch">
                            <input type="checkbox" name="wplb_settings[enabled_banner]" value="1" <?php checked(1, $opts['enabled_banner'] ?? 1); ?>>
                            <span class="wplb-slider"></span>
                        </label>
                    </div>
                    <div class="wplb-card-body">
                        <div class="wplb-field">
                            <label for="wplb_banner_text"><?php esc_html_e('Texto del Anuncio:', 'wp-lead-booster'); ?></label>
                            <input type="text" id="wplb_banner_text" name="wplb_settings[banner_text]" value="<?php echo esc_attr($opts['banner_text'] ?? ''); ?>" class="large-text">
                        </div>
                        <div class="wplb-grid-2">
                            <div class="wplb-field">
                                <label for="wplb_banner_btn"><?php esc_html_e('Texto del Botón CTA:', 'wp-lead-booster'); ?></label>
                                <input type="text" id="wplb_banner_btn" name="wplb_settings[banner_btn_text]" value="<?php echo esc_attr($opts['banner_btn_text'] ?? ''); ?>" class="regular-text">
                            </div>
                            <div class="wplb-field">
                                <label>
                                    <input type="checkbox" name="wplb_settings[banner_countdown]" value="1" <?php checked(1, $opts['banner_countdown'] ?? 1); ?>>
                                    <?php esc_html_e('Mostrar Temporizador Regresivo (Urgencia)', 'wp-lead-booster'); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 3: ANALÍTICA Y TRACKING -->
                <div class="wplb-card">
                    <div class="wplb-card-header">
                        <h2>📊 Analítica Automática (Sin Código)</h2>
                    </div>
                    <div class="wplb-card-body">
                        <p><?php esc_html_e('Dispara eventos personalizados automáticamente para medir el retorno de inversión publicitaria.', 'wp-lead-booster'); ?></p>
                        <label class="wplb-checkbox-label">
                            <input type="checkbox" name="wplb_settings[track_ga4]" value="1" <?php checked(1, $opts['track_ga4'] ?? 1); ?>>
                            <?php esc_html_e('Disparar evento "generate_lead" hacia Google Analytics 4 (gtag.js)', 'wp-lead-booster'); ?>
                        </label>
                        <br><br>
                        <label class="wplb-checkbox-label">
                            <input type="checkbox" name="wplb_settings[track_meta]" value="1" <?php checked(1, $opts['track_meta'] ?? 1); ?>>
                            <?php esc_html_e('Disparar evento "Lead" hacia Meta Pixel (Facebook Ads - fbq)', 'wp-lead-booster'); ?>
                        </label>
                    </div>
                </div>

                <?php submit_button(__('Guardar Cambios de Conversión 🚀', 'wp-lead-booster'), 'primary large'); ?>
            </form>
        </div>
        <?php
    }

    /* -------------------------------------------------------------------------
     * FRONTEND RENDERING
     * ------------------------------------------------------------------------- */

    public function enqueue_frontend_assets() {
        wp_enqueue_style('wplb-frontend-css', WPLB_PLUGIN_URL . 'assets/css/frontend.css', [], WPLB_VERSION);
        wp_enqueue_script('wplb-frontend-js', WPLB_PLUGIN_URL . 'assets/js/frontend.js', [], WPLB_VERSION, true);

        $opts = get_option('wplb_settings', $this->default_options());
        wp_localize_script('wplb-frontend-js', 'wplbData', [
            'phone'         => esc_js($opts['whatsapp_phone'] ?? ''),
            'preset'        => esc_js($opts['preset_message'] ?? ''),
            'trackGA4'      => !empty($opts['track_ga4']),
            'trackMeta'     => !empty($opts['track_meta']),
            'countdown'     => !empty($opts['banner_countdown']),
            'bannerHours'   => absint($opts['banner_hours'] ?? 24),
        ]);
    }

    public function render_frontend_elements() {
        $opts = get_option('wplb_settings', $this->default_options());

        // 1. Render Banner de Anuncios
        if (!empty($opts['enabled_banner']) && !empty($opts['banner_text'])) {
            ?>
            <div id="wplb-announcement-bar" class="wplb-bar" role="banner">
                <div class="wplb-bar-inner">
                    <span class="wplb-bar-text"><?php echo esc_html($opts['banner_text']); ?></span>
                    <?php if (!empty($opts['banner_countdown'])): ?>
                        <div id="wplb-countdown" class="wplb-countdown" aria-label="Tiempo restante">
                            <span class="wplb-time-unit"><strong id="wplb-hours">00</strong>h</span>
                            <span class="wplb-time-unit"><strong id="wplb-mins">00</strong>m</span>
                            <span class="wplb-time-unit"><strong id="wplb-secs">00</strong>s</span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($opts['banner_btn_text'])): ?>
                        <button id="wplb-bar-cta" class="wplb-bar-cta"><?php echo esc_html($opts['banner_btn_text']); ?></button>
                    <?php endif; ?>
                    <button id="wplb-bar-close" class="wplb-bar-close" aria-label="Cerrar aviso">&times;</button>
                </div>
            </div>
            <?php
        }

        // 2. Render Botón Flotante de WhatsApp
        if (!empty($opts['enabled_whatsapp']) && !empty($opts['whatsapp_phone'])) {
            $pos = esc_attr($opts['button_position'] ?? 'right');
            ?>
            <div id="wplb-widget" class="wplb-widget pos-<?php echo $pos; ?>">
                <!-- Ventana Modal de Chat -->
                <div id="wplb-chat-box" class="wplb-box" aria-hidden="true">
                    <div class="wplb-box-header">
                        <div class="wplb-avatar">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C6.48 2 2 6.48 2 12c0 1.82.49 3.53 1.34 5L2 22l5.16-1.31C8.59 21.49 10.25 22 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2zm0 18c-1.57 0-3.04-.44-4.3-1.2l-.31-.18-3.07.78.82-2.98-.2-.33C4.38 14.86 4 13.48 4 12c0-4.41 3.59-8 8-8s8 3.59 8 8-3.59 8-8 8z"/>
                            </svg>
                        </div>
                        <div class="wplb-box-title">
                            <h4><?php echo esc_html($opts['agent_name'] ?? 'Asesor en Línea'); ?></h4>
                            <p><?php echo esc_html($opts['agent_title'] ?? 'Respuesta rápida'); ?></p>
                        </div>
                        <button id="wplb-box-close" class="wplb-close" aria-label="Cerrar chat">&times;</button>
                    </div>
                    <div class="wplb-box-body">
                        <div class="wplb-bubble">
                            <p><?php echo nl2br(esc_html($opts['welcome_message'] ?? '¡Hola! ¿En qué podemos ayudarte?')); ?></p>
                            <span class="wplb-bubble-time"><?php echo date('H:i'); ?></span>
                        </div>
                    </div>
                    <div class="wplb-box-footer">
                        <button id="wplb-start-chat" class="wplb-cta-btn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2Z"/>
                            </svg>
                            <?php esc_html_e('Iniciar Chat en WhatsApp', 'wp-lead-booster'); ?>
                        </button>
                    </div>
                </div>

                <!-- Botón Gatillo Flotante -->
                <button id="wplb-trigger-btn" class="wplb-btn" aria-label="Abrir WhatsApp">
                    <span class="wplb-pulse"></span>
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="white">
                        <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C16.6 3.67 20.28 7.36 20.28 11.91C20.28 16.47 16.6 20.16 12.05 20.16C10.66 20.16 9.3 19.8 8.1 19.09L7.81 18.92L4.7 19.74L5.53 16.71L5.34 16.41C4.56 15.17 4.15 13.56 4.15 11.91C4.15 7.36 7.83 3.67 12.05 3.67Z"/>
                    </svg>
                </button>
            </div>
            <?php
        }
    }
}

// Iniciar plugin
add_action('plugins_loaded', ['WP_Lead_Booster', 'instance']);
