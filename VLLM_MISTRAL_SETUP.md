# vLLM Configuration for Cypht AI Reply Module

## Your Current vLLM Setup

You have **TWO vLLM models** running on your system:

### Model 1: Ministral-3-14B (Recommended for Email)
- **Port:** 8001
- **Model ID:** `/models/Ministral-3-14B-Instruct-2512`
- **Max Context:** 20,000 tokens
- **Best for:** Email replies, general conversation
- **Speed:** Fast ⚡

### Model 2: Qwen3-Coder-30B (For Code/Technical)
- **Port:** 8000
- **Model ID:** `/models/Qwen3-Coder-30B-A3B-Instruct-FP8`
- **Max Context:** 90,000 tokens
- **Best for:** Technical emails, code-related content
- **Speed:** Slower but more capable

---

## Quick Setup for Cypht

### Option 1: Ministral (Recommended for Most Emails)

1. **Go to Cypht Settings**
2. **Scroll to "AI Reply Settings"**
3. **Configure:**

```
AI Provider: OpenAI Compatible
API URL: http://localhost:8001
API Key: (leave empty)
Model Name: /models/Ministral-3-14B-Instruct-2512
System Prompt: You are a helpful email assistant. Generate professional and concise email responses.
```

4. **Click Save**

### Option 2: Qwen3-Coder (For Technical/Code Emails)

1. **Go to Cypht Settings**
2. **Scroll to "AI Reply Settings"**
3. **Configure:**

```
AI Provider: OpenAI Compatible
API URL: http://localhost:8000
API Key: (leave empty)
Model Name: /models/Qwen3-Coder-30B-A3B-Instruct-FP8
System Prompt: You are a technical email assistant. Generate clear, professional responses with technical accuracy.
```

4. **Click Save**

---

## Testing Your Setup

### Test 1: Verify API is Working

**For Ministral (Port 8001):**
```bash
curl -X POST http://localhost:8001/v1/chat/completions \
  -H "Content-Type: application/json" \
  -d '{
    "model": "/models/Ministral-3-14B-Instruct-2512",
    "messages": [
      {"role": "system", "content": "You are a helpful assistant."},
      {"role": "user", "content": "Write a brief professional email reply saying thank you."}
    ],
    "max_tokens": 200
  }'
```

**For Qwen3-Coder (Port 8000):**
```bash
curl -X POST http://localhost:8000/v1/chat/completions \
  -H "Content-Type: application/json" \
  -d '{
    "model": "/models/Qwen3-Coder-30B-A3B-Instruct-FP8",
    "messages": [
      {"role": "system", "content": "You are a helpful assistant."},
      {"role": "user", "content": "Write a brief professional email reply saying thank you."}
    ],
    "max_tokens": 200
  }'
```

### Test 2: Check Models List

```bash
# Ministral
curl http://localhost:8001/v1/models

# Qwen3-Coder
curl http://localhost:8000/v1/models
```

---

## Usage in Cypht

### For Regular Email Replies (Use Ministral)

1. Open an email and click **Reply**
2. Click **🤖 AI Reply** button
3. Wait 2-5 seconds for generation
4. Review and edit the generated reply
5. Send!

### For Technical/Code Emails (Use Qwen3-Coder)

1. Switch to Qwen3-Coder in Settings (port 8000)
2. Open technical email and click **Reply**
3. Click **🤖 AI Reply** button
4. Get technically accurate response
5. Review and send!

### For Custom Content

1. Click **✨ AI Generate** button
2. Enter your prompt:
   - "Write a follow-up email about the project deadline"
   - "Draft a professional decline for the meeting invitation"
   - "Compose a thank you email for the interview"
3. Click **Generate**
4. Review and edit
5. Send!

---

## Performance Comparison

| Feature | Ministral-3-14B | Qwen3-Coder-30B |
|---------|-----------------|-----------------|
| **Speed** | ⚡⚡⚡ Fast | ⚡⚡ Moderate |
| **Context** | 20K tokens | 90K tokens |
| **Best For** | General emails | Technical/Code |
| **Port** | 8001 | 8000 |
| **Memory** | Lower | Higher |

