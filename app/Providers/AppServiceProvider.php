<?php

namespace App\Providers;

use App\Events\AdminOrderCreatedEvent;
use App\Events\AdminOrderMotorcycleCreatedEvent;
use App\Events\FrontendOrderCreatedEvent;
use App\Events\FrontendOrderMotorcycleCreatedEvent;
use App\Listeners\CreateOrderMotorcyclePayOsPayment;
use App\Listeners\CreateOrderPayOsPayment;
use App\Models\Address;
use App\Models\Identification;
use App\Models\Option;
use App\Models\Order;
use App\Models\OrderMotorcycle;
use App\Models\OrderProduct;
use App\Observers\StoreAddress;
use App\Observers\StoreIdentification;
use App\Observers\StoreOptionObserver;
use App\Observers\StoreOrderMotorcycleObserver;
use App\Observers\StoreOrderObserver;
use App\Observers\StoreOrderProductObserver;
use App\Rules\Action;
use App\Rules\Image;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(
            fn (object $notifiable, string $token): string => sprintf(
                '%s/password-reset/%s?email=%s',
                config('app.frontend_url'),
                $token,
                $notifiable->getEmailForPasswordReset()
            )
        );

        Address::observe(StoreAddress::class);
        Identification::observe(StoreIdentification::class);
        Option::observe(StoreOptionObserver::class);
        Order::observe(StoreOrderObserver::class);
        OrderProduct::observe(StoreOrderProductObserver::class);
        OrderMotorcycle::observe(StoreOrderMotorcycleObserver::class);

        Event::listen(AdminOrderCreatedEvent::class, [CreateOrderPayOsPayment::class, 'handle']);
        Event::listen(FrontendOrderCreatedEvent::class, [CreateOrderPayOsPayment::class, 'handle']);

        Event::listen(AdminOrderMotorcycleCreatedEvent::class, [CreateOrderMotorcyclePayOsPayment::class, 'handle']);
        Event::listen(FrontendOrderMotorcycleCreatedEvent::class, [CreateOrderMotorcyclePayOsPayment::class, 'handle']);

        Validator::extend(
            'actions',
            fn ($attribute, $value, $parameters, $validator): bool => Action::extends($attribute, $value, $validator)
        );
        Validator::extend(
            'image_banner',
            fn ($attribute, $value, $parameters, $validator): bool => Image::extends($attribute, $value, $validator)
        );
    }
}
