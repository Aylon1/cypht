# AI Reply Module

## Overview

The AI Reply module integrates AI-powered email composition capabilities into Cypht. It allows users to generate email replies and compose content using Large Language Models (LLMs) such as Ollama or OpenAI-compatible APIs.

## Features

- **AI-Powered Reply Generation**: Automatically generate professional email replies based on the context of the email you're responding to
- **Custom Prompt Generation**: Generate email content from custom prompts
- **Multiple Provider Support**: 
  - Ollama (local LLM)
  - OpenAI-compatible APIs (OpenAI, LocalAI, etc.)
- **Configurable Settings**: Customize AI provider, API endpoints, models, and system prompts
- **Non-Intrusive**: Completely self-contained module that can be toggled on/off

## Installation

1. **Enable the Module**

   Add `ai_reply` to your `CYPHT_MODULES` in the `.env` file:
   
   ```
   CYPHT_MODULES=core,imap,smtp,ai_reply,...
   ```

2. **Regenerate Configuration**

   Run the configuration generation script:
   
   ```bash
   php scripts/config_gen.php
   ```

3. **Configure Settings**

   Navigate to Settings → AI Reply Settings and configure:
   - AI Provider (Ollama or OpenAI Compatible)
   - API URL
   - API Key (if required)
   - Model Name
   - System Prompt (optional)

## Configuration

### Ollama Setup

1. Install Ollama: https://ollama.ai
2. Pull a model: `ollama pull llama2`
3. Configure in Cypht:
   - Provider: `Ollama`
   - API URL: `http://localhost:11434`
   - Model: `llama2` (or your preferred model)

### OpenAI Compatible Setup

1. Obtain an API key from your provider
2. Configure in Cypht:
   - Provider: `OpenAI Compatible`
   - API URL: `https://api.openai.com` (or your provider's URL)
   - API Key: Your API key
   - Model: `gpt-3.5-turbo` (or your preferred model)

### LocalAI Setup

1. Install LocalAI: https://localai.io
2. Configure in Cypht:
   - Provider: `OpenAI Compatible`
   - API URL: `http://localhost:8080`
   - Model: Your installed model name

## Usage

### Generating AI Replies

1. Open an email and click "Reply" or "Reply All"
2. In the compose screen, click the **"AI Reply"** button
3. The AI will analyze the email context and generate a professional reply
4. Review and edit the generated content as needed
5. Send your email

### Generating from Custom Prompts

1. In the compose screen, click the **"AI Generate"** button
2. Enter your custom prompt in the modal dialog
3. Click "Generate"
4. The AI will create content based on your prompt
5. Review and edit as needed

## Settings

### AI Provider
Choose between Ollama (local) or OpenAI-compatible APIs.

### API URL
- **Ollama**: `http://localhost:11434`
- **OpenAI**: `https://api.openai.com`
- **LocalAI**: `http://localhost:8080`
- **Custom**: Your provider's endpoint

### API Key
Required for OpenAI and some other providers. Leave empty for Ollama.

### Model Name
Examples:
- Ollama: `llama2`, `mistral`, `codellama`
- OpenAI: `gpt-3.5-turbo`, `gpt-4`
- LocalAI: Your installed model name

### System Prompt
Customize the AI's behavior. Default:
```
You are a helpful email assistant. Generate professional and concise email responses.
```

## Privacy & Security

- **Local Processing**: When using Ollama, all AI processing happens locally on your machine
- **API Keys**: Stored securely in Cypht's user configuration
- **No Data Retention**: Email content is only sent to the AI for generation and not stored by this module
- **Provider Privacy**: Review your AI provider's privacy policy for cloud-based services

## Troubleshooting

### "AI generation failed" Error

1. **Check API URL**: Ensure the URL is correct and accessible
2. **Verify API Key**: For OpenAI-compatible APIs, ensure your key is valid
3. **Check Model Name**: Ensure the model exists and is available
4. **Network Issues**: Verify network connectivity to the API endpoint
5. **Ollama Not Running**: If using Ollama, ensure it's running: `ollama serve`

### "No email context found" Error

This occurs when clicking "AI Reply" without being in a reply context. Use "AI Generate" instead for composing new emails.

### Slow Generation

- **Local Models**: First-time model loading can be slow
- **Large Models**: Consider using smaller, faster models
- **Network Latency**: Cloud APIs may have variable response times

## Development

### Module Structure

```
modules/ai_reply/
├── setup.php       # Module configuration and routing
├── modules.php     # Handler and output classes
├── site.js         # Frontend JavaScript
├── site.css        # Styling
└── README.md       # This file
```

### Key Components

- **Handlers**: Process AI generation requests and settings
- **Outputs**: Render UI elements (buttons, settings, modals)
- **AJAX**: `ajax_ai_generate` endpoint for AI generation
- **JavaScript**: UI interactions and API calls

## Compatibility

- **Cypht Version**: 2.0+
- **PHP**: 7.4+
- **Dependencies**: cURL extension
- **Browsers**: Modern browsers with ES6 support

## License

This module follows Cypht's licensing terms.

## Support

For issues, questions, or contributions, please refer to the main Cypht project repository.

## Changelog

### Version 1.0.0
- Initial release
- Ollama support
- OpenAI-compatible API support
- Reply generation
- Custom prompt generation
- Configurable settings