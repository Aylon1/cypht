# AI Reply Module - Debug Checklist

## Issue: Buttons Not Responding on Compose Page

### Step 1: Verify Buttons Are Rendered

1. **Go to Compose page** in Cypht
2. **Open Browser DevTools** (F12)
3. **Go to Elements/Inspector tab**
4. **Search for** `ai_generate_prompt` or `ai_reply_tools`
5. **Check if you see:**
   ```html
   <div class="ai_reply_tools mt-3 mb-3">
     <div class="btn-group" role="group">
       <button type="button" class="btn btn-outline-primary btn-sm ai_generate_prompt">
   ```

**If buttons are NOT visible in HTML:**
- Module output not being rendered
- Check if module is properly enabled
- Regenerate config: `php scripts/config_gen.php`

**If buttons ARE visible but not clickable:**
- JavaScript not binding to buttons
- Continue to Step 2

---

### Step 2: Check JavaScript Console for Errors

1. **Open Browser DevTools** (F12)
2. **Go to Console tab**
3. **Look for any RED errors**
4. **Common errors:**
   - `Hm_Ajax is not defined`
   - `Hm_Notices is not defined`
   - `bootstrap is not defined`
   - `$ is not defined`

**If you see errors:**
- Note the exact error message
- JavaScript dependencies may be missing

---

### Step 3: Test Button Click Manually

1. **Open Browser DevTools** (F12)
2. **Go to Console tab**
3. **Type and press Enter:**
   ```javascript
   $('.ai_generate_prompt').length
   ```
4. **Should return:** `1` (or higher number)

**If it returns 0:**
- Buttons not in DOM
- Check Step 1 again

**If it returns 1 or more:**
5. **Try clicking manually via console:**
   ```javascript
   $('.ai_generate_prompt').click()
   ```

**If modal opens:**
- JavaScript is working, but event binding might be timing issue

**If nothing happens:**
- Event handler not attached
- Continue to Step 4

---

### Step 4: Check if JavaScript Functions Exist

In Browser Console, type:

```javascript
typeof ai_generate_reply
```

**Should return:** `"function"`

**If it returns `"undefined"`:**
- JavaScript not loaded properly
- Module JS not compiled into site.js
- Run: `php scripts/config_gen.php`

---

### Step 5: Manually Bind Event (Temporary Test)

In Browser Console, paste this:

```javascript
$(document).on('click', '.ai_generate_prompt', function() {
    alert('Button clicked!');
    var modal = new bootstrap.Modal(document.getElementById('aiPromptModal'));
    modal.show();
});
```

**If alert shows and modal opens:**
- Event binding is the issue
- JavaScript timing problem
- Continue to Step 6

---

### Step 6: Check Page Name Detection

In Browser Console, type:

```javascript
hm_page_name()
```

**Should return:** `"compose"`

**If it returns something else:**
- JavaScript thinks you're not on compose page
- Event handlers only bind on compose page
- This is the likely issue!

---

### Step 7: Force Event Binding (Temporary Fix)

If `hm_page_name()` returns wrong value, manually bind events in console:

```javascript
// Bind AI Generate button
$(document).on('click', '.ai_generate_prompt', function() {
    $('#ai_custom_prompt').val('');
    var modal = new bootstrap.Modal(document.getElementById('aiPromptModal'));
    modal.show();
});

// Bind Generate button in modal
$(document).on('click', '#ai_generate_from_prompt_btn', function() {
    var prompt = $('#ai_custom_prompt').val();
    if (!prompt) {
        alert('Please enter a prompt');
        return;
    }
    
    $('#ai_generate_from_prompt_btn').prop('disabled', true).html('Generating...');
    
    Hm_Ajax.request(
        [
            {'name': 'hm_ajax_hook', 'value': 'ajax_ai_generate'},
            {'name': 'ai_mode', 'value': 'prompt'},
            {'name': 'ai_prompt', 'value': prompt}
        ],
        function(res) {
            $('#ai_generate_from_prompt_btn').prop('disabled', false).html('Generate');
            if (res.ai_generated_text) {
                $('.compose_body').val(res.ai_generated_text);
                var modal = bootstrap.Modal.getInstance(document.getElementById('aiPromptModal'));
                if (modal) modal.hide();
                alert('Generated!');
            } else {
                alert('Failed to generate');
            }
        }
    );
});
```