---

## Recommended System Prompts

### For General Business Emails (Ministral)
```
You are a professional email assistant. Generate clear, concise, and polite email responses. Keep responses brief and to the point. Use a professional but friendly tone.
```

### For Technical Emails (Qwen3-Coder)
```
You are a technical email assistant. Generate accurate, detailed responses for technical inquiries. Include relevant technical details and code examples when appropriate. Maintain professional clarity.
```

### For Customer Support
```
You are a customer support email assistant. Generate helpful, empathetic, and solution-oriented responses. Be patient and thorough in addressing customer concerns.
```

### For Sales/Marketing
```
You are a sales and marketing email assistant. Generate engaging, persuasive, and customer-focused responses. Highlight benefits and maintain a positive, professional tone.
```

---

## Troubleshooting

### "AI generation failed" Error

**Check if vLLM is running:**
```bash
ps aux | grep vllm
```

**Check if ports are listening:**
```bash
netstat -tlnp | grep -E ':(8000|8001)'
```

**Test API directly:**
```bash
curl http://localhost:8001/v1/models
curl http://localhost:8000/v1/models
```

### Slow Generation

**Ministral is faster** - Use port 8001 for quick replies

**Qwen3-Coder is slower but more capable** - Use port 8000 for complex technical content

### Wrong Model Name Error

Make sure you use the **FULL model path**:
- ✅ `/models/Ministral-3-14B-Instruct-2512`
- ✅ `/models/Qwen3-Coder-30B-A3B-Instruct-FP8`
- ❌ `Ministral-3-14B`
- ❌ `Qwen3-Coder-30B`

---

## Advanced Configuration

### Switching Between Models

You can switch models anytime in Settings:

**For quick replies:** Use Ministral (port 8001)
**For technical content:** Use Qwen3-Coder (port 8000)

### Custom Temperature/Parameters

The AI Reply module uses default parameters:
- Temperature: 0.7
- Max Tokens: 1000

These are optimized for email generation.

---

## System Information

### Your vLLM Processes

**Ministral Process:**
```
Port: 8001
Model: /models/Ministral-3-14B-Instruct-2512
Max Length: 20000 tokens
GPU Memory: 45%
```

**Qwen3-Coder Process:**
```
Port: 8000
Model: /models/Qwen3-Coder-30B-A3B-Instruct-FP8
Max Length: 90000 tokens
GPU Memory: 42%
```

### API Endpoints

Both models expose OpenAI-compatible endpoints:

- `http://localhost:8001/v1/chat/completions` (Ministral)
- `http://localhost:8000/v1/chat/completions` (Qwen3-Coder)
- `http://localhost:8001/v1/models` (List models)
- `http://localhost:8000/v1/models` (List models)

---

## Privacy & Security

✅ **100% Local** - All processing happens on your machine
✅ **No Internet Required** - Works completely offline
✅ **No Data Sharing** - Your emails never leave your server
✅ **Full Control** - You control the models and data

---

## Quick Reference Card

### Ministral-3-14B (Port 8001) - RECOMMENDED
```
Provider: OpenAI Compatible
URL: http://localhost:8001
Key: (empty)
Model: /models/Ministral-3-14B-Instruct-2512
```

### Qwen3-Coder-30B (Port 8000) - TECHNICAL
```
Provider: OpenAI Compatible
URL: http://localhost:8000
Key: (empty)
Model: /models/Qwen3-Coder-30B-A3B-Instruct-FP8
```

---

## Support

If you encounter issues:

1. Check vLLM is running: `ps aux | grep vllm`
2. Test API: `curl http://localhost:8001/v1/models`
3. Check Cypht logs for errors
4. Verify model name is exact (with full path)

---

**Ready to use! Start with Ministral on port 8001 for best results.** 🚀