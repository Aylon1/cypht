<?php

/**
 * AI Reply modules
 * @package modules
 * @subpackage ai_reply
 */

if (!defined('DEBUG_MODE')) { die(); }

/**
 * Load AI Reply settings for AJAX requests
 * @subpackage ai_reply/handler
 */
class Hm_Handler_load_ai_reply_settings extends Hm_Handler_Module {
    public function process() {
        $settings = $this->user_config;
        $this->out('ai_reply_provider', $settings->get('ai_reply_provider_setting', 'ollama'));
        $this->out('ai_reply_api_url', $settings->get('ai_reply_api_url_setting', 'http://localhost:11434'));
        $this->out('ai_reply_api_key', $settings->get('ai_reply_api_key_setting', ''));
        $this->out('ai_reply_model', $settings->get('ai_reply_model_setting', 'llama2'));
        $this->out('ai_reply_system_prompt', $settings->get('ai_reply_system_prompt_setting', 'You are a helpful email assistant. Generate professional and concise email responses.'));
    }
}

/**
 * Process AI generation request
 * @subpackage ai_reply/handler
 */
class Hm_Handler_process_ai_generate_request extends Hm_Handler_Module {
    public function process() {
        list($success, $form) = $this->process_form(array('ai_mode'));
        
        if (!$success) {
            $this->out('ai_generated_text', '');
            Hm_Msgs::add('Invalid request', 'danger');
            return;
        }

        $provider = $this->get('ai_reply_provider', 'ollama');
        $api_url = $this->get('ai_reply_api_url', 'http://localhost:11434');
        $api_key = $this->get('ai_reply_api_key', '');
        $model = $this->get('ai_reply_model', 'llama2');
        $system_prompt = $this->get('ai_reply_system_prompt', 'You are a helpful email assistant.');
        
        $mode = $form['ai_mode']; // 'reply' or 'prompt'
        $user_prompt = '';
        
        if ($mode === 'prompt' && array_key_exists('ai_prompt', $this->request->post)) {
            $user_prompt = $this->request->post['ai_prompt'];
        } elseif ($mode === 'reply' && array_key_exists('ai_context', $this->request->post)) {
            $context = $this->request->post['ai_context'];
            $user_prompt = "Generate a professional reply to the following email:\n\n" . $context;
        } else {
            $this->out('ai_generated_text', '');
            Hm_Msgs::add('Missing prompt or context', 'danger');
            return;
        }

        try {
            $generated_text = $this->call_llm_api($provider, $api_url, $api_key, $model, $system_prompt, $user_prompt);
            $this->out('ai_generated_text', $generated_text);
        } catch (Exception $e) {
            Hm_Msgs::add('AI generation failed: ' . $e->getMessage(), 'danger');
            $this->out('ai_generated_text', '');
        }
    }

    /**
     * Call LLM API (Ollama or OpenAI compatible)
     */
    private function call_llm_api($provider, $api_url, $api_key, $model, $system_prompt, $user_prompt) {
        $api_url = rtrim($api_url, '/');
        
        if ($provider === 'ollama') {
            return $this->call_ollama($api_url, $model, $system_prompt, $user_prompt);
        } else {
            return $this->call_openai_compatible($api_url, $api_key, $model, $system_prompt, $user_prompt);
        }
    }

    /**
     * Call Ollama API
     */
    private function call_ollama($api_url, $model, $system_prompt, $user_prompt) {
        $endpoint = $api_url . '/api/generate';
        
        $data = array(
            'model' => $model,
            'prompt' => $user_prompt,
            'system' => $system_prompt,
            'stream' => false
        );

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('Ollama API error: ' . $error);
        }
        
        curl_close($ch);

        if ($http_code !== 200) {
            throw new Exception('Ollama API returned status ' . $http_code);
        }

        $result = json_decode($response, true);
        
        if (!$result || !isset($result['response'])) {
            throw new Exception('Invalid response from Ollama API');
        }

