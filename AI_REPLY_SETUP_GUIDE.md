# AI Reply Module - Complete Setup Guide

## Quick Start

This guide will help you set up and use the AI Reply module in Cypht to generate email responses using AI.

---

## Installation Steps

### Step 1: Enable the Module

1. Open your `.env` file in the Cypht root directory
2. Find the `CYPHT_MODULES` line
3. Add `ai_reply` to the list of modules:

```bash
# Example - add ai_reply to your existing modules
CYPHT_MODULES=core,imap,smtp,imap_folders,contacts,ai_reply
```

### Step 2: Regenerate Configuration

Run the configuration generation script:

```bash
cd /var/www/cypht
php scripts/config_gen.php
```

### Step 3: Clear Browser Cache

Clear your browser cache or do a hard refresh (Ctrl+Shift+R or Cmd+Shift+R) to load the new module files.

---

## Configuration Options

### Option A: Using Ollama (Recommended for Privacy)

**Ollama runs AI models locally on your machine - no data leaves your computer!**

#### 1. Install Ollama

```bash
# Linux/Mac
curl -fsSL https://ollama.ai/install.sh | sh

# Or download from: https://ollama.ai
```

#### 2. Pull a Model

```bash
# Recommended models:
ollama pull llama2          # Good balance of speed and quality
ollama pull mistral         # Faster, good for quick replies
ollama pull llama2:13b      # Better quality, slower
```

#### 3. Start Ollama

```bash
ollama serve
```

#### 4. Configure in Cypht

1. Log into Cypht
2. Go to **Settings** → Scroll to **AI Reply Settings**
3. Configure:
   - **AI Provider**: Select `Ollama`
   - **API URL**: `http://localhost:11434`
   - **API Key**: Leave empty
   - **Model Name**: `llama2` (or the model you pulled)
   - **System Prompt**: (Optional) Customize or leave default
4. Click **Save**

---

### Option B: Using OpenAI

**Uses OpenAI's cloud API - requires API key and internet connection**

#### 1. Get an API Key

