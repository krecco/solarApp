# CLI API Testing - Prompt Instructions

## For Starting a New Conversation

I've created several prompt options for you to start a new conversation about building CLI API tests:

### 📄 Files Created

1. **`prompt-cli-api-testing.md`** (Comprehensive)
   - Detailed requirements and context
   - Full list of endpoints
   - Example usage scenarios
   - All your requirements spelled out

2. **`prompt-cli-api-testing-short.md`** (Quick Start)
   - Concise version for copy-paste
   - Gets straight to the point
   - Same requirements, less text

3. **`prompt-cli-api-testing-alternative.md`** (Explore Options)
   - Focuses on existing Laravel tools
   - Questions about best approach
   - More exploratory tone

4. **`prompt-cli-api-testing-recommendation.md`** (Summary)
   - Compares different approaches
   - My recommendation (Custom Artisan Commands)
   - Pros and cons of each option

---

## 🚀 Quick Start Instructions

### Step 1: Choose Your Prompt
- **Want to build it?** Use `prompt-cli-api-testing-short.md`
- **Want to explore options?** Use `prompt-cli-api-testing-alternative.md`
- **Want all details?** Use `prompt-cli-api-testing.md`

### Step 2: Start New Conversation
Copy the chosen prompt and paste it into a new Claude conversation.

### Step 3: Specify Your Preference
Tell Claude if you prefer:
- **Artisan Commands** (recommended) - Native Laravel approach
- **Bash Scripts** - Simple curl commands
- **Pest Tests** - Modern testing framework
- **Hybrid** - Mix of approaches

---

## 🎯 What You'll Get

A CLI testing system that lets you:

```bash
# Test registration
php artisan api:test register --email=john@example.com --password=Test123!

# Token is saved automatically to bearer.txt
✅ Registered successfully!
🔑 Token saved to bearer.txt
📊 Database: User #42 and Tenant #15 created

# Use token for authenticated requests
php artisan api:test user
✅ Authenticated as: john@example.com

# Test admin endpoints
php artisan api:test admin:users --use-token=admin-token.txt
✅ Found 47 users
```

---

## 📝 Key Features to Request

Make sure the solution includes:

1. **Token Management**
   - Auto-save tokens to files
   - Auto-load tokens for requests
   - Multiple token file support

2. **Response Handling**
   - Pretty-printed JSON
   - Colored output
   - Save to timestamped files

3. **Database Verification**
   - Show what changed
   - SQL queries to verify
   - Before/after comparisons

4. **Documentation**
   - Inline help for each command
   - Example payloads
   - Expected responses

---

## 🔧 Laravel Context

Laravel doesn't have a built-in "Swagger CLI" tool, but you have options:

1. **Build It** - Custom Artisan commands (best option)
2. **Test It** - Use Pest/PHPUnit with helpers
3. **Script It** - Bash/curl scripts
4. **Document It** - Generate OpenAPI specs

The custom Artisan command approach is recommended because:
- Native to your Laravel app
- Can access database directly
- Reuses your existing code
- Easy to maintain

---

## 💡 Pro Tips

1. **Start Simple** - Build commands for 2-3 endpoints first
2. **Use Base Class** - Share common functionality
3. **Color Output** - Makes it easier to read
4. **Save Everything** - Keep response history
5. **Database Helpers** - Reset between tests

Good luck with your CLI testing system! 🚀