---

### Step 8: Test API Connection

In Browser Console:

```javascript
Hm_Ajax.request(
    [
        {'name': 'hm_ajax_hook', 'value': 'ajax_ai_generate'},
        {'name': 'ai_mode', 'value': 'prompt'},
        {'name': 'ai_prompt', 'value': 'Say hello'}
    ],
    function(res) {
        console.log('Response:', res);
        if (res.ai_generated_text) {
            console.log('Generated text:', res.ai_generated_text);
        }
    }
);
```

**Check Console for:**
- Network request to `?page=compose&hm_ajax_hook=ajax_ai_generate`
- Response with `ai_generated_text`

---

### Step 9: Check Settings Are Saved

1. **Go to Settings page**
2. **Scroll to AI Reply Settings**
3. **Verify values are filled:**
   - AI Provider: `OpenAI Compatible`
   - API URL: `http://localhost:8001`
   - Model Name: `/models/Ministral-3-14B-Instruct-2512`

4. **Click Save**
5. **Refresh page**
6. **Check values are still there**

**If values disappear:**
- Settings not saving properly
- Check file permissions on user settings directory

---

### Step 10: Test vLLM API Directly

From terminal:

```bash
curl -X POST http://localhost:8001/v1/chat/completions \
  -H "Content-Type: application/json" \
  -d '{
    "model": "/models/Ministral-3-14B-Instruct-2512",
    "messages": [
      {"role": "system", "content": "You are helpful."},
      {"role": "user", "content": "Say hello"}
    ],
    "max_tokens": 50
  }'
```

**Should return JSON with:**
```json
{
  "choices": [
    {
      "message": {
        "content": "Hello! ..."
      }
    }
  ]
}
```

**If this fails:**
- vLLM not working properly
- Wrong port or model name
- Check: `curl http://localhost:8001/v1/models`

---

## Quick Fixes

### Fix 1: Clear Browser Cache
```
Ctrl+Shift+R (Windows/Linux)
Cmd+Shift+R (Mac)
```

### Fix 2: Regenerate Config
```bash
cd /var/www/cypht
php scripts/config_gen.php
```

### Fix 3: Check Module is Enabled
```bash
grep CYPHT_MODULES .env
# Should include: ai_reply
```

### Fix 4: Restart Apache/Nginx
```bash
sudo systemctl restart apache2
# or
sudo systemctl restart nginx
```

---

## Most Likely Issues

### Issue 1: Page Name Detection
**Symptom:** Buttons visible but not clickable
**Cause:** `hm_page_name()` not returning "compose"
**Fix:** Check Step 6 above

### Issue 2: JavaScript Not Loaded
**Symptom:** `typeof ai_generate_reply` returns "undefined"
**Cause:** Module JS not compiled
**Fix:** Run `php scripts/config_gen.php`

### Issue 3: Bootstrap Modal Not Available
**Symptom:** Error: "bootstrap is not defined"
**Cause:** Bootstrap JS not loaded
**Fix:** Check if Bootstrap is included in page

### Issue 4: Settings Not Saved
**Symptom:** API calls fail with wrong URL
**Cause:** Settings not persisting
**Fix:** Check file permissions, verify settings save

---

## Report Back

After going through these steps, report:

1. **Step 1 result:** Are buttons in HTML? (Yes/No)
2. **Step 3 result:** What does `$('.ai_generate_prompt').length` return?
3. **Step 4 result:** What does `typeof ai_generate_reply` return?
4. **Step 6 result:** What does `hm_page_name()` return?
5. **Step 10 result:** Does curl command work? (Yes/No)
6. **Any console errors:** Copy exact error messages

This will help identify the exact issue!