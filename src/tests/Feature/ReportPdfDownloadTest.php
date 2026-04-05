<?php

namespace Tests\Feature;

use App\Http\Controllers\ReportController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery;
use Tests\TestCase;

class ReportPdfDownloadTest extends TestCase
{
    public function test_it_downloads_a_report_pdf_by_uuid(): void
    {
        $mock = Mockery::mock(ReportController::class);
        $mock->shouldReceive('downloadPdf')
            ->once()
            ->with('11111111-1111-1111-1111-111111111111')
            ->andReturn(response('%PDF-1.4 fake', 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="monthly-html-report.pdf"',
            ]));

        $this->app->instance(ReportController::class, $mock);

        $response = $this->get('/reports/11111111-1111-1111-1111-111111111111/pdf');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $response->assertHeader('content-disposition', 'attachment; filename="monthly-html-report.pdf"');
        $this->assertStringContainsString('%PDF-1.4 fake', $response->getContent());
    }

    public function test_it_returns_not_found_for_a_missing_report_pdf_uuid(): void
    {
        $mock = Mockery::mock(ReportController::class);
        $mock->shouldReceive('downloadPdf')
            ->once()
            ->with('99999999-9999-9999-9999-999999999999')
            ->andThrow(new ModelNotFoundException());

        $this->app->instance(ReportController::class, $mock);

        $response = $this->get('/reports/99999999-9999-9999-9999-999999999999/pdf');

        $response->assertNotFound();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}