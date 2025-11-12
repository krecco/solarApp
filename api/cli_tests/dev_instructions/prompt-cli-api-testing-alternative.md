# Alternative Approach - Laravel Testing Tools

If you prefer using existing Laravel tools, you could ask:

---

I want to create API tests for my Laravel 12 SaaS app at `/home/test/saas/saas-central`. I need to test all API endpoints interactively from the command line.

**Current tools I'm aware of:**
- Laravel HTTP tests
- Pest PHP
- Laravel Dusk
- Postman/Newman
- Laravel Telescope

**My specific needs:**
1. Run individual endpoint tests (not full suite)
2. Save auth tokens between requests
3. See pretty-printed responses
4. Verify database changes
5. Work from CLI, not browser

**Questions:**
1. Can I use Laravel's built-in HTTP tests interactively?
2. Would Pest PHP's architecture work for this?
3. Should I create a Postman collection and run with Newman CLI?
4. Is there a Laravel package that provides Swagger-like CLI testing?
5. Should I just write custom Artisan commands?

Example of what I want:
```bash
php artisan tinker
>>> $response = Http::post('/api/v1/register', [...])
>>> $token = $response->json()['token']
>>> Http::withToken($token)->get('/api/v1/user')
```

But more structured and reusable. What's the best approach?
