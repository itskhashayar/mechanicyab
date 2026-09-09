<?php

declare(strict_types=1);

namespace MechanicYab\Core\Tests;

use MechanicYab\Core\Contracts\LedgerRepository;
use MechanicYab\Core\Core\LedgerService;
use PHPUnit\Framework\TestCase;

final class LedgerServiceTest extends TestCase
{
    public function testBalancedPostingIsAccepted(): void { $repo=new FakeLedgerRepository(); self::assertTrue((new LedgerService($repo))->post([['entry_key'=>'p1-d','account_code'=>'cash','direction'=>'debit','amount'=>10000],['entry_key'=>'p1-c','account_code'=>'revenue','direction'=>'credit','amount'=>10000]])); }
    public function testUnbalancedPostingIsRejected(): void { $this->expectException(\DomainException::class); (new LedgerService(new FakeLedgerRepository()))->post([['entry_key'=>'x','account_code'=>'cash','direction'=>'debit','amount'=>10000],['entry_key'=>'y','account_code'=>'revenue','direction'=>'credit','amount'=>9000]]); }
}
final class FakeLedgerRepository implements LedgerRepository { public function post(array $entries): bool { return true; } }
