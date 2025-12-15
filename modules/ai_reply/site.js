'use strict';

/**
 * AI Reply JavaScript functionality
 */

var ai_reply_in_progress = false;

/**
 * Generate AI reply based on email context
 */
var ai_generate_reply = function() {
    if (ai_reply_in_progress) {
        return;
    }
    
    // Get the original message context from the compose body
    var compose_body = $('.compose_body').val();
    var context = compose_body;
    
    // Extract quoted text if available (common email reply format)
    var quoted_match = compose_body.match(/On .+ wrote:[\s\S]*$/);
    if (quoted_match) {
        context = quoted_match[0];
    }
    
    if (!context || context.trim() === '') {
        Hm_Notices.show('No email context found to generate a reply', 'danger');
        return;
    }
    
    ai_reply_in_progress = true;
    $('.ai_generate_reply').prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Generating...');
    
    Hm_Ajax.request(
        [
            {'name': 'hm_ajax_hook', 'value': 'ajax_ai_generate'},
            {'name': 'ai_mode', 'value': 'reply'},
            {'name': 'ai_context', 'value': context}
        ],
        function(res) {
            ai_reply_in_progress = false;
            $('.ai_generate_reply').prop('disabled', false).html('<i class="bi bi-robot"></i> AI Reply');
            
            if (res.ai_generated_text) {
                // Insert the generated text at the beginning of the compose body
                var current_body = $('.compose_body').val();
                var new_body = res.ai_generated_text + '\n\n' + current_body;
                $('.compose_body').val(new_body);
                
                // If using HTML editor, update it too
                if (window.HTMLEditor && typeof CKEDITOR !== 'undefined') {
                    var editor = CKEDITOR.instances.compose_body;
                    if (editor) {
                        editor.setData(new_body.replace(/\n/g, '<br>'));
                    }
                }
                
                // Move cursor to the beginning
                $('.compose_body').focus();
                $('.compose_body')[0].setSelectionRange(0, 0);
                
                Hm_Notices.show('AI reply generated successfully', 'info');
            } else {
                Hm_Notices.show('Failed to generate AI reply', 'danger');
            }
        },
        [],
        false,
        function() {
            ai_reply_in_progress = false;
            $('.ai_generate_reply').prop('disabled', false).html('<i class="bi bi-robot"></i> AI Reply');
        }
    );
};

/**
 * Show prompt modal for custom AI generation
 */
var ai_show_prompt_modal = function() {
    $('#ai_custom_prompt').val('');
    var modal = new bootstrap.Modal(document.getElementById('aiPromptModal'));
    modal.show();
};

/**
 * Generate AI content from custom prompt
 */