1. Go to https://platform.openai.com
2. Sign up or log in
3. Navigate to API Keys section
4. Create a new API key
5. Copy the key (you won't see it again!)

#### 2. Configure in Cypht

1. Log into Cypht
2. Go to **Settings** → Scroll to **AI Reply Settings**
3. Configure:
   - **AI Provider**: Select `OpenAI Compatible`
   - **API URL**: `https://api.openai.com`
   - **API Key**: Paste your OpenAI API key
   - **Model Name**: `gpt-3.5-turbo` (or `gpt-4` if you have access)
   - **System Prompt**: (Optional) Customize or leave default
4. Click **Save**

---

### Option C: Using LocalAI (Self-Hosted Alternative)

**Run OpenAI-compatible models on your own server**

#### 1. Install LocalAI

```bash
# Using Docker
docker run -p 8080:8080 --name localai -ti localai/localai:latest
```

#### 2. Configure in Cypht

1. Log into Cypht
2. Go to **Settings** → Scroll to **AI Reply Settings**
3. Configure:
   - **AI Provider**: Select `OpenAI Compatible`
   - **API URL**: `http://localhost:8080`
   - **API Key**: Leave empty (unless you configured authentication)
   - **Model Name**: Your installed model name
   - **System Prompt**: (Optional) Customize or leave default
4. Click **Save**

---

## Usage

### Generating AI Replies

**Use this when replying to an email:**

1. Open an email in Cypht
2. Click **Reply** or **Reply All**
3. In the compose screen, you'll see two new buttons:
   - **🤖 AI Reply** - Generates a reply based on the email context
   - **✨ AI Generate** - Generates content from a custom prompt

4. Click **🤖 AI Reply**
5. Wait a few seconds while the AI generates a response
6. The generated reply will appear in the compose body
7. Review and edit the generated text as needed
8. Send your email!

### Generating from Custom Prompts

**Use this for composing new emails or custom content:**

1. Go to **Compose** (or click Reply on any email)
2. Click **✨ AI Generate**
3. A modal will appear asking for your prompt
4. Enter your prompt, for example:
   - "Write a professional follow-up email about the project deadline"
   - "Draft a thank you email for the meeting yesterday"
   - "Compose a polite decline for the invitation"
5. Click **Generate** (or press Ctrl+Enter)
6. The AI will generate content based on your prompt
7. Review and edit as needed
8. Send your email!

---

## Customizing the System Prompt

The system prompt tells the AI how to behave. You can customize it in Settings:

### Default Prompt
```
You are a helpful email assistant. Generate professional and concise email responses.
```

### Example Custom Prompts

**For Formal Business Emails:**
```
You are a professional business email assistant. Generate formal, concise, and polite email responses. Use professional language and maintain a respectful tone.
```

**For Casual/Friendly Emails:**
```
You are a friendly email assistant. Generate warm, conversational, and helpful email responses. Keep the tone casual but professional.
```

**For Technical Support:**
```
You are a technical support email assistant. Generate clear, helpful, and solution-oriented email responses. Include step-by-step instructions when appropriate.
```

**For Sales/Marketing:**
```
You are a sales and marketing email assistant. Generate engaging, persuasive, and customer-focused email responses. Highlight benefits and maintain a positive tone.
```

---

## Troubleshooting

### "AI generation failed" Error

**Problem**: The AI request failed

**Solutions**:
1. **Check if Ollama is running** (if using Ollama):
   ```bash
   ollama serve
   ```

2. **Verify API URL**:
   - Ollama: Should be `http://localhost:11434`
   - OpenAI: Should be `https://api.openai.com`

3. **Check API Key** (if using OpenAI):
   - Ensure it's valid and has credits
   - Check for typos

4. **Verify Model Name**:
   - Ollama: Run `ollama list` to see available models
   - OpenAI: Use `gpt-3.5-turbo` or `gpt-4`

5. **Check Network Connection**:
   - Ensure you can reach the API endpoint
   - Check firewall settings

### "No email context found" Error

**Problem**: Clicked "AI Reply" but not in a reply context

**Solution**: Use "AI Generate" button instead for composing new emails

### Slow Generation

**Causes**:
- First-time model loading (Ollama)
- Large models
- Network latency (cloud APIs)

**Solutions**:
- Use smaller/faster models (e.g., `mistral` instead of `llama2:13b`)
- Ensure good internet connection (for cloud APIs)
- Wait for first load to complete (subsequent requests will be faster)

### Module Not Appearing

**Solutions**:
1. Verify module is in `.env`:
   ```bash
   grep CYPHT_MODULES .env
   ```

2. Regenerate config:
   ```bash
   php scripts/config_gen.php
   ```

3. Clear browser cache (Ctrl+Shift+R)

4. Check file permissions:
   ```bash
   ls -la modules/ai_reply/
   ```

---

## Privacy & Security

### Using Ollama (Local)
- ✅ **100% Private**: All processing happens on your machine
- ✅ **No Internet Required**: Works offline
- ✅ **No Data Sharing**: Your emails never leave your computer
- ⚠️ **Resource Usage**: Requires CPU/RAM for model inference

### Using OpenAI (Cloud)
- ⚠️ **Data Sent to OpenAI**: Email content is sent to OpenAI's servers
- ⚠️ **Internet Required**: Needs active internet connection
- ⚠️ **API Costs**: Usage is billed by OpenAI
- ✅ **Fast**: No local resource usage
- 📋 **Review**: Check OpenAI's privacy policy and terms

### Best Practices
1. **Sensitive Emails**: Use Ollama for confidential/sensitive content
2. **API Keys**: Never share your API keys
3. **Review Generated Content**: Always review AI-generated text before sending
4. **Data Retention**: Check your AI provider's data retention policies

---

## Advanced Configuration

### Using Different Models

**Ollama Models:**
```bash
# List available models
ollama list

# Pull specific models
ollama pull codellama        # For technical/code-heavy emails
ollama pull neural-chat      # Optimized for conversations
ollama pull orca-mini        # Lightweight, fast
```

**OpenAI Models:**
- `gpt-3.5-turbo`: Fast, cost-effective
- `gpt-4`: Higher quality, more expensive
- `gpt-4-turbo`: Balance of speed and quality

### Custom API Endpoints

You can use any OpenAI-compatible API:

**Examples:**
- **Azure OpenAI**: `https://YOUR-RESOURCE.openai.azure.com`
- **Together.ai**: `https://api.together.xyz`
- **Anyscale**: `https://api.endpoints.anyscale.com`
- **Custom**: Your own API endpoint

---

## Module Architecture

Following Cypht's development guide principles:

### ✅ Self-Contained Module
- No dependencies on other modules (except core)
- Can be enabled/disabled independently
- All functionality contained within `modules/ai_reply/`

### ✅ Proper Authorization
- All pages, POST variables, and outputs are explicitly allowed
- Follows Cypht security patterns

### ✅ Non-Intrusive Integration
- Uses handler/output positioning (`before`/`after`)
- No modifications to core or other modules
- Clean injection points

### Files Structure
```
modules/ai_reply/
├── setup.php       # Module configuration, routes, permissions
├── modules.php     # Handler and output classes
├── site.js         # Frontend JavaScript
├── site.css        # Styling
└── README.md       # Documentation
```

---

## Support & Contribution

### Getting Help
1. Check this guide first
2. Review the module README: `modules/ai_reply/README.md`
3. Check Cypht documentation
4. Review the development guide: `CYPHT_DEVELOPMENT_GUIDE.md`

### Contributing
Contributions are welcome! Follow Cypht's development guidelines when making changes.

---

## Changelog

### Version 1.0.0 (Initial Release)
- ✨ AI-powered reply generation
- ✨ Custom prompt generation
- ✨ Ollama support (local LLMs)
- ✨ OpenAI-compatible API support
- ✨ Configurable settings
- ✨ Professional UI integration
- ✨ Privacy-focused design

---

## License

This module follows Cypht's licensing terms.

---

**Enjoy AI-powered email composition! 🚀**