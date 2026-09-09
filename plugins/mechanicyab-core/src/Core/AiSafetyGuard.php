<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

final class AiSafetyGuard
{
    /** @return array{allowed: bool, risk: string, message: string|null} */
    public function inspect(string $input): array
    {
        $lower = mb_strtolower(trim($input));
        if ($lower === '') { return ['allowed'=>false,'risk'=>'invalid','message'=>'Input is required.']; }
        foreach (['ignore previous instructions','reveal system prompt','show secret','api key','رمز عبور','کلید api'] as $pattern) { if (str_contains($lower, $pattern)) { return ['allowed'=>false,'risk'=>'prompt_injection','message'=>'این درخواست قابل پردازش نیست.']; } }
        foreach (['ترمز بریده','بوی بنزین','آتش','دود زیاد','brake failure','fuel leak','fire','smoke'] as $pattern) { if (str_contains($lower, $pattern)) { return ['allowed'=>true,'risk'=>'high','message'=>'در صورت خطر فوری خودرو را متوقف کنید و با امداد یا متخصص تماس بگیرید.']; } }
        return ['allowed'=>true,'risk'=>'normal','message'=>null];
    }
    public function validateOutput(string $output): string { $output = trim($output); if ($output === '') { throw new \RuntimeException('AI returned an empty response.'); } return $output; }
}
