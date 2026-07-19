<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CancellationRequest;
use App\Services\CancellationRequestResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Fila de solicitações de cancelamento (task-8, seção 2, passo 8;
 * task-9, seção 3.2).
 */
class CancellationRequestController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');

        $cancellationRequests = CancellationRequest::query()
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->with(['requestable', 'requestedBy'])
            ->orderBy('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.cancellation-requests.index', compact('cancellationRequests', 'status'));
    }

    public function approve(Request $request, CancellationRequest $cancellationRequest, CancellationRequestResolver $resolver): RedirectResponse
    {
        abort_unless($cancellationRequest->status === 'pending', 422);

        $resolver->approve($cancellationRequest, $request->user());

        return redirect()->route('cancellation-requests.index')
            ->with('success', 'Cancelamento aprovado.');
    }

    public function reject(Request $request, CancellationRequest $cancellationRequest, CancellationRequestResolver $resolver): RedirectResponse
    {
        abort_unless($cancellationRequest->status === 'pending', 422);

        $resolver->reject($cancellationRequest, $request->user());

        return redirect()->route('cancellation-requests.index')
            ->with('success', 'Cancelamento rejeitado.');
    }
}
