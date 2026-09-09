<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\PaymentRepository;

final class WpdbPaymentRepository implements PaymentRepository
{
    public function __construct(private readonly object $wpdb) {}
    public function create(array $data): int { $now=gmdate('Y-m-d H:i:s'); if($this->wpdb->insert($this->table('payments'),$data+['created_at'=>$now,'updated_at'=>$now])===false){throw new \RuntimeException('Payment record could not be created.');} return (int)$this->wpdb->insert_id; }
    public function findByOrder(string $orderKey): ?array { $row=$this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM {$this->table('payments')} WHERE order_key = %s LIMIT 1",$orderKey),ARRAY_A); return is_array($row)?$row:null; }
    public function markRequested(int $id,string $reference): bool { return $this->wpdb->update($this->table('payments'),['gateway_reference'=>$reference,'status'=>'requested','updated_at'=>gmdate('Y-m-d H:i:s')],['id'=>$id],['%s','%s','%s'],['%d'])!==false; }
    public function finalize(int $id,string $status,string $reference,int $amount): bool { return $this->wpdb->query($this->wpdb->prepare("UPDATE {$this->table('payments')} SET status = %s, gateway_reference = %s, paid_at = %s, updated_at = %s WHERE id = %d AND amount = %f AND status IN ('pending','requested')",$status,$reference,gmdate('Y-m-d H:i:s'),gmdate('Y-m-d H:i:s'),$id,$amount))!==false; }
    private function table(string $name): string { if(!isset($this->wpdb->prefix)){throw new \RuntimeException('WordPress database prefix is unavailable.');} return $this->wpdb->prefix.'my_'.$name; }
}