        return $result['response'];
    }

    /**
     * Call OpenAI-compatible API
     */
    private function call_openai_compatible($api_url, $api_key, $model, $system_prompt, $user_prompt) {
        $endpoint = $api_url . '/v1/chat/completions';
        
        $data = array(
            'model' => $model,
            'messages' => array(
                array('role' => 'system', 'content' => $system_prompt),
                array('role' => 'user', 'content' => $user_prompt)
            ),
            'temperature' => 0.7,
            'max_tokens' => 1000
        );

        $headers = array(
            'Content-Type: application/json'
        );
        
        if (!empty($api_key)) {
            $headers[] = 'Authorization: Bearer ' . $api_key;
        }

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('OpenAI API error: ' . $error);
        }
        
        curl_close($ch);

        if ($http_code !== 200) {
            throw new Exception('OpenAI API returned status ' . $http_code . ': ' . $response);
        }

        $result = json_decode($response, true);
        
        if (!$result || !isset($result['choices'][0]['message']['content'])) {
            throw new Exception('Invalid response from OpenAI API');
        }

        return $result['choices'][0]['message']['content'];
    }
}

/**
 * Process AI Reply provider setting
 * @subpackage ai_reply/handler
 */
class Hm_Handler_process_ai_reply_provider_setting extends Hm_Handler_Module {
    public function process() {
        function ai_reply_provider_callback($val) {
            return in_array($val, array('ollama', 'openai')) ? $val : 'ollama';
        }
        process_site_setting('ai_reply_provider', $this, 'ai_reply_provider_callback', 'ollama');
    }
}

/**
 * Process AI Reply API URL setting
 * @subpackage ai_reply/handler
 */
class Hm_Handler_process_ai_reply_api_url_setting extends Hm_Handler_Module {
    public function process() {
        function ai_reply_api_url_callback($val) {
            return $val;
        }
        process_site_setting('ai_reply_api_url', $this, 'ai_reply_api_url_callback', 'http://localhost:11434');
    }
}

/**
 * Process AI Reply API key setting
 * @subpackage ai_reply/handler
 */
class Hm_Handler_process_ai_reply_api_key_setting extends Hm_Handler_Module {
    public function process() {
        function ai_reply_api_key_callback($val) {
            return $val;
        }
        process_site_setting('ai_reply_api_key', $this, 'ai_reply_api_key_callback', '');
    }
}

/**
 * Process AI Reply model setting
 * @subpackage ai_reply/handler
 */
class Hm_Handler_process_ai_reply_model_setting extends Hm_Handler_Module {
    public function process() {
        function ai_reply_model_callback($val) {
            return $val;
        }
        process_site_setting('ai_reply_model', $this, 'ai_reply_model_callback', 'llama2');
    }
}

/**
 * Process AI Reply system prompt setting
 * @subpackage ai_reply/handler
 */
class Hm_Handler_process_ai_reply_system_prompt_setting extends Hm_Handler_Module {
    public function process() {
        function ai_reply_system_prompt_callback($val) {
            return $val;
        }
        process_site_setting('ai_reply_system_prompt', $this, 'ai_reply_system_prompt_callback', 
            'You are a helpful email assistant. Generate professional and concise email responses.');
    }
}

/**
 * Start AI Reply settings section
 * @subpackage ai_reply/output
 */
class Hm_Output_start_ai_reply_settings extends Hm_Output_Module {
    protected function output() {
        return '<tr><td data-target=".ai_reply_setting" colspan="2" class="settings_subtitle cursor-pointer border-bottom p-2">
            <i class="bi bi-robot fs-5 me-2"></i>
            ' . $this->trans('AI Reply Settings') . '
        </td></tr>';
    }
}

/**
 * AI Reply provider setting
 * @subpackage ai_reply/output
 */
