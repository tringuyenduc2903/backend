<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Laravel\Fortify\RoutePath;

Route::get(RoutePath::for('login', '/login'), fn (): RedirectResponse => redirect(config('app.frontend_url')))
    ->middleware(['guest:'.config('fortify.guard')])
    ->name('login');

Route::middleware(config('fortify.middleware', 'web'))
    ->group(function () {
        // Authentication...
        $limiter = config('fortify.limiters.login');
        $twoFactorLimiter = config('fortify.limiters.two-factor');
        $verificationLimiter = config('fortify.limiters.verification');

        Route::post(RoutePath::for('login', '/login'), [AuthenticatedSessionController::class, 'store'])
            ->middleware(array_filter([
                'guest:'.config('fortify.guard'),
                'throttle:'.$limiter,
            ]));

        Route::post(RoutePath::for('logout', '/logout'), [AuthenticatedSessionController::class, 'destroy'])
            ->middleware([config('fortify.auth_middleware', 'auth').':'.config('fortify.guard_auth')])
            ->name('logout');

        // Password Reset...
        if (Features::enabled(Features::resetPasswords())) {
            Route::post(RoutePath::for('password.email', '/forgot-password'), [PasswordResetLinkController::class, 'store'])
                ->middleware(['guest:'.config('fortify.guard')])
                ->name('password.email');

            Route::post(RoutePath::for('password.update', '/reset-password'), [NewPasswordController::class, 'store'])
                ->middleware(['guest:'.config('fortify.guard')])
                ->name('password.update');
        }

        // Registration...
        if (Features::enabled(Features::registration())) {
            Route::post(RoutePath::for('register', '/register'), [RegisteredUserController::class, 'store'])
                ->middleware(['guest:'.config('fortify.guard')]);
        }

        // Email Verification...
        if (Features::enabled(Features::emailVerification())) {
            Route::get(RoutePath::for('verification.notice', '/email/verify'), [EmailVerificationPromptController::class, '__invoke'])
                ->middleware([config('fortify.auth_middleware', 'auth').':'.config('fortify.guard_auth')])
                ->name('verification.notice');

            Route::get(RoutePath::for('verification.verify', '/email/verify/{id}/{hash}'), [VerifyEmailController::class, '__invoke'])
                ->middleware([config('fortify.auth_middleware', 'auth').':'.config('fortify.guard_auth'), 'signed', 'throttle:'.$verificationLimiter])
                ->name('verification.verify');

            Route::post(RoutePath::for('verification.send', '/email/verification-notification'), [EmailVerificationNotificationController::class, 'store'])
                ->middleware([config('fortify.auth_middleware', 'auth').':'.config('fortify.guard_auth'), 'throttle:'.$verificationLimiter])
                ->name('verification.send');
        }

        // Profile Information...
        if (Features::enabled(Features::updateProfileInformation())) {
            Route::put(RoutePath::for('user-profile-information.update', '/user/profile-information'), [ProfileInformationController::class, 'update'])
                ->middleware([config('fortify.auth_middleware', 'auth').':'.config('fortify.guard_auth')])
                ->name('user-profile-information.update');
        }

        // Passwords...
        if (Features::enabled(Features::updatePasswords())) {
            Route::put(RoutePath::for('user-password.update', '/user/password'), [PasswordController::class, 'update'])
                ->middleware([config('fortify.auth_middleware', 'auth').':'.config('fortify.guard_auth')])
                ->name('user-password.update');
        }

        // Password Confirmation...
        Route::get(RoutePath::for('password.confirmation', '/user/confirmed-password-status'), [ConfirmedPasswordStatusController::class, 'show'])
            ->middleware([config('fortify.auth_middleware', 'auth').':'.config('fortify.guard_auth')])
            ->name('password.confirmation');

        Route::post(RoutePath::for('password.confirm', '/user/confirm-password'), [ConfirmablePasswordController::class, 'store'])
            ->middleware([config('fortify.auth_middleware', 'auth').':'.config('fortify.guard_auth')])
            ->name('password.confirm');

        // Two-Factor Authentication...
        if (Features::enabled(Features::twoFactorAuthentication())) {
            Route::post(RoutePath::for('two-factor.login', '/two-factor-challenge'), [TwoFactorAuthenticatedSessionController::class, 'store'])
                ->middleware(array_filter([
                    'guest:'.config('fortify.guard'),
                    'throttle:'.$twoFactorLimiter,
                ]));

            $twoFactorMiddleware = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')
                ? [config('fortify.auth_middleware', 'auth').':'.config('fortify.guard_auth'), 'password.confirm']
                : [config('fortify.auth_middleware', 'auth').':'.config('fortify.guard_auth')];

            Route::post(RoutePath::for('two-factor.enable', '/user/two-factor-authentication'), [TwoFactorAuthenticationController::class, 'store'])
                ->middleware($twoFactorMiddleware)
                ->name('two-factor.enable');

            Route::post(RoutePath::for('two-factor.confirm', '/user/confirmed-two-factor-authentication'), [ConfirmedTwoFactorAuthenticationController::class, 'store'])
                ->middleware($twoFactorMiddleware)
                ->name('two-factor.confirm');

            Route::delete(RoutePath::for('two-factor.disable', '/user/two-factor-authentication'), [TwoFactorAuthenticationController::class, 'destroy'])
                ->middleware($twoFactorMiddleware)
                ->name('two-factor.disable');

            Route::get(RoutePath::for('two-factor.qr-code', '/user/two-factor-qr-code'), [TwoFactorQrCodeController::class, 'show'])
                ->middleware($twoFactorMiddleware)
                ->name('two-factor.qr-code');

            Route::get(RoutePath::for('two-factor.secret-key', '/user/two-factor-secret-key'), [TwoFactorSecretKeyController::class, 'show'])
                ->middleware($twoFactorMiddleware)
                ->name('two-factor.secret-key');

            Route::get(RoutePath::for('two-factor.recovery-codes', '/user/two-factor-recovery-codes'), [RecoveryCodeController::class, 'index'])
                ->middleware($twoFactorMiddleware)
                ->name('two-factor.recovery-codes');

            Route::post(RoutePath::for('two-factor.recovery-codes', '/user/two-factor-recovery-codes'), [RecoveryCodeController::class, 'store'])
                ->middleware($twoFactorMiddleware);
        }
    });
