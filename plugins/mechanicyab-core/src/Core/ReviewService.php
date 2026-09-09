<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\ReviewRepository;

final class ReviewService
{
    public function __construct(private readonly ReviewRepository $repository, private readonly ?\Closure $moderationChecker = null, private readonly ?\Closure $ownerChecker = null) {}

    /** @param array<string, mixed> $data */
    public function submit(array $data, int $wpUserId): int
    {
        if ($wpUserId < 1 || (int) ($data['mechanic_id'] ?? 0) < 1) {
            throw new \DomainException('Authenticated user and mechanic are required.');
        }
        $rating = (int) ($data['rating'] ?? 0);
        $body = trim((string) ($data['body'] ?? ''));
        if ($rating < 1 || $rating > 5 || $body === '') {
            throw new \InvalidArgumentException('Review rating must be between 1 and 5 and body is required.');
        }
        foreach (['quality_rating', 'price_rating', 'speed_rating', 'behavior_rating'] as $key) {
            if (isset($data[$key]) && ((int) $data[$key] < 1 || (int) $data[$key] > 5)) {
                throw new \InvalidArgumentException('Review detail ratings must be between 1 and 5.');
            }
        }
        $data['wp_user_id'] = $wpUserId;
        $data['rating'] = $rating;
        $data['body'] = $body;
        $data['moderation_status'] = 'pending';
        return $this->repository->create($data);
    }

    public function moderate(int $reviewId, string $status, int $actorId): bool
    {
        if (!$this->canModerate()) {
            throw new \DomainException('Review moderation capability is required.');
        }
        if (!in_array($status, ['approved', 'rejected', 'under_review'], true)) {
            throw new \InvalidArgumentException('Invalid moderation status.');
        }
        if ($this->repository->find($reviewId) === null) {
            throw new \RuntimeException('Review not found.');
        }
        $updated = $this->repository->updateModeration($reviewId, $status, $actorId);
        if ($updated && $status === 'approved') {
            $review = $this->repository->find($reviewId);
            $this->repository->rebuildMechanicSummary((int) $review['mechanic_id']);
        }
        return $updated;
    }

    /** @param array<string, mixed> $data */
    public function report(array $data, int $wpUserId): int
    {
        if ($wpUserId < 1 || (int) ($data['review_id'] ?? 0) < 1 || trim((string) ($data['reason_code'] ?? '')) === '') {
            throw new \InvalidArgumentException('Review, reporter and reason are required.');
        }
        $data['reported_by'] = $wpUserId;
        $data['status'] = 'open';
        return $this->repository->createReport($data);
    }

    public function reply(int $reviewId, int $mechanicId, string $body): int
    {
        if ($this->ownerChecker !== null && !(bool) ($this->ownerChecker)($mechanicId)) { throw new \DomainException('Mechanic ownership is required.'); }
        if ($reviewId < 1 || $mechanicId < 1 || trim($body) === '') { throw new \InvalidArgumentException('Review, mechanic and reply body are required.'); }
        $review = $this->repository->find($reviewId);
        if ($review === null || (int) ($review['mechanic_id'] ?? 0) !== $mechanicId || ($review['moderation_status'] ?? '') !== 'approved') { throw new \DomainException('Only an approved review owned by this mechanic can be replied to.'); }
        if ($this->repository->findReply($reviewId) !== null) { throw new \DomainException('A reply already exists for this review.'); }
        return $this->repository->createReply(['review_id' => $reviewId, 'mechanic_id' => $mechanicId, 'body' => trim($body)]);
    }

    /** @return array<string, mixed> */
    public function publicById(int $reviewId): array
    {
        $review = $this->repository->find($reviewId);
        if ($review === null || ($review['moderation_status'] ?? '') !== 'approved' || ($review['status'] ?? '') !== 'active') {
            throw new \RuntimeException('Review not found.');
        }
        $response = (new ReviewPublicResource())->toResponse($review);
        $reply = $this->repository->findReply($reviewId);
        if ($reply !== null) { $response['reply'] = ['id' => (int) $reply['id'], 'body' => (string) $reply['body'], 'created_at' => $reply['created_at'] ?? null]; }
        return $response;
    }

    private function canModerate(): bool
    {
        if ($this->moderationChecker !== null) {
            return (bool) ($this->moderationChecker)('mechanicyab_moderate_reviews');
        }
        return function_exists('current_user_can') && current_user_can('mechanicyab_moderate_reviews');
    }
}
