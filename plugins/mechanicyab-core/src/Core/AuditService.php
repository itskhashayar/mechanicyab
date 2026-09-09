<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

final class AuditService
{
    /** @param array<string, mixed> $metadata */
    public function event(int $actorId, string $action, string $entityType, ?int $entityId, array $metadata=[]): array { if($actorId<1||$action===''||$entityType===''){throw new \InvalidArgumentException('Audit identity and action are required.');} return ['actor_id'=>$actorId,'action'=>$action,'entity_type'=>$entityType,'entity_id'=>$entityId,'metadata'=>$metadata,'created_at'=>gmdate('Y-m-d H:i:s')]; }
}
