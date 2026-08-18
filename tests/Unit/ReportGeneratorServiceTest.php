<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ReportGeneratorService;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class ReportGeneratorServiceTest extends TestCase
{
    public function test_it_throws_exception_if_template_missing()
    {
        $service = new ReportGeneratorService();
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Undefined array key "totalDilaporkan"');
        
        $service->generatePersentaseDocx([], []);
    }
}
