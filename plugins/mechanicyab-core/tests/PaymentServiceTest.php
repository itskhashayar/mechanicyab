<?php

declare(strict_types=1);

namespace MechanicYab\Core\Tests;

use MechanicYab\Core\Contracts\PaymentGateway;
use MechanicYab\Core\Contracts\PaymentRepository;
use MechanicYab\Core\Contracts\PaymentRequest;
use MechanicYab\Core\Core\PaymentService;
use PHPUnit\Framework\TestCase;

final class PaymentServiceTest extends TestCase
{
    public function testPaymentRequestRejectsInvalidAmount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new PaymentRequest('order-1', 999, 'IRR', 'https://example.test/callback');
    }
    public function testVerifyRequiresStoredAmountAndReference(): void
    {
        $repo = new FakePaymentRepository(); $repo->record = ['id'=>1,'amount'=>10000,'gateway_reference'=>'track-1','status'=>'requested'];
        $service = new PaymentService($repo, new FakePaymentGateway());
        $result = $service->verify('order-1','track-1',10000);
        self::assertTrue($result['verified']); self::assertSame('paid',$result['status']);
    }
    public function testAmountMismatchCannotFinalize(): void
    {
        $repo = new FakePaymentRepository(); $repo->record = ['id'=>1,'amount'=>10000,'gateway_reference'=>'track-1','status'=>'requested'];
        $this->expectException(\DomainException::class);
        (new PaymentService($repo, new FakePaymentGateway()))->verify('order-1','track-1',11000);
    }
}
final class FakePaymentGateway implements PaymentGateway
{
    public function request(PaymentRequest $request): array { return ['accepted'=>true,'reference'=>'track-1','redirect_url'=>'https://gateway.test/start','status'=>'requested']; }
    public function verify(string $reference,int $amount,string $orderKey): array { return ['verified'=>true,'duplicate'=>false,'reference'=>$reference,'amount'=>$amount,'status'=>'100']; }
    public function inquiry(string $reference,string $orderKey): array { return ['status'=>'1','reference'=>$reference]; }
}
final class FakePaymentRepository implements PaymentRepository
{
    public array $record=[]; public function create(array $data): int { return 1; } public function findByOrder(string $orderKey): ?array { return $this->record ?: null; } public function markRequested(int $id,string $reference): bool { return true; } public function finalize(int $id,string $status,string $reference,int $amount): bool { $this->record['status']=$status; return true; }
}
