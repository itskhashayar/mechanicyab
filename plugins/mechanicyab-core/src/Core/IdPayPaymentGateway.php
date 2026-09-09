<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\PaymentGateway;
use MechanicYab\Core\Contracts\PaymentRequest;

final class IdPayPaymentGateway implements PaymentGateway
{
    public function __construct(private readonly string $apiKey, private readonly bool $sandbox=false, private readonly int $timeout=10) {}
    public function request(PaymentRequest $r): array { if($this->apiKey===''){throw new \RuntimeException('Payment gateway is not configured.');} $endpoint = $this->sandbox ? 'https://api.idpay.ir/v1.1/payment/test' : 'https://api.idpay.ir/v1.1/payment'; $x=wp_remote_post($endpoint,['timeout'=>$this->timeout,'headers'=>['X-API-KEY'=>$this->apiKey,'Content-Type'=>'application/json'],'body'=>wp_json_encode(['order_id'=>$r->orderKey,'amount'=>$r->amount,'callback'=>$r->callbackUrl])]); if(is_wp_error($x)){throw new \RuntimeException('Payment transport failed.');} $d=json_decode((string)wp_remote_retrieve_body($x),true); if(wp_remote_retrieve_response_code($x)!==201){throw new \RuntimeException('Payment request rejected.');} return ['accepted'=>true,'reference'=>(string)$d['id'],'redirect_url'=>(string)$d['link'],'status'=>'requested']; }
    public function verify(string $reference,int $amount,string $orderKey): array { $x=wp_remote_post('https://api.idpay.ir/v1.1/payment/verify',['timeout'=>$this->timeout,'headers'=>['X-API-KEY'=>$this->apiKey,'Content-Type'=>'application/json'],'body'=>wp_json_encode(['id'=>$reference,'order_id'=>$orderKey])]); if(is_wp_error($x)){throw new \RuntimeException('Payment verify transport failed.');} $d=json_decode((string)wp_remote_retrieve_body($x),true); $s=(int)($d['status']??0); return ['verified'=>in_array($s,[100,101,200],true),'duplicate'=>$s===101,'reference'=>$reference,'amount'=>(int)($d['amount']??$amount),'status'=>(string)$s]; }
    public function inquiry(string $reference,string $orderKey): array { return ['status'=>'unsupported','reference'=>$reference]; }
}
