<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\PaymentGateway;
use MechanicYab\Core\Contracts\PaymentRequest;

final class ZarinpalPaymentGateway implements PaymentGateway
{
    public function __construct(private readonly string $merchantId, private readonly int $timeout=10) {}
    public function request(PaymentRequest $r): array { if($this->merchantId===''){throw new \RuntimeException('Payment gateway is not configured.');} $x=wp_remote_post('https://payment.zarinpal.com/pg/v4/payment/request.json',['timeout'=>$this->timeout,'headers'=>['Content-Type'=>'application/json'],'body'=>wp_json_encode(['merchant_id'=>$this->merchantId,'amount'=>$r->amount,'description'=>'MechanicYab order '.$r->orderKey,'callback_url'=>$r->callbackUrl,'metadata'=>['order_id'=>$r->orderKey]])]); if(is_wp_error($x)){throw new \RuntimeException('Payment transport failed.');} $d=json_decode((string)wp_remote_retrieve_body($x),true); if((int)($d['data']['code']??0)!==100){throw new \RuntimeException('Payment request rejected.');} $a=(string)$d['data']['authority']; return ['accepted'=>true,'reference'=>$a,'redirect_url'=>'https://payment.zarinpal.com/pg/StartPay/'.$a,'status'=>'requested']; }
    public function verify(string $reference,int $amount,string $orderKey): array { $x=wp_remote_post('https://payment.zarinpal.com/pg/v4/payment/verify.json',['timeout'=>$this->timeout,'headers'=>['Content-Type'=>'application/json'],'body'=>wp_json_encode(['merchant_id'=>$this->merchantId,'amount'=>$amount,'authority'=>$reference])]); if(is_wp_error($x)){throw new \RuntimeException('Payment verify transport failed.');} $d=json_decode((string)wp_remote_retrieve_body($x),true); $c=(int)($d['data']['code']??0); return ['verified'=>in_array($c,[100,101],true),'duplicate'=>$c===101,'reference'=>$reference,'amount'=>$amount,'status'=>(string)$c]; }
    public function inquiry(string $reference,string $orderKey): array { return ['status'=>'unsupported','reference'=>$reference]; }
}