var ai_generate_from_prompt = function() {
    var prompt = $('#ai_custom_prompt').val();
    
    if (!prompt || prompt.trim() === '') {
        Hm_Notices.show('Please enter a prompt', 'danger');
        return;
    }
    
    if (ai_reply_in_progress) {
        return;
    }
    
    ai_reply_in_progress = true;
    $('#ai_generate_from_prompt_btn').prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Generating...');
    
    Hm_Ajax.request(
        [
            {'name': 'hm_ajax_hook', 'value': 'ajax_ai_generate'},
            {'name': 'ai_mode', 'value': 'prompt'},
            {'name': 'ai_prompt', 'value': prompt}
        ],
        function(res) {
            ai_reply_in_progress = false;
            $('#ai_generate_from_prompt_btn').prop('disabled', false).html('Generate');
            
            if (res.ai_generated_text) {
                var generated = res.ai_generated_text.trim();
                var subject = '';
                var body = '';
                
                // Try to parse as JSON first
                try {
                    var json_data = JSON.parse(generated);
                    if (json_data.subject) {
                        subject = json_data.subject;
                    }
                    if (json_data.body) {
                        body = json_data.body;
                    }
                } catch (e) {
                    // If not JSON, try multiple subject patterns
                    // Pattern 1: **Betreff:** or **Subject:** (markdown bold)
                    var md_subject_match = generated.match(/^\*\*(?:Subject|Betreff|Objet|Asunto|Oggetto|Assunto):\*\*\s*(.+?)$/im);
                    if (md_subject_match) {
                        subject = md_subject_match[1].trim();
                        body = generated.replace(/^\*\*(?:Subject|Betreff|Objet|Asunto|Oggetto|Assunto):\*\*\s*.+?$/im, '').trim();
                    } else {
                        // Pattern 2: Subject: or Betreff: (plain)
                        var plain_subject_match = generated.match(/^(?:Subject|Betreff|Objet|Asunto|Oggetto|Assunto):\s*(.+?)$/im);
                        if (plain_subject_match) {
                            subject = plain_subject_match[1].trim();
                            body = generated.replace(/^(?:Subject|Betreff|Objet|Asunto|Oggetto|Assunto):\s*.+?$/im, '').trim();
                        } else {
                            // No subject found, use entire text as body
                            body = generated;
                        }
                    }
                }
                
                // Strip markdown formatting from body
                if (body) {
                    // Remove bold (**text** or __text__)
                    body = body.replace(/\*\*(.+?)\*\*/g, '$1');
                    body = body.replace(/__(.+?)__/g, '$1');
                    // Remove italic (*text* or _text_)
                    body = body.replace(/\*(.+?)\*/g, '$1');
                    body = body.replace(/_(.+?)_/g, '$1');
                    // Remove headers (## text)
                    body = body.replace(/^#+\s+(.+?)$/gm, '$1');
                    // Clean up any remaining markdown artifacts
                    body = body.trim();
                }
                
                // Strip markdown from subject too
                if (subject) {
                    subject = subject.replace(/\*\*(.+?)\*\*/g, '$1');
                    subject = subject.replace(/__(.+?)__/g, '$1');
                    subject = subject.replace(/\*(.+?)\*/g, '$1');
                    subject = subject.replace(/_(.+?)_/g, '$1');
                    subject = subject.trim();
                }
                
                // Set subject if found
                if (subject) {
                    $('.compose_subject').val(subject);
                }
                
                // Insert the body text
                var current_body = $('.compose_body').val();
                var new_body = body;
                
                // If there's existing content, add it after the generated text
                if (current_body && current_body.trim() !== '') {
                    new_body = body + '\n\n' + current_body;
                }
                
                $('.compose_body').val(new_body);
                
                // If using HTML editor, update it too
                if (window.HTMLEditor && typeof CKEDITOR !== 'undefined') {
                    var editor = CKEDITOR.instances.compose_body;
                    if (editor) {
                        editor.setData(new_body.replace(/\n/g, '<br>'));
                    }
                }
                
                // Close the modal
                var modal = bootstrap.Modal.getInstance(document.getElementById('aiPromptModal'));
                if (modal) {
                    modal.hide();
                }
                
                // Move cursor to the beginning
                $('.compose_body').focus();
                $('.compose_body')[0].setSelectionRange(0, 0);
                
                Hm_Notices.show('AI content generated successfully', 'info');
            } else {
                Hm_Notices.show('Failed to generate AI content', 'danger');
            }
        },
        [],
        false,
        function() {
            ai_reply_in_progress = false;
            $('#ai_generate_from_prompt_btn').prop('disabled', false).html('Generate');
        }
    );
};

/**
 * Initialize AI Reply functionality on compose page
 */
$(function() {
    // Bind AI Reply button (using document delegation for dynamic content)
    $(document).on('click', '.ai_generate_reply', ai_generate_reply);
    
    // Bind AI Generate from Prompt button
    $(document).on('click', '.ai_generate_prompt', ai_show_prompt_modal);
    
    // Bind Generate button in modal
    $(document).on('click', '#ai_generate_from_prompt_btn', ai_generate_from_prompt);
    
    // Allow Ctrl+Enter in modal to trigger generation
    $(document).on('keypress', '#ai_custom_prompt', function(e) {
        if (e.which === 13 && e.ctrlKey) {
            ai_generate_from_prompt();
        }
    });
});