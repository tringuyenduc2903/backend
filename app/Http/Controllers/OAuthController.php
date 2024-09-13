<?php

namespace App\Http\Controllers;

use App\Actions\OAuth\Login;
use App\Actions\OAuth\Register;
use App\Enums\CustomerProvider;
use App\Enums\CustomerProviderEnum;
use App\Models\Customer;
use App\Models\Social;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
    public function redirect(CustomerProviderEnum $driver_name, Request $request): RedirectResponse
    {
        if ($request->exists('callback')) {
            session([
                'callback' => request('callback'),
            ]);
        }

        return Socialite::driver($driver_name->value)->redirect();
    }

    /**
     * @throws Exception
     */
    public function callback(CustomerProviderEnum $driver_name): RedirectResponse
    {
        $user = Socialite::driver($driver_name->value)->user();
        $provider_name = CustomerProvider::keyForValue($driver_name->value);

        $customer = Customer::orWhere('email', $user->getEmail())
            ->orWhereHas(
                'socials',
                function (Builder $query) use ($user, $provider_name): Builder {
                    /** @var Social $query */
                    return $query
                        ->whereProviderId($user->getId())
                        ->whereProviderName($provider_name);
                })
            ->first();

        $customer
            ? app(Login::class)->handle($customer, $user, $provider_name)
            : app(Register::class)->handle($user, $provider_name);

        return redirect(session(
            'callback',
            config('app.frontend_url')
        ));
    }
}
