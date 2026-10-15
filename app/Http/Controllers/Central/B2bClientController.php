<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\B2bClient\IndexB2bClientRequest;
use App\Http\Requests\Central\B2bClient\StoreB2bClientRequest;
use App\Http\Requests\Central\B2bClient\UpdateB2bClientRequest;
use App\Http\Resources\Central\B2bClientResource;
use App\Models\B2bClient;
use App\Services\Central\B2bClientService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Clientes B2B da plataforma. O acesso é do middleware `central.admin`.
 */
class B2bClientController extends Controller
{
    public function __construct(
        protected B2bClientService $service,
    ) {}

    public function index(IndexB2bClientRequest $request): Response
    {
        $search = $request->validated('search');

        return Inertia::render('Central/B2bClients/Index', [
            'clients' => B2bClientResource::collection($this->service->paginate($search)),
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Central/B2bClients/Create');
    }

    public function store(StoreB2bClientRequest $request): RedirectResponse
    {
        $client = $this->service->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => "Cliente B2B {$client->displayName()} cadastrado."]);

        return to_route('central.b2b-clients.index');
    }

    public function edit(B2bClient $b2bClient): Response
    {
        return Inertia::render('Central/B2bClients/Edit', [
            'client' => new B2bClientResource($b2bClient),
        ]);
    }

    public function update(UpdateB2bClientRequest $request, B2bClient $b2bClient): RedirectResponse
    {
        $this->service->update($b2bClient, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => "Cliente B2B {$b2bClient->displayName()} atualizado."]);

        return to_route('central.b2b-clients.index');
    }

    public function destroy(B2bClient $b2bClient): RedirectResponse
    {
        $this->service->delete($b2bClient);

        Inertia::flash('toast', ['type' => 'success', 'message' => "Cliente B2B {$b2bClient->displayName()} excluído."]);

        return to_route('central.b2b-clients.index');
    }
}
