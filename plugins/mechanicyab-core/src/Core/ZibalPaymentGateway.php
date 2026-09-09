<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\PaymentGateway;
use MechanicYab\Core\Contracts\PaymentRequest;

final class ZibalPaymentGateway implements PaymentGateway
{
    public function __construct(private readonly string $merchant, private readonly int $timeout = 10) {}
    public function request(PaymentRequest $request): array { $this->configured(); $body = ['merchant'=>$this->merchant,'amount'=>$request->amount,'callbackUrl'=>$request->callbackUrl,'orderId'=>$request->orderKey]; $response = wp_remote_post('https://gateway.zibal.ir/v1/request', ['timeout'=>$this->timeout,'headers'=>['Content-Type'=>'application/json'],'body'=>wp_json_encode($body)]); if (is_wp_error($response)) { throw new \RuntimeException('Payment transport failed.'); } $data = json_decode((string)wp_remote_retrieve_body($response), true); if (wp_remote_retrieve_response_code($response) < 200 || wp_remote_retrieve_response_code($response) >= 300 || (int)($data['result']??0) !== 100) { throw new \RuntimeException('Payment request rejected.'); } $ref=(string)$data['trackId']; return ['accepted'=>true,'reference'=>$ref,'redirect_url'=>'https://gateway.zibal.ir/start/'.$ref,'status'=>'requested']; }
    public function verify(string $reference, int $amount, string $orderKey): array { $this->configured(); $response=wp_remote_post('https://gateway.zibal.ir/v1/verify',['timeout'=>$this->timeout,'headers'=>['Content-Type'=>'application/json'],'body'=>wp_json_encode(['merchant'=>$this->merchant,'trackId'=>$reference])]); if(is_wp_error($response)){throw new \RuntimeException('Payment verify transport failed.');} $data=json_decode((string)wp_remote_retrieve_body($response),true); $result=(int)($data['result']??0); return ['verified'=>in_array($result,[100,201],true),'duplicate'=>$result===201,'reference'=>$reference,'amount'=>$amount,'status'=>(string)$result]; }
    public function inquiry(string $reference, string $orderKey): array { $this->configured(); $response=wp_remote_post('https://gateway.zibal.ir/v1/inquiry',['timeout'=>$this->timeout,'headers'=>['Content-Type'=>'application/json'],'body'=>wp_json_encode(['merchant'=>$this->merchant,'trackId'=>$reference])]); if(is_wp_error($response)){throw new \RuntimeException('Payment inquiry transport failed.');} $data=json_decode((string)wp_remote_retrieve_body($response),true); return ['status'=>(string)($data['status']??$data['result']??'unknown'),'reference'=>$reference]; }
    private function configured(): void { if($this->merchant===''){throw new \RuntimeException('Payment gateway is not configured.');} }
}