class Hm_Output_ai_reply_provider_setting extends Hm_Output_Module {
    protected function output() {
        $settings = $this->get('user_settings', array());
        $provider = 'ollama';
        $reset = '';
        
        if (array_key_exists('ai_reply_provider', $settings)) {
            $provider = $settings['ai_reply_provider'];
        }
        
        if ($provider !== 'ollama') {
            $reset = '<span class="tooltip_restore" restore_aria_label="Restore default value"><i class="bi bi-arrow-counterclockwise refresh_list reset_default_value_select"></i></span>';
        }
        
        return '<tr class="ai_reply_setting"><td><label for="ai_reply_provider">' .
            $this->trans('AI Provider') . '</label></td><td>' .
            '<select class="form-select form-select-sm w-auto" name="ai_reply_provider" id="ai_reply_provider" data-default-value="ollama">' .
            '<option value="ollama"' . ($provider === 'ollama' ? ' selected="selected"' : '') . '>Ollama</option>' .
            '<option value="openai"' . ($provider === 'openai' ? ' selected="selected"' : '') . '>OpenAI Compatible</option>' .
            '</select>' . $reset . '</td></tr>';
    }
}

/**
 * AI Reply API URL setting
 * @subpackage ai_reply/output
 */
class Hm_Output_ai_reply_api_url_setting extends Hm_Output_Module {
    protected function output() {
        $settings = $this->get('user_settings', array());
        $api_url = 'http://localhost:11434';
        $reset = '';
        
        if (array_key_exists('ai_reply_api_url', $settings)) {
            $api_url = $settings['ai_reply_api_url'];
        }
        
        if ($api_url !== 'http://localhost:11434') {
            $reset = '<span class="tooltip_restore" restore_aria_label="Restore default value"><i class="bi bi-arrow-counterclockwise refresh_list reset_default_value_input"></i></span>';
        }
        
        return '<tr class="ai_reply_setting"><td><label for="ai_reply_api_url">' .
            $this->trans('API URL') . '</label></td><td>' .
            '<input type="text" class="form-control form-control-sm w-auto" name="ai_reply_api_url" id="ai_reply_api_url" ' .
            'value="' . $this->html_safe($api_url) . '" data-default-value="http://localhost:11434" />' .
            $reset . '<br><small class="form-text text-muted">' . 
            $this->trans('For Ollama: http://localhost:11434, For OpenAI: https://api.openai.com') . 
            '</small></td></tr>';
    }
}

/**
 * AI Reply API key setting
 * @subpackage ai_reply/output
 */
class Hm_Output_ai_reply_api_key_setting extends Hm_Output_Module {
    protected function output() {
        $settings = $this->get('user_settings', array());
        $api_key = '';
        $reset = '';
        
        if (array_key_exists('ai_reply_api_key', $settings)) {
            $api_key = $settings['ai_reply_api_key'];
        }
        
        if (!empty($api_key)) {
            $reset = '<span class="tooltip_restore" restore_aria_label="Restore default value"><i class="bi bi-arrow-counterclockwise refresh_list reset_default_value_input"></i></span>';
        }
        
        return '<tr class="ai_reply_setting"><td><label for="ai_reply_api_key">' .
            $this->trans('API Key') . '</label></td><td>' .
            '<input type="password" class="form-control form-control-sm w-auto" name="ai_reply_api_key" id="ai_reply_api_key" ' .
            'value="' . $this->html_safe($api_key) . '" data-default-value="" />' .
            $reset . '<br><small class="form-text text-muted">' . 
            $this->trans('Required for OpenAI, optional for Ollama') . 
            '</small></td></tr>';
    }
}

/**
 * AI Reply model setting
 * @subpackage ai_reply/output
 */
class Hm_Output_ai_reply_model_setting extends Hm_Output_Module {
    protected function output() {
        $settings = $this->get('user_settings', array());
        $model = 'llama2';
        $reset = '';
        
        if (array_key_exists('ai_reply_model', $settings)) {
            $model = $settings['ai_reply_model'];
        }
        
        if ($model !== 'llama2') {
            $reset = '<span class="tooltip_restore" restore_aria_label="Restore default value"><i class="bi bi-arrow-counterclockwise refresh_list reset_default_value_input"></i></span>';
        }
        
        return '<tr class="ai_reply_setting"><td><label for="ai_reply_model">' .
            $this->trans('Model Name') . '</label></td><td>' .
            '<input type="text" class="form-control form-control-sm w-auto" name="ai_reply_model" id="ai_reply_model" ' .
            'value="' . $this->html_safe($model) . '" data-default-value="llama2" />' .
            $reset . '<br><small class="form-text text-muted">' . 
            $this->trans('e.g., llama2, gpt-3.5-turbo, mistral') . 
            '</small></td></tr>';
    }
}

