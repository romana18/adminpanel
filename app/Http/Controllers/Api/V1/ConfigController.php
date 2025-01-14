<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Currency;
use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;

class ConfigController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function configuration(): JsonResponse
    {
        $currencySymbol = Currency::where(['currency_code' => Helpers::currency_code()])->first();

        $languageCode = null;
        $languages = Helpers::get_business_settings('language');
        foreach($languages as $language) {
            if($language['default']) {
                $languageCode = $language['code'];
            }
        }

        $digitalPaymentMethods = ['ssl_commerz', 'paypal', 'stripe', 'razor_pay', 'senang_pay', 'paystack', 'paymob_accept', 'flutterwave', 'bkash', 'mercadopago', 'phonepe', 'instamojo', 'cashfree'];

        $isPublished = addon_published_status('Gateways');
        $paymentGateways = collect($this->getPaymentMethods())
                            ->filter(function ($query) use ($isPublished, $digitalPaymentMethods) {
                                if (!$isPublished) {
                                    return in_array($query['gateway'], $digitalPaymentMethods);
                                } else return $query;
                            })->map(function ($query) {
                                $query['label'] = ucwords(str_replace('_', ' ', $query['gateway']));
                                return $query;
                            })->values();

        return response()->json([
            'company_name' => Helpers::get_business_settings('business_name'),
            'company_logo' => Helpers::get_business_settings('logo'),
            'company_address' => Helpers::get_business_settings('address'),
            'company_phone' => (string)Helpers::get_business_settings('phone'),
            'company_email' => Helpers::get_business_settings('email'),
            'base_urls' => [
                'customer_image_url' => asset('storage/app/public/customer'),
                'agent_image_url' => asset('storage/app/public/agent'),
                'linked_website_image_url' => asset('storage/app/public/website'),
                'purpose_image_url' => asset('storage/app/public/purpose'),
                'notification_image_url' => asset('storage/app/public/notification'),
                'company_image_url' => asset('storage/app/public/business'),
                'banner_image_url' => asset('storage/app/public/banner'),
                'gateway_image_url' => asset('storage/app/public/payment_modules/gateway_image'),
            ],
            'currency_symbol' => $currencySymbol?->currency_symbol,
            'currency_position' => Helpers::get_business_settings('currency_symbol_position') ?? 'right',

            'cashout_charge_percent' => (float) Helpers::get_business_settings('cashout_charge_percent'),
            'sendmoney_charge_flat' => (float) Helpers::get_business_settings('sendmoney_charge_flat'),
            'agent_commission_percent' => (float) Helpers::get_business_settings('agent_commission_percent'),
            'withdraw_charge_percent' => (float) Helpers::get_business_settings('withdraw_charge_percent'),
            'admin_commission' => (float) Helpers::get_business_settings('admin_commission'),
            'two_factor' => (integer) Helpers::get_business_settings('two_factor'),
            'country' => Helpers::get_business_settings('country') ?? 'BD',

            'terms_and_conditions' => Helpers::get_business_settings('terms_and_conditions'),
            'privacy_policy' => Helpers::get_business_settings('privacy_policy'),
            'about_us' => Helpers::get_business_settings('about_us'),
            'phone_verification' => Helpers::get_business_settings('phone_verification'),
            'email_verification' => Helpers::get_business_settings('email_verification'),
            'user_app_theme' => (string) Helpers::get_business_settings('app_theme'),
            'software_version' => (string)env('SOFTWARE_VERSION')??null,
            'language_code' => (string)$languageCode,
            'active_payment_method_list' => $paymentGateways,
            'otp_resend_time' => Helpers::get_business_settings('otp_resend_time') ?? 60,
            'agent_self_registration' => Helpers::get_business_settings('agent_self_registration') ?? 1,
            'customer_self_delete' => Helpers::get_business_settings('customer_self_delete') ?? 0,
            'agent_self_delete' => Helpers::get_business_settings('agent_self_delete') ?? 0,
            'system_feature' => [
                'add_money_status' => Helpers::get_business_settings('add_money_status') ?? 1,
                'send_money_status' => Helpers::get_business_settings('send_money_status') ?? 1,
                'cash_out_status' => Helpers::get_business_settings('cash_out_status') ?? 1,
                'send_money_request_status' => Helpers::get_business_settings('send_money_request_status') ?? 1,
                'withdraw_request_status' => Helpers::get_business_settings('withdraw_request_status') ?? 1,
                'linked_website_status' => Helpers::get_business_settings('linked_website_status') ?? 1,
                'banner_status' => Helpers::get_business_settings('banner_status') ?? 1,
                'faq_section_status' => Helpers::get_business_settings('faq_section_status') ?? 0,
            ],
            'customer_add_money_limit' => Helpers::get_business_settings('customer_add_money_limit'),
            'customer_send_money_limit' => Helpers::get_business_settings('customer_send_money_limit'),
            'customer_send_money_request_limit' => Helpers::get_business_settings('customer_send_money_request_limit'),
            'customer_cash_out_limit' => Helpers::get_business_settings('customer_cash_out_limit'),
            'customer_withdraw_request_limit' => Helpers::get_business_settings('customer_withdraw_request_limit'),
            'agent_add_money_limit' => Helpers::get_business_settings('agent_add_money_limit'),
            'agent_send_money_limit' => Helpers::get_business_settings('agent_send_money_limit'),
            'agent_send_money_request_limit' => Helpers::get_business_settings('agent_send_money_request_limit'),
            'agent_withdraw_request_limit' => Helpers::get_business_settings('agent_withdraw_request_limit'),
        ]);
    }

    private function getPaymentMethods(): array
    {
        if (!Schema::hasTable('addon_settings')) {
            return [];
        }

        $methods = DB::table('addon_settings')->where('settings_type', 'payment_config')->get();
        $env = env('APP_ENV') == 'live' ? 'live' : 'test';
        $credentials = $env . '_values';

        $data = [];
        foreach ($methods as $method) {
            $credentialsData = json_decode($method->$credentials);
            $additionalData = json_decode($method->additional_data);
            if ($credentialsData?->status == 1) {
                $data[] = [
                    'gateway' => $method->key_name,
                    'gateway_title' => $additionalData?->gateway_title,
                    'gateway_image' => $additionalData?->gateway_image
                ];
            }
        }
        return $data;
    }
}
