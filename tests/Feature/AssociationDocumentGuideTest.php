<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Alp;
use App\Services\Documents\AssociationDocumentGuideService;
use Tests\Concerns\BuildsWorkflow;
use Tests\TestCase;

class AssociationDocumentGuideTest extends TestCase
{
    use BuildsWorkflow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflow();
    }

    public function test_alp_can_download_association_document_guide_pdf(): void
    {
        $alp = Alp::factory()->create();
        $user = $this->userWithRole(RoleName::ALP->value, $alp);

        $response = $this->actingAs($user)->get(route('association-guide.download'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertGreaterThan(5000, strlen($response->getContent()));
    }

    public function test_guest_cannot_download_association_document_guide_pdf(): void
    {
        $this->get(route('association-guide.download'))->assertRedirect(route('login'));
    }

    public function test_service_merges_guide_with_report_card_and_eft_form(): void
    {
        $service = app(AssociationDocumentGuideService::class);

        $this->assertTrue($service->eftFormExists());
        $this->assertTrue($service->reportCardTemplateExists());

        $pdf = $service->generate();

        $this->assertStringStartsWith('%PDF', $pdf);
        $this->assertGreaterThan(15000, strlen($pdf));
    }
}
