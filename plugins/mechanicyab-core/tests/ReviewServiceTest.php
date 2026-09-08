<?php

declare(strict_types=1);

namespace MechanicYab\Core\Tests;

use MechanicYab\Core\Contracts\ReviewRepository;
use MechanicYab\Core\Core\ReviewPublicResource;
use MechanicYab\Core\Core\ReviewService;
use PHPUnit\Framework\TestCase;

final class ReviewServiceTest extends TestCase
{
    public function testReviewSubmissionStartsPendingAndValidatesRatings(): void
    {
        $repository = new InMemoryReviewRepository();
        $service = new ReviewService($repository);
        $id = $service->submit(['mechanic_id' => 3, 'rating' => 5, 'body' => 'Excellent'], 9);
        self::assertSame(1, $id);
        self::assertSame('pending', $repository->find($id)['moderation_status']);
    }

    public function testInvalidRatingIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new ReviewService(new InMemoryReviewRepository()))->submit(['mechanic_id' => 3, 'rating' => 6, 'body' => 'Bad'], 9);
    }

    public function testApprovalRebuildsDerivedSummaryOnlyAfterModeration(): void
    {
        $repository = new InMemoryReviewRepository();
        $service = new ReviewService($repository, static fn (): bool => true);
        $id = $service->submit(['mechanic_id' => 3, 'rating' => 4, 'body' => 'Good'], 9);
        self::assertTrue($service->moderate($id, 'approved', 1));
        self::assertSame(3, $repository->rebuiltMechanicId);
    }

    public function testPublicApprovedReviewDoesNotExposeAuthorIdentity(): void
    {
        $repository = new InMemoryReviewRepository();
        $service = new ReviewService($repository, static fn (): bool => true);
        $id = $service->submit(['mechanic_id' => 3, 'rating' => 4, 'body' => 'Good'], 99);
        $service->moderate($id, 'approved', 1);
        $data = $service->publicById($id);
        self::assertArrayNotHasKey('wp_user_id', $data);
        self::assertSame(4, $data['rating']);
    }
}

final class InMemoryReviewRepository implements ReviewRepository
{
    private array $records = [];
    private int $next = 1;
    public int $rebuiltMechanicId = 0;
    public function create(array $data): int { $id = $this->next++; $this->records[$id] = $data + ['id' => $id, 'status' => 'active']; return $id; }
    public function find(int $id): ?array { return $this->records[$id] ?? null; }
    public function updateModeration(int $id, string $status, int $actorId): bool { $this->records[$id]['moderation_status'] = $status; return true; }
    public function createReport(array $data): int { return 1; }
    public function rebuildMechanicSummary(int $mechanicId): bool { $this->rebuiltMechanicId = $mechanicId; return true; }
}
