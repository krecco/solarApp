<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    protected ActivityService $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }
    /**
     * Get all settings (or specific group)
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'group' => 'nullable|string|in:general,email,investment,payment,notification,security,tariff,campaign,web_info',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $cacheKey = 'settings.' . ($request->group ?? 'all');

        $settings = Cache::remember($cacheKey, 3600, function () use ($request) {
            $query = Setting::where('rs', 0);

            if ($request->has('group')) {
                $query->where('group', $request->group);
            }

            return $query->orderBy('group')->orderBy('key')->get();
        });

        // Format settings for easier frontend consumption
        $formatted = [];
        foreach ($settings as $setting) {
            if (!isset($formatted[$setting->group])) {
                $formatted[$setting->group] = [];
            }
            $formatted[$setting->group][$setting->key] = [
                'value' => $setting->value,
                'type' => $setting->type,
                'description' => $setting->description,
                'is_public' => $setting->is_public,
            ];
        }

        return response()->json([
            'data' => $formatted,
        ]);
    }

    /**
     * Get public settings (accessible without authentication)
     */
    public function publicSettings(): JsonResponse
    {
        $settings = Cache::remember('settings.public', 3600, function () {
            return Setting::where('rs', 0)
                ->where('is_public', true)
                ->orderBy('group')
                ->orderBy('key')
                ->get();
        });

        // Format settings
        $formatted = [];
        foreach ($settings as $setting) {
            if (!isset($formatted[$setting->group])) {
                $formatted[$setting->group] = [];
            }
            $formatted[$setting->group][$setting->key] = [
                'value' => $setting->value,
                'type' => $setting->type,
                'description' => $setting->description,
            ];
        }

        return response()->json([
            'data' => $formatted,
        ]);
    }

    /**
     * Get single setting
     */
    public function show(Request $request, string $group, string $key): JsonResponse
    {
        $setting = Setting::where('group', $group)
            ->where('key', $key)
            ->where('rs', 0)
            ->first();

        if (!$setting) {
            return response()->json([
                'message' => 'Setting not found',
            ], 404);
        }

        // Check if setting is public or user is admin
        if (!$setting->is_public && !$request->user()->hasRole('admin', 'sanctum')) {
            return response()->json([
                'message' => 'Unauthorized to view this setting',
            ], 403);
        }

        return response()->json([
            'data' => [
                'group' => $setting->group,
                'key' => $setting->key,
                'value' => $setting->value,
                'type' => $setting->type,
                'description' => $setting->description,
                'is_public' => $setting->is_public,
            ],
        ]);
    }

    /**
     * Update setting (admin only)
     */
    public function update(Request $request, string $group, string $key): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required',
            'type' => 'nullable|in:string,integer,boolean,json,decimal',
            'description' => 'nullable|string|max:500',
            'is_public' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $setting = Setting::where('group', $group)
            ->where('key', $key)
            ->where('rs', 0)
            ->first();

        if (!$setting) {
            return response()->json([
                'message' => 'Setting not found',
            ], 404);
        }

        // Validate value based on type
        $type = $request->input('type', $setting->type);
        $value = $request->value;

        if (!$this->validateSettingValue($value, $type)) {
            return response()->json([
                'message' => 'Invalid value for setting type',
            ], 422);
        }

        $setting->update([
            'value' => $value,
            'type' => $type,
            'description' => $request->input('description', $setting->description),
            'is_public' => $request->input('is_public', $setting->is_public),
        ]);

        // Clear cache
        Cache::forget('settings.all');
        Cache::forget('settings.' . $group);
        Cache::forget('settings.public');

        // Log activity
        $this->activityService->log('updated setting', $setting, $request->user(), [
            'old_value' => $setting->getOriginal('value'),
            'new_value' => $value,
        ]);

        return response()->json([
            'message' => 'Setting updated successfully',
            'data' => $setting,
        ]);
    }

    /**
     * Create new setting (admin only)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'group' => 'required|string|in:general,email,investment,payment,notification,security,tariff,campaign,web_info',
            'key' => 'required|string|max:255',
            'value' => 'required',
            'type' => 'required|in:string,integer,boolean,json,decimal',
            'description' => 'nullable|string|max:500',
            'is_public' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if setting already exists
        $existing = Setting::where('group', $request->group)
            ->where('key', $request->key)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Setting already exists',
            ], 409);
        }

        // Validate value based on type
        if (!$this->validateSettingValue($request->value, $request->type)) {
            return response()->json([
                'message' => 'Invalid value for setting type',
            ], 422);
        }

        $setting = Setting::create([
            'group' => $request->group,
            'key' => $request->key,
            'value' => $request->value,
            'type' => $request->type,
            'description' => $request->description,
            'is_public' => $request->input('is_public', false),
        ]);

        // Clear cache
        Cache::forget('settings.all');
        Cache::forget('settings.' . $request->group);
        Cache::forget('settings.public');

        // Log activity
        $this->activityService->log('created setting', $setting, $request->user());

        return response()->json([
            'message' => 'Setting created successfully',
            'data' => $setting,
        ], 201);
    }

    /**
     * Delete setting (admin only)
     */
    public function destroy(Request $request, string $group, string $key): JsonResponse
    {
        $setting = Setting::where('group', $group)
            ->where('key', $key)
            ->where('rs', 0)
            ->first();

        if (!$setting) {
            return response()->json([
                'message' => 'Setting not found',
            ], 404);
        }

        // Soft delete
        $setting->rs = 99;
        $setting->save();
        $setting->delete();

        // Clear cache
        Cache::forget('settings.all');
        Cache::forget('settings.' . $group);
        Cache::forget('settings.public');

        // Log activity
        $this->activityService->log('deleted setting', $setting, $request->user());

        return response()->json([
            'message' => 'Setting deleted successfully',
        ]);
    }

    /**
     * Bulk update settings (admin only)
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*.group' => 'required|string',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $updated = [];
        $errors = [];

        foreach ($request->settings as $settingData) {
            $setting = Setting::where('group', $settingData['group'])
                ->where('key', $settingData['key'])
                ->where('rs', 0)
                ->first();

            if (!$setting) {
                $errors[] = "Setting {$settingData['group']}.{$settingData['key']} not found";
                continue;
            }

            // Validate value based on type
            if (!$this->validateSettingValue($settingData['value'], $setting->type)) {
                $errors[] = "Invalid value for setting {$settingData['group']}.{$settingData['key']}";
                continue;
            }

            $setting->update(['value' => $settingData['value']]);
            $updated[] = $setting;
        }

        // Clear all caches
        Cache::forget('settings.all');
        Cache::forget('settings.general');
        Cache::forget('settings.email');
        Cache::forget('settings.investment');
        Cache::forget('settings.payment');
        Cache::forget('settings.notification');
        Cache::forget('settings.security');
        Cache::forget('settings.tariff');
        Cache::forget('settings.campaign');
        Cache::forget('settings.web_info');
        Cache::forget('settings.public');

        // Log activity
        $this->activityService->log('bulk updated settings', null, $request->user(), [
            'updated_count' => count($updated),
            'error_count' => count($errors),
        ]);

        return response()->json([
            'message' => 'Settings updated',
            'data' => [
                'updated_count' => count($updated),
                'updated' => $updated,
                'errors' => $errors,
            ],
        ]);
    }

    /**
     * Reset settings to default (admin only)
     */
    public function reset(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'group' => 'nullable|string|in:general,email,investment,payment,notification,security,tariff,campaign,web_info',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Get default settings
        $defaults = $this->getDefaultSettings();

        $resetCount = 0;

        if ($request->has('group')) {
            // Reset specific group
            $group = $request->group;
            if (isset($defaults[$group])) {
                foreach ($defaults[$group] as $key => $config) {
                    $setting = Setting::where('group', $group)
                        ->where('key', $key)
                        ->first();

                    if ($setting) {
                        $setting->update(['value' => $config['value']]);
                        $resetCount++;
                    }
                }
            }
        } else {
            // Reset all settings
            foreach ($defaults as $group => $settings) {
                foreach ($settings as $key => $config) {
                    $setting = Setting::where('group', $group)
                        ->where('key', $key)
                        ->first();

                    if ($setting) {
                        $setting->update(['value' => $config['value']]);
                        $resetCount++;
                    }
                }
            }
        }

        // Clear cache
        Cache::forget('settings.all');
        Cache::forget('settings.' . ($request->group ?? 'all'));
        Cache::forget('settings.public');

        // Log activity
        $this->activityService->log('reset settings to default', null, $request->user(), [
            'group' => $request->group ?? 'all',
            'reset_count' => $resetCount,
        ]);

        return response()->json([
            'message' => 'Settings reset to default',
            'data' => [
                'reset_count' => $resetCount,
            ],
        ]);
    }

    /**
     * Validate setting value based on type
     */
    protected function validateSettingValue($value, string $type): bool
    {
        switch ($type) {
            case 'integer':
                return is_numeric($value);
            case 'boolean':
                return in_array($value, [true, false, 1, 0, '1', '0', 'true', 'false'], true);
            case 'json':
                if (is_string($value)) {
                    json_decode($value);
                    return json_last_error() === JSON_ERROR_NONE;
                }
                return is_array($value) || is_object($value);
            case 'decimal':
                return is_numeric($value);
            case 'string':
            default:
                return true;
        }
    }

    /**
     * Get default settings configuration
     */
    protected function getDefaultSettings(): array
    {
        return [
            'general' => [
                'app_name' => ['value' => 'Solar Planning', 'type' => 'string'],
                'maintenance_mode' => ['value' => false, 'type' => 'boolean'],
                'default_language' => ['value' => 'de', 'type' => 'string'],
                'default_currency' => ['value' => 'EUR', 'type' => 'string'],
            ],
            'email' => [
                'from_address' => ['value' => 'noreply@solarplanning.com', 'type' => 'string'],
                'from_name' => ['value' => 'Solar Planning', 'type' => 'string'],
                'admin_email' => ['value' => 'admin@solarplanning.com', 'type' => 'string'],
            ],
            'investment' => [
                'min_investment_amount' => ['value' => 500, 'type' => 'decimal'],
                'max_investment_amount' => ['value' => 100000, 'type' => 'decimal'],
                'default_interest_rate' => ['value' => 4.5, 'type' => 'decimal'],
                'default_duration_months' => ['value' => 120, 'type' => 'integer'],
                'auto_verify_investments' => ['value' => false, 'type' => 'boolean'],
            ],
            'payment' => [
                'payment_provider' => ['value' => 'sepa', 'type' => 'string'],
                'enable_instant_payments' => ['value' => false, 'type' => 'boolean'],
            ],
            'notification' => [
                'send_welcome_email' => ['value' => true, 'type' => 'boolean'],
                'send_investment_confirmations' => ['value' => true, 'type' => 'boolean'],
                'send_repayment_reminders' => ['value' => true, 'type' => 'boolean'],
                'reminder_days_before_due' => ['value' => 7, 'type' => 'integer'],
            ],
            'security' => [
                'require_email_verification' => ['value' => true, 'type' => 'boolean'],
                'enable_2fa' => ['value' => false, 'type' => 'boolean'],
                'session_lifetime' => ['value' => 120, 'type' => 'integer'],
                'password_min_length' => ['value' => 8, 'type' => 'integer'],
            ],
            'tariff' => [
                'base_tariff_rate' => ['value' => 0.28, 'type' => 'decimal'],
                'peak_tariff_rate' => ['value' => 0.35, 'type' => 'decimal'],
                'off_peak_tariff_rate' => ['value' => 0.22, 'type' => 'decimal'],
                'feed_in_tariff' => ['value' => 0.12, 'type' => 'decimal'],
                'grid_fee_per_kwh' => ['value' => 0.08, 'type' => 'decimal'],
                'renewable_energy_levy' => ['value' => 0.065, 'type' => 'decimal'],
                'tariff_currency' => ['value' => 'EUR', 'type' => 'string'],
            ],
            'campaign' => [
                'enable_referral_program' => ['value' => true, 'type' => 'boolean'],
                'referral_bonus_amount' => ['value' => 50, 'type' => 'decimal'],
                'enable_seasonal_campaigns' => ['value' => true, 'type' => 'boolean'],
                'min_investment_for_bonus' => ['value' => 5000, 'type' => 'decimal'],
            ],
            'web_info' => [
                'show_public_projects' => ['value' => true, 'type' => 'boolean'],
                'show_statistics' => ['value' => true, 'type' => 'boolean'],
                'show_news' => ['value' => true, 'type' => 'boolean'],
                'news_items_per_page' => ['value' => 10, 'type' => 'integer'],
            ],
        ];
    }
}
