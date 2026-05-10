<?php

namespace Tests\Unit;

use App\Support\QuestionTextNormalizer;
use PHPUnit\Framework\TestCase;

class QuestionTextNormalizerTest extends TestCase
{
    public function test_trims_and_collapses_whitespace_and_is_case_insensitive(): void
    {
        $a = 'Risk Assessment Date';
        $b = '  risk   assessment   date  ';

        $this->assertSame(
            QuestionTextNormalizer::normalize($a),
            QuestionTextNormalizer::normalize($b)
        );
    }
}
