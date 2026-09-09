<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

final class AuctionEligibility
{
    /** @param array{approved_review_count:int,average_rating:float,status:string,publication_status:string,deleted_at?:string|null} $mechanic */
    public function eligible(array $mechanic): bool { return (int)$mechanic['approved_review_count'] >= 5 && (float)$mechanic['average_rating'] > 4.5 && $mechanic['status'] === 'active' && $mechanic['publication_status'] === 'published' && empty($mechanic['deleted_at']); }
}
