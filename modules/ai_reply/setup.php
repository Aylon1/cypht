<?php

/**
 * AI Reply module
 * @package modules
 * @subpackage ai_reply
 */

if (!defined('DEBUG_MODE')) { die(); }

handler_source('ai_reply');
output_source('ai_reply');

/* Settings page */
add_handler('settings', 'process_ai_reply_provider_setting', true, 'ai_reply', 'save_user_settings', 'before');
add_handler('settings', 'process_ai_reply_api_url_setting', true, 'ai_reply', 'save_user_settings', 'before');
add_handler('settings', 'process_ai_reply_api_key_setting', true, 'ai_reply', 'save_user_settings', 'before');
add_handler('settings', 'process_ai_reply_model_setting', true, 'ai_reply', 'save_user_settings', 'before');
add_handler('settings', 'process_ai_reply_system_prompt_setting', true, 'ai_reply', 'save_user_settings', 'before');

add_output('settings', 'start_ai_reply_settings', true, 'ai_reply', 'start_general_settings', 'after');
add_output('settings', 'ai_reply_provider_setting', true, 'ai_reply', 'start_ai_reply_settings', 'after');
add_output('settings', 'ai_reply_api_url_setting', true, 'ai_reply', 'ai_reply_provider_setting', 'after');
add_output('settings', 'ai_reply_api_key_setting', true, 'ai_reply', 'ai_reply_api_url_setting', 'after');
add_output('settings', 'ai_reply_model_setting', true, 'ai_reply', 'ai_reply_api_key_setting', 'after');
add_output('settings', 'ai_reply_system_prompt_setting', true, 'ai_reply', 'ai_reply_model_setting', 'after');

/* Compose page */
add_output('compose', 'ai_reply_compose_buttons', true, 'ai_reply', 'compose_form_content', 'after');

/* AJAX handler for AI generation */
setup_base_ajax_page('ajax_ai_generate', 'core');
add_handler('ajax_ai_generate', 'load_ai_reply_settings', true, 'ai_reply', 'load_user_data', 'after');
add_handler('ajax_ai_generate', 'process_ai_generate_request', true, 'ai_reply', 'load_ai_reply_settings', 'after');

return array(
    'allowed_pages' => array(
        'ajax_ai_generate'
    ),
    'allowed_output' => array(
        'ai_generated_text' => array(FILTER_UNSAFE_RAW, false),
        'ai_reply_provider' => array(FILTER_UNSAFE_RAW, false),
        'ai_reply_api_url' => array(FILTER_UNSAFE_RAW, false),
        'ai_reply_model' => array(FILTER_UNSAFE_RAW, false),
        'ai_reply_system_prompt' => array(FILTER_UNSAFE_RAW, false)
    ),
    'allowed_post' => array(
        'ai_reply_provider' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'ai_reply_api_url' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'ai_reply_api_key' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'ai_reply_model' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'ai_reply_system_prompt' => FILTER_UNSAFE_RAW,
        'ai_prompt' => FILTER_UNSAFE_RAW,
        'ai_context' => FILTER_UNSAFE_RAW,
        'ai_mode' => FILTER_SANITIZE_FULL_SPECIAL_CHARS
    )
);