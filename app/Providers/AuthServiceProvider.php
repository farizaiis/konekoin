<?php

namespace App\Providers;


use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport; // add this
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;
use App\Models\User;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

        /**
     * Set a callback that should be used when creating the email verification URL.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public static function createUrlUsing($callback)
    {
        static::$createUrlCallback = $callback;
    }

    /**
     * Set a callback that should be used when building the notification mail message.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public static function toMailUsing($callback)
    {
        static::$toMailCallback = $callback;
    }


    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Passport::routes();
        Passport::tokensExpireIn(now()->addDays(13));
        Passport::refreshTokensExpireIn(now()->addDays(30));

        // ResetPassword::createUrlUsing(function ($user, string $token) {
        //     return 'https://konekita-nextjs.vercel.app/reset-password?token='.$token;
        // });

        // VerifyEmail::toMailUsing(function (User $user, string $verificationUrl) {
        //     $frontendUrl = 'https://konekita-nextjs.vercel.app/verify-email?';
        //     $link_back_end = env('APP_URL').'/api/v1/verify-email/'.$user->getKey().'/'.sha1($user->getEmailForVerification()).'?';
        //     $url = str_replace($link_back_end, '', $verificationUrl);
        //     $final_url = $frontendUrl.'id='.$user->getKey().'&hash='.sha1($user->getEmailForVerification()).'&'.$url;

        //     return (new MailMessage)
        //         ->subject(Lang::get('Verify Email Address'))
        //         ->line(Lang::get('Please click the button below to verify your email address.'))
        //         ->action(Lang::get('Verify Email Address'), $final_url)
        //         ->line(Lang::get('This verify email link will expire in 60 minutes.'))
        //         ->line(Lang::get('If you did not create an account, no further action is required.'));
        // });
    }
}
