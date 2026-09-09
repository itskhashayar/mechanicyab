<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\PaymentGateway;
use MechanicYab\Core\Contracts\PaymentRepository;
use MechanicYab\Core\Contracts\PaymentRequest;

final class PaymentService
{
    public function __construct(private readonly PaymentRepository $repository, private readonly PaymentGateway $gateway) {}
    public function start(int $payerUserId, PaymentRequest $request): array
    {
        if ($payerUserId < 1) { throw new \DomainException('Authenticated payer is required.'); }
        $existing = $this->repository->findByOrder($request->orderKey);
        if ($existing !== null) { if (($existing['status'] ?? '') === 'paid') { throw new \DomainException('Order is already paid.'); } if (!empty($existing['gateway_reference'])) { return ['redirect_url' => null, 'reference' => $existing['gateway_reference'], 'status' => $existing['status']]; } }
        $idempotency = hash('sha256', $payerUserId.'|'.$request->orderKey.'|'.$request->amount.'|'.$request->currency);
        $id = $this->repository->create(['payer_user_id'=>$payerUserId,'order_key'=>$request->orderKey,'amount'=>$request->amount,'currency'=>$request->currency,'gateway'=>'zibal','status'=>'pending','idempotency_key'=>$idempotency]);
        $result = $this->gateway->request($request);
        if (!$result['accepted'] || $result['reference'] === null) { throw new \RuntimeException('Payment gateway did not accept the request.'); }
        $this->repository->markRequested($id, $result['reference']);
        return ['payment_id'=>$id,'redirect_url'=>$result['redirect_url'],'reference'=>$result['reference'],'status'=>'requested'];
    }
    public function verify(string $orderKey, string $reference, int $amount): array
    {
        $payment = $this->repository->findByOrder($orderKey);
        if ($payment === null || (int)$payment['amount'] !== $amount || (string)($payment['gateway_reference'] ?? '') !== $reference) { throw new \DomainException('Payment identity or amount mismatch.'); }
        if (($payment['status'] ?? '') === 'paid') { return ['verified'=>true,'duplicate'=>true,'status'=>'paid']; }
        $result = $this->gateway->verify($reference, $amount, $orderKey);
        if (!$result['verified']) { throw new \RuntimeException('Payment verification failed.'); }
        $this->repository->finalize((int)$payment['id'], 'paid', $reference, $amount);
        return array_merge($result, ['status'=>'paid']);
    }
}
