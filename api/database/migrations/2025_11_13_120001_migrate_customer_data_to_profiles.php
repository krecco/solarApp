<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migrate existing customer-specific data from users to customer_profiles
     */
    public function up(): void
    {
        // Get all users that have customer-related data
        $users = DB::table('users')
            ->whereNull('deleted_at')
            ->get();

        foreach ($users as $user) {
            // Only create profile if user has customer-specific data or is a customer/investor
            $hasCustomerData = $user->customer_no
                || $user->customer_type
                || $user->is_business
                || $user->user_files_verified
                || $user->document_extra_text_block_a
                || $user->document_extra_text_block_b;

            if ($hasCustomerData) {
                DB::table('customer_profiles')->insert([
                    'user_id' => $user->id,
                    'customer_no' => $user->customer_no,
                    'customer_type' => $user->customer_type ?? 'investor',
                    'is_business' => $user->is_business ?? false,
                    'title_prefix' => $user->title_prefix,
                    'title_suffix' => $user->title_suffix,
                    'phone_nr' => $user->phone_nr,
                    'gender' => $user->gender,
                    'user_files_verified' => $user->user_files_verified ?? false,
                    'user_verified_at' => $user->user_verified_at,
                    'document_extra_text_block_a' => $user->document_extra_text_block_a,
                    'document_extra_text_block_b' => $user->document_extra_text_block_b,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     * Migrate data back from customer_profiles to users
     */
    public function down(): void
    {
        $profiles = DB::table('customer_profiles')->get();

        foreach ($profiles as $profile) {
            DB::table('users')
                ->where('id', $profile->user_id)
                ->update([
                    'customer_no' => $profile->customer_no,
                    'customer_type' => $profile->customer_type,
                    'is_business' => $profile->is_business,
                    'title_prefix' => $profile->title_prefix,
                    'title_suffix' => $profile->title_suffix,
                    'phone_nr' => $profile->phone_nr,
                    'gender' => $profile->gender,
                    'user_files_verified' => $profile->user_files_verified,
                    'user_verified_at' => $profile->user_verified_at,
                    'document_extra_text_block_a' => $profile->document_extra_text_block_a,
                    'document_extra_text_block_b' => $profile->document_extra_text_block_b,
                ]);
        }
    }
};
