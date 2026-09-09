<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

final class AnalyticsAggregationService
{
    /** @param list<array{event_name:string,occurred_at:string,entity_type?:string,entity_id?:int,cta_id?:string|null,outcome?:string|null}> $events @return array<string, float> */
    public function aggregate(array $events, string $from, string $to): array
    {
        $out=[]; foreach($events as $event){$time=strtotime($event['occurred_at']); if($time===false||$time<strtotime($from)||$time>strtotime($to)){continue;} $key='events.'.($event['event_name']??'unknown'); $out[$key]=($out[$key]??0)+1; if(!empty($event['cta_id'])){$cta='cta.'.(string)$event['cta_id'];$out[$cta]=($out[$cta]??0)+1;} if(($event['outcome']??null)==='success'){$success=$key.'.success';$out[$success]=($out[$success]??0)+1;} } return $out;
    }
}
