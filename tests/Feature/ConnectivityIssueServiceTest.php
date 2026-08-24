<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Agency;
use App\Models\Device;
use App\Models\ErrorLog;
use App\Models\AlertRecipient;
use App\Services\ConnectivityIssueService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConnectivityIssueServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ConnectivityIssueService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ConnectivityIssueService();
    }

    /** @test */
    public function it_records_connectivity_issue_for_agency()
    {
        $agency = Agency::factory()->create();
        
        $issue = $this->service->recordAgencyConnectivityIssue($agency);

        $this->assertInstanceOf(ErrorLog::class, $issue);
        $this->assertEquals('Panne Agence', $issue->error_type);
        $this->assertFalse($issue->is_resolved);
        $this->assertFalse($issue->mail_sent);
    }

    /** @test */
    public function it_creates_router_device_if_not_exists()
    {
        $agency = Agency::factory()->create();
        
        $this->assertNull(
            Device::where('ip_address', $agency->router_ip)->first()
        );

        $this->service->recordAgencyConnectivityIssue($agency);

        $this->assertNotNull(
            Device::where('ip_address', $agency->router_ip)->first()
        );
    }

    /** @test */
    public function it_does_not_create_duplicate_issues()
    {
        $agency = Agency::factory()->create();
        
        $issue1 = $this->service->recordAgencyConnectivityIssue($agency);
        $issue2 = $this->service->recordAgencyConnectivityIssue($agency);

        $routerDevice = Device::where('ip_address', $agency->router_ip)->first();
        $unresolved = ErrorLog::where('device_id', $routerDevice->id)
            ->where('is_resolved', false)
            ->count();

        $this->assertEquals(1, $unresolved);
        $this->assertEquals($issue1->id, $issue2->id);
    }

    /** @test */
    public function it_sends_alert_email():void
    {
        Mail::fake();
        
        $agency = Agency::factory()->create();
        AlertRecipient::factory()->create(['email' => 'admin@test.local']);
        
        $issue = $this->service->recordAgencyConnectivityIssue($agency);
        $sent = $this->service->sendAlertEmail($agency, $issue);

        $this->assertTrue($sent);
        $this->assertTrue($issue->refresh()->mail_sent);
        Mail::assertQueued(\App\Mail\DeviceStatusAlert::class);
    }

    /** @test */
    public function it_does_not_send_duplicate_emails()
    {
        Mail::fake();
        
        $agency = Agency::factory()->create();
        AlertRecipient::factory()->create(['email' => 'admin@test.local']);
        
        $issue = $this->service->recordAgencyConnectivityIssue($agency);
        $this->service->sendAlertEmail($agency, $issue);

        // Tentative d'envoi d'un email déjà envoyé
        $this->service->sendAlertEmail($agency, $issue->refresh());

        // Vérifier qu'un seul email a été envoyé
        Mail::assertQueuedCount(1);
    }

    /** @test */
    public function it_resolves_connectivity_issue()
    {
        $agency = Agency::factory()->create();
        
        $issue = $this->service->recordAgencyConnectivityIssue($agency);
        $this->assertFalse($issue->is_resolved);

        $resolved = $this->service->resolveConnectivityIssue($agency);

        $this->assertTrue($resolved->is_resolved);
        $this->assertNotNull($resolved->resolved_at);
        $this->assertEquals('Connexion rétablie', $resolved->resolution_note);
    }

    /** @test */
    public function it_resets_mail_sent_on_resolution()
    {
        $agency = Agency::factory()->create();
        
        $issue = $this->service->recordAgencyConnectivityIssue($agency);
        $issue->update(['mail_sent' => true]);

        $this->service->resolveConnectivityIssue($agency);

        $this->assertFalse($issue->refresh()->mail_sent);
    }

    /** @test */
    public function it_marks_issue_as_unresolved_on_new_problem()
    {
        $agency = Agency::factory()->create();
        
        $issue1 = $this->service->recordAgencyConnectivityIssue($agency);
        $this->service->resolveConnectivityIssue($agency);

        // Nouveau problème
        $issue2 = $this->service->recordAgencyConnectivityIssue($agency);

        $this->assertFalse($issue2->is_resolved);
        $this->assertFalse($issue2->mail_sent);
    }

    /** @test */
    public function it_retrieves_connectivity_issues_via_scope()
    {
        $agency = Agency::factory()->create();
        $this->service->recordAgencyConnectivityIssue($agency, 'Panne Agence');

        $issues = ErrorLog::connectivityIssues()->unsent()->get();

        $this->assertCount(1, $issues);
        $this->assertTrue($issues->first()->error_type === 'Panne Agence');
    }
}
