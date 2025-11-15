# Laravel API + Vue 3 Frontend Integration Guide

**Complete reference for avoiding common integration errors**
**Based on real debugging session - Laravel 11 + Vue 3**

---

## 📖 Table of Contents

1. [Quick Start Checklist](#quick-start-checklist) - Print this!
2. [Common Errors & Fixes](#common-errors--fixes) - Fast solutions
3. [Complete Integration Checklist](#complete-integration-checklist) - Detailed guide
4. [Quick Reference Commands](#quick-reference-commands) - Emergency toolkit

---

# Quick Start Checklist

**Print this section and keep it visible while coding**

## Before Writing Code

```
☐ Check migration file exists and has correct columns
☐ Verify all required packages are in composer.json
☐ Check filesystem disk is configured
☐ Create required storage directories
```

## Backend Implementation

```
☐ Controller validation rules defined
☐ Service method exists
☐ Method returns correct type (object vs path)
☐ Column names match migration
☐ Test with curl/Postman (NOT browser)
☐ Check Laravel logs for errors
```

**Quick test:**
```bash
curl -X POST http://localhost:8000/api/v1/endpoint \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"required_field": "value"}'
```

## Frontend Implementation

```
☐ API service method created
☐ All required fields included in request
☐ Field names match backend (snake_case)
☐ Binary responses use responseType: 'blob'
☐ Blob created from response.data (not response)
☐ Loading states implemented
☐ Error handling implemented
☐ Success notifications shown
```

**Binary response pattern:**
```typescript
const response = await apiClient.get('/api/pdf', {
  responseType: 'blob'
})
const blob = new Blob([response.data], { type: 'application/pdf' })
const url = window.URL.createObjectURL(blob)
window.open(url, '_blank')
setTimeout(() => window.URL.revokeObjectURL(url), 100)
```

## Integration Testing

```
☐ Network tab shows correct request payload
☐ Response status is 200 (not 422, 500)
☐ Database updated correctly
☐ No console errors
☐ UI updates reflect changes
☐ Error cases handled gracefully
```

## Common Gotchas

| Issue | Fix |
|-------|-----|
| "Field required" | Frontend missing field in request |
| "Disk not found" | Add to config/filesystems.php |
| "Class not found" | composer require package-name |
| "Column not exists" | Code uses wrong column name |
| "Method not found" | Service missing method |
| "PDF won't load" | Use response.data not response |
| "500 error" | Check Laravel logs |

## After Any Config Change

```bash
php artisan config:clear
php artisan cache:clear
composer dump-autoload
# Restart Laravel server
```

## Field Name Mapping

| Frontend (camelCase) | Backend (snake_case) |
|---------------------|---------------------|
| paymentMethod | payment_method |
| fileName | original_name (check migration!) |
| isVerified | is_verified |

**Always check migration for actual column names!**

## Validation Template

```php
// Backend
$validator = Validator::make($request->all(), [
    'amount' => 'required|numeric|min:0',
    'payment_method' => 'nullable|string',
]);
```

```typescript
// Frontend - MUST match
const payload = {
  amount: 1450.00,  // required
  payment_method: 'bank_transfer'  // nullable
}
```

**🎯 Golden Rule: Test backend with curl BEFORE testing in browser**

---

# Common Errors & Fixes

**Quick solutions for the most common errors**

## ❌ Error: "Validation failed - field is required"

**Symptom:**
```json
{
    "message": "Validation failed",
    "errors": {
        "amount": ["The amount field is required."]
    }
}
```

**Cause:** Frontend doesn't send required field

**Fix:**
```typescript
// ❌ WRONG - Missing required field
await api.post('/endpoint', {
  payment_method: form.method
})

// ✅ CORRECT - Include all required fields
await api.post('/endpoint', {
  amount: repayment.amount,        // ← Add missing field
  payment_method: form.method
})
```

---

## ❌ Error: "Disk [private] does not have a configured driver"

**Cause:** Disk not configured in `config/filesystems.php`

**Fix:**
```bash
# 1. Add to config/filesystems.php
'private' => [
    'driver' => 'local',
    'root' => storage_path('app/private'),
    'visibility' => 'private',
],

# 2. Create directory
mkdir -p storage/app/private

# 3. Clear cache
php artisan config:clear && php artisan config:cache
```

---

## ❌ Error: "Class not found" (e.g., DomPDF)

**Cause:** Required package not installed

**Fix:**
```bash
# Install the package
composer require barryvdh/laravel-dompdf

# Clear caches
php artisan config:clear
composer dump-autoload

# Restart Laravel server
```

---

## ❌ Error: "Undefined column: containerable_type"

**Cause:** Code uses polymorphic relations but migration uses foreign keys

**Fix:**
```php
// ❌ WRONG - Assuming polymorphic
FileContainer::where('containerable_type', Investment::class)
    ->where('containerable_id', $id)
    ->first();

// ✅ CORRECT - Use actual foreign key
$investment = Investment::find($id);
FileContainer::find($investment->file_container_id);
```

---

## ❌ Error: "Call to undefined method streamPdf()"

**Cause:** Method doesn't exist in service

**Fix:**
```php
// Add missing method to ContractGeneratorService
public function streamPdf($pdf)
{
    if (is_object($pdf) && method_exists($pdf, 'stream')) {
        return $pdf->stream();
    }

    if (Storage::disk('private')->exists($pdf)) {
        return response()->file(Storage::disk('private')->path($pdf));
    }

    abort(404, 'PDF not found');
}
```

---

## ❌ Error: "Failed to load PDF document" (blob error)

**Cause:** Wrong blob creation - wrapping entire response instead of response.data

**Fix:**
```typescript
// ❌ WRONG
const blob = new Blob([response], { type: 'application/pdf' })

// ✅ CORRECT
const blob = new Blob([response.data], { type: 'application/pdf' })
```

**Full working example:**
```typescript
const response = await apiClient.get('/api/pdf/contract', {
  responseType: 'blob'  // Important!
})

const blob = new Blob([response.data], { type: 'application/pdf' })
const url = window.URL.createObjectURL(blob)
window.open(url, '_blank')

setTimeout(() => window.URL.revokeObjectURL(url), 100)
```

---

## ❌ Error: 500 Internal Server Error (PDF generation)

**Common causes:**
1. Template view doesn't exist
2. Missing data in template
3. Syntax error in Blade template

**Debug:**
```bash
# 1. Check if template exists
ls resources/views/pdfs/en/investment-contract.blade.php

# 2. Check Laravel logs
tail -f storage/logs/laravel.log

# 3. Test PDF generation in tinker
php artisan tinker
>>> $investment = Investment::find('ID');
>>> $pdf = Pdf::loadView('pdfs.en.investment-contract', ['investment' => $investment]);
>>> $pdf->stream();  // Should show errors if template is broken
```

---

## ❌ Error: Incorrect field names (filename vs original_name)

**Cause:** Code uses old field names that don't match migration

**Fix:**
```php
// Check actual columns
php artisan tinker
>>> Schema::getColumnListing('files')

// Update code to match
// ❌ WRONG
$file->filename = 'test.pdf';
$file->stored_filename = 'uuid.pdf';
$file->file_type = 'contract';

// ✅ CORRECT (based on actual migration)
$file->original_name = 'test.pdf';
$file->name = 'uuid.pdf';
$file->document_type = 'contract';
```

---

## ❌ Error: Opening PDF URL directly gives 500/401

**Cause:** Trying to open API URL directly bypasses authentication

**Fix:**
```typescript
// ❌ WRONG - Bypasses auth, insecure
window.open(`/api/v1/pdf/contract/${id}`, '_blank')

// ✅ CORRECT - Fetch through API client with auth
const response = await apiClient.get(`/api/v1/pdf/contract/${id}`, {
  responseType: 'blob'
})
const blob = new Blob([response.data], { type: 'application/pdf' })
const url = window.URL.createObjectURL(blob)
window.open(url, '_blank')
```

---

# Complete Integration Checklist

**Comprehensive guide for implementing and debugging API integrations**

## 🔍 1. API Endpoint Implementation Verification

### Before claiming "it works", verify these:

#### ✅ API Request/Response Contract
```bash
# Test with curl or Postman first, not just the UI
curl -X POST http://localhost:8000/api/v1/endpoint \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"field": "value"}'
```

**Common Issues:**
- ❌ Frontend sends `payment_method` but backend expects `paymentMethod` (naming convention mismatch)
- ❌ Frontend doesn't send required field (e.g., `amount` missing)
- ❌ Backend validation rules don't match frontend form fields

**Fix:**
```php
// Laravel Controller - Check validation rules
$validator = Validator::make($request->all(), [
    'amount' => 'required|numeric|min:0',  // ← Is this field sent by frontend?
    'payment_method' => 'nullable|string',  // ← Does frontend use snake_case or camelCase?
]);
```

```typescript
// Vue Frontend - Match backend expectations
await api.post('/endpoint', {
  amount: repayment.amount,        // ← Required field must be included
  payment_method: form.method,     // ← Use snake_case for Laravel
})
```

---

## 🗄️ 2. Database Schema vs Code Alignment

### Always verify column names match your code

**Common Issues:**
- ❌ Code uses `containerable_type` and `containerable_id` but migration doesn't have them
- ❌ Code uses `filename` but database has `original_name`
- ❌ Code uses `rs` column for soft deletes but table doesn't have it
- ❌ Code uses `verified` but database has `is_verified`

**How to Check:**
```bash
# View actual table structure
php artisan tinker
>>> Schema::getColumnListing('files')
=> ["id", "file_container_id", "name", "original_name", "path", ...]

# Or check migration files
ls database/migrations/*create_table_name*
cat database/migrations/2025_11_12_000003_create_file_containers_table.php
```

**Fix:**
```php
// Wrong - using non-existent column
$file->filename = 'test.pdf';

// Correct - use actual column name from migration
$file->original_name = 'test.pdf';
```

---

## 📦 3. Required Packages Installation

### Don't assume packages are installed

**Common Issues:**
- ❌ `Class "Barryvdh\DomPDF\Facade\Pdf" not found` → DomPDF not installed
- ❌ `Driver [s3] is not supported` → AWS SDK not installed
- ❌ `Class "Intervention\Image\ImageManager" not found` → Image package missing

**Always verify:**
```bash
# Check if package exists in composer.json
cat composer.json | grep "package-name"

# If missing, install it
composer require barryvdh/laravel-dompdf
composer require intervention/image
composer require aws/aws-sdk-php

# Clear cache after installing
php artisan config:clear
php artisan cache:clear
composer dump-autoload
```

---

## 💾 4. Laravel Filesystem Disk Configuration

### Disk must be configured before use

**Common Issues:**
- ❌ `Disk [private] does not have a configured driver` → Not configured in `config/filesystems.php`
- ❌ `Storage::disk('uploads')` fails → Custom disk not defined

**How to Check:**
```bash
cat config/filesystems.php | grep -A 5 "'private'"
```

**Fix:**
```php
// config/filesystems.php
'disks' => [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app/private'),
    ],

    // Add missing disk
    'private' => [
        'driver' => 'local',
        'root' => storage_path('app/private'),
        'visibility' => 'private',
        'throw' => false,
    ],
],
```

**Create required directories:**
```bash
mkdir -p storage/app/private/{contracts,uploads,temp}
chmod -R 775 storage/app/private
```

---

## 🔐 5. Binary/Blob Response Handling (PDFs, Images, ZIPs)

### Frontend blob handling patterns

**Common Issues:**
- ❌ Creating blob from entire `response` object instead of `response.data`
- ❌ Opening API URL directly: `window.open('/api/pdf/...', '_blank')` → Insecure, bypasses auth
- ❌ Wrong Content-Type header causes download instead of display

**Wrong:**
```typescript
// ❌ WRONG - Security risk + doesn't work with auth
window.open(`/api/v1/pdf/investment/${id}/contract`, '_blank')

// ❌ WRONG - Wrapping entire response object
const blob = new Blob([response], { type: 'application/pdf' })
```

**Correct:**
```typescript
// ✅ CORRECT - Fetch through API client with auth
const response = await apiClient.get(
  `/api/v1/pdf/investments/${id}/contract`,
  { responseType: 'blob' }  // Important!
)

// ✅ CORRECT - Use response.data (axios returns blob in .data)
const blob = new Blob([response.data], { type: 'application/pdf' })
const url = window.URL.createObjectURL(blob)
window.open(url, '_blank')

// ✅ Clean up
setTimeout(() => window.URL.revokeObjectURL(url), 100)
```

**Backend:**
```php
// Stream PDF directly (in-memory, no disk save)
return $pdf->stream();

// Or download
return $pdf->download('filename.pdf');
```

---

## 📝 6. Service Method Return Types

### Methods should return what they claim

**Common Issues:**
- ❌ Method generates PDF in-memory but always saves to disk
- ❌ Method returns file path but controller expects PDF object for streaming
- ❌ No flexibility between "stream" vs "save" modes

**Fix - Support both modes:**
```php
public function generatePdf(Investment $investment, array $options = [])
{
    $pdf = Pdf::loadView('template', $data);

    // If save option is true, save to storage and return path
    if (isset($options['save']) && $options['save']) {
        $path = "contracts/{$investment->id}/contract.pdf";
        Storage::disk('private')->put($path, $pdf->output());
        return $path;  // Returns string path
    }

    // Otherwise return PDF object for streaming
    return $pdf;  // Returns PDF object
}

// Controller can handle both
public function generateContract(Investment $investment)
{
    $pdf = $this->service->generatePdf($investment);

    // Stream (no disk save)
    return $pdf->stream();
}
```

**Helper methods should be flexible:**
```php
public function streamPdf($pdf)
{
    // Handle PDF object
    if (is_object($pdf) && method_exists($pdf, 'stream')) {
        return $pdf->stream();
    }

    // Handle file path
    if (is_string($pdf) && Storage::disk('private')->exists($pdf)) {
        return response()->file(Storage::disk('private')->path($pdf));
    }

    abort(404, 'PDF not found');
}
```

---

## 🧪 7. Testing Checklist Before Claiming "Done"

### Minimum tests before saying a feature works:

#### ✅ Backend API Test
```bash
# 1. Test with curl/Postman first (not browser)
curl -X POST http://localhost:8000/api/v1/repayments/123/mark-paid \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 1450.00,
    "payment_method": "bank_transfer",
    "payment_reference": "REF123"
  }'

# Expected: 200 OK with success message
# If 422: Check validation errors - what field is missing?
# If 500: Check logs - what's the actual error?
```

#### ✅ Database Verification
```bash
php artisan tinker
>>> $repayment = InvestmentRepayment::find('123');
>>> $repayment->status; // Should be 'paid'
>>> $repayment->paid_amount; // Should match amount sent
```

#### ✅ Frontend Integration Test
```typescript
// Open browser console and check:
// 1. Network tab - what was actually sent?
// 2. Response - what did backend return?
// 3. Console errors - any JavaScript errors?
```

---

## 🚨 8. Common Laravel-Vue Pitfalls

### Polymorphic Relations
**Issue:** Code assumes `morphTo('containerable')` exists but migration has `file_container_id` foreign key instead.

**Check:**
```bash
# Does migration use polymorphic columns?
cat database/migrations/*_table.php | grep "morphs\|morphTo"

# If NO morphs found, use regular foreign key
Schema::create('files', function (Blueprint $table) {
    $table->foreignUuid('file_container_id')->constrained();
    // NOT: $table->morphs('containerable');
});
```

### Field Name Conventions
**Issue:** Frontend uses camelCase, backend uses snake_case.

**Solutions:**
```php
// Option 1: Accept both in backend
'paymentMethod' => 'sometimes|string',
'payment_method' => 'sometimes|string',

// Option 2: Transform in frontend
const snakeCase = (str) => str.replace(/[A-Z]/g, letter => `_${letter.toLowerCase()}`);

// Option 3: Use Laravel API Resources to transform output
class FileResource extends JsonResource {
    public function toArray($request) {
        return [
            'fileName' => $this->original_name, // Transform to camelCase
            'fileSize' => $this->size,
        ];
    }
}
```

### Blob Downloads
**Issue:** ZIP/PDF downloads fail or show corrupted data.

**Always use:**
```php
// Backend
return response()->download($zipPath)->deleteFileAfterSend(true);

// Frontend
const response = await apiClient.post('/bulk-download', data, {
    responseType: 'blob'  // MUST specify blob for binary data
});

const blob = new Blob([response.data], { type: 'application/zip' });
const url = window.URL.createObjectURL(blob);
const link = document.createElement('a');
link.href = url;
link.download = 'archive.zip';
link.click();
window.URL.revokeObjectURL(url);
```

---

## 📋 9. Pre-Flight Checklist Template

Copy this checklist before implementing any API endpoint:

```markdown
## Endpoint: POST /api/v1/endpoint-name

### Backend
- [ ] Migration matches code (column names, types)
- [ ] Validation rules defined in controller
- [ ] All required packages installed (composer.json)
- [ ] Service methods exist and work
- [ ] Database queries use correct column names
- [ ] Tested with curl/Postman
- [ ] Error responses return proper status codes
- [ ] Success response returns expected data structure

### Frontend
- [ ] API service method created
- [ ] Request payload matches backend validation
- [ ] Field names match (camelCase vs snake_case)
- [ ] All required fields included
- [ ] Response handling (success + error)
- [ ] Loading states implemented
- [ ] Toast notifications for feedback
- [ ] Blob responses use `responseType: 'blob'`
- [ ] Binary data uses `response.data` not `response`

### Integration
- [ ] End-to-end test in browser
- [ ] Network tab shows correct request/response
- [ ] Database updated correctly
- [ ] No console errors
- [ ] UI updates reflect changes
- [ ] Error cases handled gracefully
```

---

# Quick Reference Commands

**Emergency debug toolkit**

## 🔧 When Things Don't Work

```bash
# 1. Check actual table structure
php artisan tinker
>>> Schema::getColumnListing('table_name')

# 2. Check if package is installed
composer show | grep package-name

# 3. Clear ALL caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload

# 4. Check filesystem disk configuration
php artisan tinker
>>> config('filesystems.disks.private')

# 5. Test API endpoint directly
curl -X POST http://localhost:8000/api/v1/endpoint \
  -H "Authorization: Bearer $(cat storage/test_token.txt)" \
  -H "Content-Type: application/json" \
  -d '{"test": "data"}' \
  -v

# 6. Check Laravel logs
tail -f storage/logs/laravel.log

# 7. Verify storage permissions
ls -la storage/app/private
chmod -R 775 storage/app/private
```

---

## 🚨 Emergency Fixes

**"It was working yesterday, now it's broken!"**

```bash
# Nuclear option - clear everything
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload

# Restart Laravel server
# Ctrl+C then restart

# Clear browser cache + hard reload
# Cmd+Shift+R (Mac) or Ctrl+Shift+R (Windows)
```

---

## 📌 Most Common Issues We Solved

Based on actual debugging session:

| # | Issue | Location | Fix |
|---|-------|----------|-----|
| 1 | Missing required field in API request | Frontend | Add `amount` field to payload |
| 2 | PDF generation error - class not found | Backend | `composer require barryvdh/laravel-dompdf` |
| 3 | Disk [private] not configured | Backend | Add disk to `config/filesystems.php` |
| 4 | Polymorphic columns don't exist | Backend | Use actual FK: `file_container_id` |
| 5 | Wrong field names (filename vs original_name) | Backend | Check migration, use correct columns |
| 6 | streamPdf() method not found | Backend | Add method to service |
| 7 | PDF blob won't load | Frontend | Use `response.data` not `response` |
| 8 | Opening PDF URL gives 401 | Frontend | Fetch via API client, create blob URL |

---

## 🎯 Key Takeaways

1. **Never trust assumptions** - Always verify with curl/tinker before UI testing
2. **Check migrations first** - Database schema is source of truth
3. **Match field names** - Backend columns, frontend properties, API payload
4. **Test binary responses** - PDFs/ZIPs need `responseType: 'blob'` and `response.data`
5. **Configure before use** - Disks, packages, directories must exist
6. **Return appropriate types** - Don't return paths when objects are expected
7. **Handle both modes** - Stream vs Save, Download vs View
8. **Clear caches religiously** - Config/route/view caches can hide changes

---

## 📖 How to Use This Guide

### Starting a New Feature
```
1. Use "Pre-Flight Checklist Template"
2. Implement backend → Test with curl
3. Implement frontend → Check integration
4. If errors → Jump to "Common Errors & Fixes"
```

### Debugging Existing Code
```
1. Find error in "Common Errors & Fixes"
2. Apply fix
3. If not found → Use "Quick Reference Commands"
4. Still broken? → Read "Complete Integration Checklist"
```

### Code Review
```
Use "Pre-Flight Checklist Template" to verify completeness
```

---

## 🤝 Using in Other Projects

1. **Copy this file to new project root**
   ```bash
   cp LARAVEL_VUE_INTEGRATION_GUIDE.md /path/to/new/project/
   ```

2. **Customize for your stack**
   - Update package names if different
   - Adjust field naming conventions
   - Add project-specific patterns

3. **Keep updated**
   - Add new errors as encountered
   - Document project-specific quirks
   - Share with team

---

**Remember:** The best debugging tool is systematic verification, not random trial and error.

---

**Created:** 2025-11-14
**Based on:** Real debugging session - Laravel 11 + Vue 3 + PrimeVue
