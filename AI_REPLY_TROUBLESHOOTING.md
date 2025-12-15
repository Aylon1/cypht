# AI Reply Module - Troubleshooting Guide

## Issue: 500 Error on Settings Page

### Problem
Getting a 500 Internal Server Error when trying to access the Settings page after enabling the AI Reply module.

### Root Cause
The initial implementation used `Hm_Image_Sources::$chevron` which is not available in the current Cypht version. Modern Cypht uses Bootstrap icons instead.

### Solution
✅ **FIXED** - The module has been updated to use Bootstrap icons (`<i class="bi bi-robot fs-5 me-2"></i>`) instead of the deprecated image source.

### Steps Taken
1. Identified the issue in `Hm_Output_start_ai_reply_settings` class
2. Updated to use Bootstrap icon syntax matching other modules (tags, etc.)
3. Regenerated configuration with `php scripts/config_gen.php`

### Verification
After the fix:
1. Clear browser cache (Ctrl+Shift+R or Cmd+Shift+R)
2. Navigate to Settings page
3. You should now see "AI Reply Settings" section with a robot icon
4. All settings should be accessible

---

## Common Issues & Solutions

### Module Not Appearing

**Symptoms:**
- AI Reply settings not visible
- No AI buttons on compose page

**Solutions:**
1. Verify module is enabled in `.env`:
   ```bash
   grep CYPHT_MODULES .env
   ```
   Should include `ai_reply`

2. Regenerate configuration:
   ```bash
   php scripts/config_gen.php
   ```

3. Clear browser cache completely

4. Check file permissions:
   ```bash
   ls -la modules/ai_reply/
   ```
   All files should be readable

### PHP Errors

**Check for syntax errors:**
```bash
php -l modules/ai_reply/setup.php
php -l modules/ai_reply/modules.php
```

**View PHP error log:**
```bash
tail -50 /var/log/apache2/error.log
# or
tail -50 /var/log/httpd/error_log
```

### Configuration Not Updating

**Problem:** Changes to settings not saving

**Solutions:**
1. Check file permissions on user settings directory
2. Verify `USER_SETTINGS_DIR` in `.env`
3. Check browser console for JavaScript errors
4. Ensure cookies are enabled

### AJAX Requests Failing

**Problem:** AI generation buttons not working

**Solutions:**
1. Open browser DevTools → Network tab
2. Click AI button and check for failed requests
3. Look for 403/404/500 errors
4. Check that `ajax_ai_generate` is in allowed_pages

### Ollama Connection Issues

**Problem:** "AI generation failed" with Ollama

**Solutions:**
1. Verify Ollama is running:
   ```bash
   curl http://localhost:11434/api/tags
   ```

2. Check if model is available:
   ```bash
   ollama list
   ```

3. Pull model if missing:
   ```bash
   ollama pull llama2
   ```

4. Verify API URL in settings: `http://localhost:11434`

### OpenAI API Issues

**Problem:** "AI generation failed" with OpenAI

**Solutions:**
1. Verify API key is correct
2. Check API key has credits/quota
3. Verify API URL: `https://api.openai.com`
4. Test with curl:
   ```bash
   curl https://api.openai.com/v1/models \
     -H "Authorization: Bearer YOUR_API_KEY"
   ```

---

## Debug Mode

Enable debug logging in Cypht:

1. Edit `.env`:
   ```bash
   DEBUG_LOG=true
   ```

2. Regenerate config:
   ```bash
   php scripts/config_gen.php
   ```

3. Check debug output in browser console

---

## Module Reinstallation

If all else fails, reinstall the module:

1. **Backup settings** (if you configured any)

2. **Remove from .env:**
   ```bash
   # Remove ai_reply from CYPHT_MODULES
   ```

3. **Regenerate:**
   ```bash
   php scripts/config_gen.php
   ```

4. **Re-add to .env:**
   ```bash
   # Add ai_reply back to CYPHT_MODULES
   ```

5. **Regenerate again:**
   ```bash
   php scripts/config_gen.php
   ```

6. **Clear browser cache**

---

## Getting Help

### Information to Provide

When reporting issues, include:

1. **Cypht Version:**
   ```bash
   git describe --tags
   ```

2. **PHP Version:**
   ```bash
   php -v
   ```

3. **Module Files Present:**
   ```bash
   ls -la modules/ai_reply/
   ```

4. **Configuration Check:**
   ```bash
   grep ai_reply .env
   ```

5. **Error Messages:**
   - Browser console errors
   - PHP error log entries
   - Network tab failures

6. **Steps to Reproduce**

---

## Known Limitations

1. **Large Models:** May be slow on first request (model loading)
2. **Context Length:** Very long email threads may exceed model limits
3. **HTML Emails:** Generated content is plain text (HTML support planned)
4. **Attachments:** AI doesn't analyze attachments (text only)

---

## Performance Tips

1. **Use Smaller Models:**
   - Ollama: `mistral` instead of `llama2:13b`
   - OpenAI: `gpt-3.5-turbo` instead of `gpt-4`

2. **Local vs Cloud:**
   - Local (Ollama): Slower first request, then fast
   - Cloud (OpenAI): Consistent speed, requires internet

3. **System Prompt:**
   - Keep it concise for faster generation
   - Be specific about desired output format

---

## Security Checklist

- [ ] API keys stored securely (not in version control)
- [ ] Using HTTPS for cloud APIs
- [ ] Reviewed AI provider's privacy policy
- [ ] Using local Ollama for sensitive emails
- [ ] Regularly updating API keys
- [ ] Monitoring API usage/costs

---

## Version History

### v1.0.1 (Current)
- Fixed: Bootstrap icon usage instead of deprecated Hm_Image_Sources
- Fixed: Settings page 500 error
- Improved: Error handling and user feedback

### v1.0.0 (Initial)
- Initial release
- Ollama and OpenAI support
- Reply and prompt generation
- Configurable settings

---

**Last Updated:** 2025-12-15