<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

final class AuthService
{
    public function __construct(private readonly OtpService $otp) {}
    public function requestOtp(string $mobile): int { return $this->otp->request($mobile, 'login'); }
    public function verifyOtp(string $mobile, string $code): int
    {
        $mobile = $this->otp->normalizeMobile($mobile);
        if (!$this->otp->verify($mobile, $code, 'login')) { throw new \DomainException('OTP is invalid or expired.'); }
        $userId = $this->findOrCreateUser($mobile);
        if (function_exists('wp_set_auth_cookie')) { wp_set_auth_cookie($userId, false, is_ssl()); }
        if (function_exists('do_action')) { do_action('mechanicyab_user_authenticated', $userId); }
        return $userId;
    }
    public function logout(): void
    {
        if (function_exists('wp_destroy_current_session')) { wp_destroy_current_session(); }
        if (function_exists('wp_logout')) { wp_logout(); }
    }
    private function findOrCreateUser(string $mobile): int
    {
        global $wpdb;
        $table = $wpdb->prefix . 'my_users';
        $existing = $wpdb->get_var($wpdb->prepare("SELECT wp_user_id FROM {$table} WHERE mobile = %s AND status = 'active' LIMIT 1", $mobile));
        if ($existing) { return (int) $existing; }
        if (!function_exists('wp_insert_user')) { throw new \RuntimeException('WordPress user runtime is unavailable.'); }
        $userId = wp_insert_user(['user_login' => 'mobile_' . substr(hash('sha256', $mobile), 0, 24), 'user_pass' => wp_generate_password(32, true, true), 'role' => 'subscriber']);
        if (is_wp_error($userId)) { throw new \RuntimeException('Identity creation failed.'); }
        $now = gmdate('Y-m-d H:i:s');
        $wpdb->insert($table, ['wp_user_id'=>(int)$userId,'mobile'=>$mobile,'mobile_verified_at'=>$now,'status'=>'active','created_at'=>$now,'updated_at'=>$now]);
        return (int) $userId;
    }
}
