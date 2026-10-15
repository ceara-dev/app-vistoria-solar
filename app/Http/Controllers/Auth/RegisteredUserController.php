<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\RegistrationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function __construct(
        protected RegistrationService $service,
    ) {}

    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Cria a conta e a empresa da pessoa, já como proprietária.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = $this->service->register($request->validated());

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('tenant.dashboard', absolute: false));
    }
}