/**
 * AI Reply system prompt setting
 * @subpackage ai_reply/output
 */
class Hm_Output_ai_reply_system_prompt_setting extends Hm_Output_Module {
    protected function output() {
        $settings = $this->get('user_settings', array());
        $default_prompt = 'You are a helpful email assistant. Generate professional and concise email responses.';
        $system_prompt = $default_prompt;
        $reset = '';
        
        if (array_key_exists('ai_reply_system_prompt', $settings)) {
            $system_prompt = $settings['ai_reply_system_prompt'];
        }
        
        if ($system_prompt !== $default_prompt) {
            $reset = '<span class="tooltip_restore" restore_aria_label="Restore default value"><i class="bi bi-arrow-counterclockwise refresh_list reset_default_value_textarea"></i></span>';
        }
        
        return '<tr class="ai_reply_setting"><td><label for="ai_reply_system_prompt">' .
            $this->trans('System Prompt') . '</label></td><td>' .
            '<textarea class="form-control form-control-sm" name="ai_reply_system_prompt" id="ai_reply_system_prompt" ' .
            'rows="3" data-default-value="' . $this->html_safe($default_prompt) . '">' .
            $this->html_safe($system_prompt) . '</textarea>' .
            $reset . '<br><small class="form-text text-muted">' . 
            $this->trans('Instructions for the AI model') . 
            '</small></td></tr>';
    }
}

/**
 * AI Reply compose buttons
 * @subpackage ai_reply/output
 */
class Hm_Output_ai_reply_compose_buttons extends Hm_Output_Module {
    protected function output() {
        $reply_details = $this->get('reply_details', array());
        $has_context = !empty($reply_details);
        
        $res = '<div class="ai_reply_tools mt-3 mb-3">';
        $res .= '<div class="btn-group" role="group">';
        
        // AI Reply button (only show if replying to a message)
        if ($has_context) {
            $res .= '<button type="button" class="btn btn-outline-primary btn-sm ai_generate_reply" title="' . 
                $this->trans('Generate AI reply based on the email context') . '">';
            $res .= '<i class="bi bi-robot"></i> ' . $this->trans('AI Reply') . '</button>';
        }
        
        // AI Generate from Prompt button
        $res .= '<button type="button" class="btn btn-outline-primary btn-sm ai_generate_prompt" title="' . 
            $this->trans('Generate content from a custom prompt') . '">';
        $res .= '<i class="bi bi-magic"></i> ' . $this->trans('AI Generate') . '</button>';
        
        $res .= '</div></div>';
        
        // Modal for prompt input
        $res .= '<div class="modal fade" id="aiPromptModal" tabindex="-1" aria-hidden="true">';
        $res .= '<div class="modal-dialog">';
        $res .= '<div class="modal-content">';
        $res .= '<div class="modal-header">';
        $res .= '<h5 class="modal-title">' . $this->trans('AI Generate from Prompt') . '</h5>';
        $res .= '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
        $res .= '</div>';
        $res .= '<div class="modal-body">';
        $res .= '<div class="mb-3">';
        $res .= '<label for="ai_custom_prompt" class="form-label">' . $this->trans('Enter your prompt') . '</label>';
        $res .= '<textarea class="form-control" id="ai_custom_prompt" rows="4" placeholder="' . 
            $this->trans('e.g., Write a professional follow-up email...') . '"></textarea>';
        $res .= '</div>';
        $res .= '</div>';
        $res .= '<div class="modal-footer">';
        $res .= '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . 
            $this->trans('Cancel') . '</button>';
        $res .= '<button type="button" class="btn btn-primary" id="ai_generate_from_prompt_btn">' . 
            $this->trans('Generate') . '</button>';
        $res .= '</div>';
        $res .= '</div></div></div>';
        
        return $res;
    }
}