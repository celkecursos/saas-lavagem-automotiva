<?php

namespace App\Services\Wash;

use App\Models\CancellationRequest;
use App\Models\WashRedemption;
use App\Notifications\NewCancellationRequest;
use App\Support\AdminRecipients;
use Illuminate\Support\Facades\Notification;

/**
 * Solicitação de cancelamento de lavagem já confirmada (task-8,
 * seção 2, passo 8) — nem assinante nem funcionário desfazem sozinhos;
 * fica pending até o admin decidir (task-9/11).
 */
class WashCancellationRequestService
{
    /**
     * @throws WashRedemptionValidationException
     */
    public function request(WashRedemption $redemption, int $requestedByUserId, string $reason): CancellationRequest
    {
        if ($redemption->status !== 'completed') {
            throw new WashRedemptionValidationException('Só é possível solicitar cancelamento de uma lavagem já confirmada.');
        }

        $hasPending = CancellationRequest::where('requestable_type', WashRedemption::class)
            ->where('requestable_id', $redemption->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            throw new WashRedemptionValidationException('Já existe uma solicitação de cancelamento pendente pra essa lavagem.');
        }

        $request = CancellationRequest::create([
            'requestable_type' => WashRedemption::class,
            'requestable_id' => $redemption->id,
            'requested_by_user_id' => $requestedByUserId,
            'reason' => $reason,
            'status' => 'pending',
        ]);

        Notification::send(
            AdminRecipients::withPermission('cancellation-requests.approve'),
            new NewCancellationRequest($request),
        );

        return $request;
    }
}
