<?php

namespace Database\Seeders\Support;

use App\Enums\ChecklistQuestionType;

/**
 * Single source for demo templates + question blueprints consumed by checklist seeders.
 *
 * @phpstan-import-type QuestionDef from self
 */
final class ComplianceTemplateDefinitions
{
    /**
     * Shared select options used across audits (value/label pairs).
     */
    public static function complianceLevels(): array
    {
        return [
            ['value' => 'exceeds', 'label' => 'Exceeds policy'],
            ['value' => 'meets', 'label' => 'Meets policy'],
            ['value' => 'partial', 'label' => 'Partially meets'],
            ['value' => 'gap', 'label' => 'Gap identified'],
        ];
    }

    public static function yesNoPartial(): array
    {
        return [
            ['value' => 'yes', 'label' => 'Yes'],
            ['value' => 'no', 'label' => 'No'],
            ['value' => 'partial', 'label' => 'Partially'],
        ];
    }

    /**
     * @return list array{
     *              name:string,
     *              description:string,
     *              status:string,
     *              questions:list<QuestionDef>,
     *              }
     *
     * @phpstan-type QuestionDef array{
     *   key:string,
     *   label:string,
     *   help_text:?string,
     *   type:ChecklistQuestionType,
     *   is_required:bool,
     *   sort_order:int,
     *   options:?list<array{value:string,label:string}>
     * }
     */
    public static function templates(): array
    {
        return [
            self::tpl(
                name: 'Workplace Safety Inspection',
                description: 'Periodic walk-through covering hazard controls, housekeeping, signage, and PPE compliance.',
                sortBase: 0,
                extras: fn (int $base) => [
                    Q::mk('ws_emergency_marked', 'Are emergency exits clearly marked and unobstructed?', null, ChecklistQuestionType::Boolean, true, $base + 40),
                    Q::mk('ws_ppe_stock', 'Is employee PPE available, stocked, and within inspection dates?', null, ChecklistQuestionType::Boolean, true, $base + 50),
                    Q::mk('ws_housekeeping', 'Overall housekeeping meets site safety rules', null, ChecklistQuestionType::Select, true, $base + 60,
                        ComplianceTemplateDefinitions::complianceLevels()
                    ),
                    Q::mk('ws_last_near_miss', 'Date of last documented near-miss review', null, ChecklistQuestionType::Date, false, $base + 70),
                    Q::mk('ws_walkthrough_notes', 'Notes from today’s inspection walk-through', null, ChecklistQuestionType::Textarea, false, $base + 80),
                    Q::mk('ws_supervisor_contact', 'Site safety coordinator email', null, ChecklistQuestionType::Email, false, $base + 90),
                    Q::mk('ws_safety_hotline', 'Site safety desk phone extension', null, ChecklistQuestionType::Phone, false, $base + 100),
                ]
            ),
            self::tpl(
                name: 'Fire Safety Compliance Audit',
                description: 'Extinguishers, alarms, egress drills, signage, training records.',
                sortBase: 200,
                extras: fn (int $base) => [
                    Q::mk('fire_ext_serviced', 'When was each floor’s primary extinguisher last serviced?', null, ChecklistQuestionType::Date, true, $base + 40),
                    Q::mk('fire_alarm_test', 'Monthly alarm test documented for this reporting period?', null, ChecklistQuestionType::Boolean, true, $base + 50),
                    Q::mk('fire_drill_recent', 'Date/time of last full evacuation drill', null, ChecklistQuestionType::DateTime, false, $base + 60),
                    Q::mk('fire_risk_rating', 'Fire readiness score (facility self-assessment 0–10)', null, ChecklistQuestionType::Number, true, $base + 70),
                    Q::mk('fire_escapes_ok', 'Egress readiness', null, ChecklistQuestionType::Radio, true, $base + 80, ComplianceTemplateDefinitions::yesNoPartial()),
                    Q::mk('fire_evidence_pack', 'URL to archived drill evidence package', null, ChecklistQuestionType::Url, false, $base + 90),
                ]
            ),
            self::tpl(
                name: 'Data Protection Compliance Review',
                description: 'ROPA excerpts, DPIA posture, subprocessors, breach readiness.',
                sortBase: 300,
                extras: fn (int $base) => [
                    Q::mk('dp_last_dpa_review', 'Date of last vendor DPA renewal review', null, ChecklistQuestionType::Date, true, $base + 40),
                    Q::mk('dp_breach_drill', 'Breach escalation drills completed quarterly?', null, ChecklistQuestionType::Boolean, true, $base + 50),
                    Q::mk('dp_encryption_scope', 'Select systems covered by encryption-at-rest policy', null, ChecklistQuestionType::Checkbox, true, $base + 60,
                        [
                            ['value' => 'crm', 'label' => 'CRM / customer datastore'],
                            ['value' => 'warehouse', 'label' => 'Analytics warehouse'],
                            ['value' => 'backups', 'label' => 'Backups / archives'],
                            ['value' => 'endpoints', 'label' => 'Endpoints'],
                        ]
                    ),
                    Q::mk('dp_dsr_sla_hours', 'Average DSR acknowledgement time (hours)', null, ChecklistQuestionType::Number, false, $base + 70),
                    Q::mk('dp_notes', 'Key findings summary', null, ChecklistQuestionType::Textarea, false, $base + 80),
                    Q::mk('dp_privacy_lead_mail', 'Registered privacy inbox', null, ChecklistQuestionType::Email, true, $base + 90),
                ]
            ),
            self::tpl(
                name: 'Equipment Maintenance Checklist',
                description: 'Scheduled maintenance, anomalies, overdue work orders.',
                sortBase: 400,
                extras: fn (int $base) => [
                    Q::mk('maint_last_pm', 'Date of last preventative maintenance', null, ChecklistQuestionType::Date, true, $base + 40),
                    Q::mk('maint_open_orders', 'Count of overdue work orders (>7 days)', null, ChecklistQuestionType::Number, true, $base + 50),
                    Q::mk('maint_spares_critical', 'Are critical spares stocked to policy minimum?', null, ChecklistQuestionType::SingleSelect, true, $base + 60,
                        ComplianceTemplateDefinitions::yesNoPartial()
                    ),
                    Q::mk('maint_criticality', 'Asset criticality for this audit window', null, ChecklistQuestionType::Select, true, $base + 70,
                        [
                            ['value' => 'tier1', 'label' => 'Tier 1 (production blocker)'],
                            ['value' => 'tier2', 'label' => 'Tier 2 (degraded)'],
                            ['value' => 'tier3', 'label' => 'Tier 3 (non-critical)'],
                        ]
                    ),
                    Q::mk('maint_comment', 'Supervisor commentary', null, ChecklistQuestionType::Textarea, false, $base + 80),
                    Q::mk('maint_vendor_hotline', 'OEM escalations phone line', null, ChecklistQuestionType::Phone, false, $base + 90),
                ]
            ),
            self::tpl(
                name: 'Employee Onboarding Compliance',
                description: 'Pre-hire checks, policy attestations, access provisioning checkpoints.',
                sortBase: 500,
                extras: fn (int $base) => [
                    Q::mk('onb_offer_signed', 'Offer letter digitally signed?', null, ChecklistQuestionType::Boolean, true, $base + 40),
                    Q::mk('onb_bg_complete', 'Background check completed?', null, ChecklistQuestionType::Boolean, true, $base + 50),
                    Q::mk('onb_started_at', 'Employee first-day orientation start timestamp', null, ChecklistQuestionType::DateTime, false, $base + 60),
                    Q::mk('onb_modules', 'Required training modules acknowledged', null, ChecklistQuestionType::MultiSelect, true, $base + 70,
                        [
                            ['value' => 'codes', 'label' => 'Code of Conduct'],
                            ['value' => 'security', 'label' => 'Security awareness'],
                            ['value' => 'harassment', 'label' => 'Anti-harassment'],
                            ['value' => 'privacy', 'label' => 'Privacy basics'],
                            ['value' => 'safety', 'label' => 'Site safety primer'],
                        ]
                    ),
                    Q::mk('onb_ticket_url', 'IT provisioning ticket tracker link', null, ChecklistQuestionType::Url, true, $base + 80),
                    Q::mk('onb_escalations_email', 'HRBP escalation mailbox', null, ChecklistQuestionType::Email, false, $base + 90),
                ]
            ),
            self::tpl(
                name: 'Warehouse Inspection Audit',
                description: 'Racking, forklift lanes, spills, lithium storage, CCTV lighting.',
                sortBase: 600,
                extras: fn (int $base) => [
                    Q::mk('wh_routes_clear', 'Forklift aisles obstruction-free?', null, ChecklistQuestionType::Boolean, true, $base + 40),
                    Q::mk('wh_spills', 'Number of unresolved spill hazards observed', null, ChecklistQuestionType::Number, true, $base + 50),
                    Q::mk('wh_audit_window', 'Shift window inspected', null, ChecklistQuestionType::Select, true, $base + 60,
                        [
                            ['value' => 'am', 'label' => 'AM shift'],
                            ['value' => 'pm', 'label' => 'PM shift'],
                            ['value' => 'night', 'label' => 'Night shift'],
                        ]
                    ),
                    Q::mk('wh_racking_status', 'Racking upright condition snapshot', null, ChecklistQuestionType::Radio, true, $base + 70,
                        ComplianceTemplateDefinitions::complianceLevels()
                    ),
                    Q::mk('wh_supervisor_mobile', 'Shift supervisor SMS-capable mobile', null, ChecklistQuestionType::Phone, true, $base + 80),
                    Q::mk('wh_details', 'Notable deviations / corrective actions issued', null, ChecklistQuestionType::Textarea, false, $base + 90),
                    Q::mk('wh_last_cap', 'CAPA review date covering prior findings', null, ChecklistQuestionType::Date, false, $base + 100),
                ]
            ),
            self::tpl(
                name: 'Information Security Assessment',
                description: 'Identity, endpoint, patching, logging, phishing posture snapshots.',
                sortBase: 700,
                extras: fn (int $base) => [
                    Q::mk('sec_mfa_rollout_pct', '% workforce with phishing-resistant MFA enforced', null, ChecklistQuestionType::Number, true, $base + 40),
                    Q::mk('sec_patch_exceptions', 'Open critical patching exceptions?', null, ChecklistQuestionType::Boolean, true, $base + 50),
                    Q::mk('sec_log_review', 'Date of last SOC log review sign-off', null, ChecklistQuestionType::Date, true, $base + 60),
                    Q::mk('sec_ir_playbook_refresh', 'IR tabletop exercise timestamp', null, ChecklistQuestionType::DateTime, false, $base + 70),
                    Q::mk('sec_data_flows_reviewed', 'Reviewed regulated data flows for this BU?', null, ChecklistQuestionType::Boolean, false, $base + 80),
                    Q::mk('sec_ciso_notes', 'CISO escalation notes', null, ChecklistQuestionType::Textarea, false, $base + 90),
                    Q::mk('sec_soc_email', 'Security operations distribution list', null, ChecklistQuestionType::Email, true, $base + 100),
                ]
            ),
            self::tpl(
                name: 'Environmental Compliance Review',
                description: 'Waste manifests, SDS availability, containment, wastewater sampling posture.',
                sortBase: 800,
                extras: fn (int $base) => [
                    Q::mk('env_manifest_current', 'Hazardous waste manifests current?', null, ChecklistQuestionType::Boolean, true, $base + 40),
                    Q::mk('env_sampling_recent', 'Last wastewater sampling event date/time', null, ChecklistQuestionType::DateTime, false, $base + 50),
                    Q::mk('env_spillkits', 'Count of refill-ready spill kits on floor', null, ChecklistQuestionType::Number, true, $base + 60),
                    Q::mk('env_regulator_url', 'Regulator-facing permit portal link', null, ChecklistQuestionType::Url, false, $base + 70),
                    Q::mk('env_epc_contact', 'EHS duty phone tree number', null, ChecklistQuestionType::Phone, true, $base + 80),
                    Q::mk('env_escalations', 'Select outstanding environmental corrective actions tracked', null, ChecklistQuestionType::Checkbox, false, $base + 90,
                        [
                            ['value' => 'air', 'label' => 'Air permit deviations'],
                            ['value' => 'water', 'label' => 'Water discharge monitoring'],
                            ['value' => 'waste', 'label' => 'Waste storage duration'],
                            ['value' => 'noise', 'label' => 'Noise abatement follow-up'],
                        ]
                    ),
                ]
            ),
            self::tpl(
                name: 'Risk Assessment Checklist',
                description: 'Inherent/residual scoring, mitigation owners, review cadence.',
                sortBase: 900,
                extras: fn (int $base) => [
                    Q::mk('risk_last_assessment', 'Date of last enterprise risk workshop', null, ChecklistQuestionType::Date, true, $base + 40),
                    Q::mk('risk_inherent_rating', 'Inherent residual score (worst plausible scenario)', null, ChecklistQuestionType::Select, true, $base + 50,
                        [
                            ['value' => 'low', 'label' => 'Low'],
                            ['value' => 'med', 'label' => 'Medium'],
                            ['value' => 'high', 'label' => 'High'],
                            ['value' => 'crit', 'label' => 'Critical'],
                        ]
                    ),
                    Q::mk('risk_mitigations_tracked', 'Mitigation tasks on-track?', null, ChecklistQuestionType::Boolean, true, $base + 60),
                    Q::mk('risk_owner_voice', 'Primary risk owner voicemail line', null, ChecklistQuestionType::Phone, false, $base + 70),
                    Q::mk('risk_summary', 'Key themes / hotspots', null, ChecklistQuestionType::Textarea, true, $base + 80),
                ]
            ),
            self::tpl(
                name: 'Incident Response Readiness Audit',
                description: 'Playbooks, comms bridges, tooling, escalation trees tested.',
                sortBase: 1000,
                extras: fn (int $base) => [
                    Q::mk('ir_tabletop_recent', 'Date/time of tabletop exercise', null, ChecklistQuestionType::DateTime, true, $base + 40),
                    Q::mk('ir_bridge_ok', 'Exec comms bridge tested within SLA?', null, ChecklistQuestionType::Boolean, true, $base + 50),
                    Q::mk('ir_tooling_status', 'IR tooling telemetry coverage', null, ChecklistQuestionType::Radio, true, $base + 60,
                        [
                            ['value' => 'full', 'label' => 'Fully instrumented'],
                            ['value' => 'partial', 'label' => 'Partial coverage'],
                            ['value' => 'blind', 'label' => 'Meaningful blind spots remain'],
                        ]
                    ),
                    Q::mk('ir_external_counsel', 'Breach counsel retainer acknowledgement email', null, ChecklistQuestionType::Email, false, $base + 70),
                    Q::mk('ir_score', 'Weighted readiness numeric score', null, ChecklistQuestionType::Number, false, $base + 80),
                    Q::mk('ir_after_actions', 'After-action deltas since last rehearsal', null, ChecklistQuestionType::Textarea, false, $base + 90),
                    Q::mk('ir_status_portal', 'Status page runbook hyperlink', null, ChecklistQuestionType::Url, true, $base + 100),
                ]
            ),
            self::tpl(
                name: 'HIPAA Privacy Walkthrough',
                description: 'PHI workstation posture, disclosures, BAAs, sanction policy.',
                sortBase: 1100,
                extras: fn (int $base) => [
                    Q::mk('hipaa_baa_current', 'Covered BAAs refreshed within renewal window?', null, ChecklistQuestionType::Boolean, true, $base + 40),
                    Q::mk('hipaa_phi_workstations_locked', 'Workstations idle-lock enforced?', null, ChecklistQuestionType::Boolean, true, $base + 50),
                    Q::mk('hipaa_notice_url', 'NPP acknowledgment tracking URL', null, ChecklistQuestionType::Url, false, $base + 60),
                    Q::mk('hipaa_notes', 'Findings commentary', null, ChecklistQuestionType::Textarea, false, $base + 70),
                    Q::mk('hipaa_priv_officer', 'Privacy officer inbox', null, ChecklistQuestionType::Email, true, $base + 80),
                ]
            ),
            self::tpl(
                name: 'Chemical Handling Audit',
                description: 'SDS binders, secondary containment, PPE coupling, VOC controls.',
                sortBase: 1200,
                extras: fn (int $base) => [
                    Q::mk('chem_sds_posted', 'SDS visibly posted/stored?', null, ChecklistQuestionType::Boolean, true, $base + 40),
                    Q::mk('chem_last_inventory_cycle', 'Date of last reconciliation cycle', null, ChecklistQuestionType::Date, true, $base + 50),
                    Q::mk('chem_secondary_containment', 'Secondary containment satisfies capacity rule?', null, ChecklistQuestionType::SingleSelect, true, $base + 60,
                        ComplianceTemplateDefinitions::yesNoPartial()
                    ),
                    Q::mk('chem_lab_coat_policy', 'PPE conformance rating', null, ChecklistQuestionType::Select, true, $base + 70,
                        ComplianceTemplateDefinitions::complianceLevels()
                    ),
                    Q::mk('chem_call_tree', 'EHS escalation phone roster', null, ChecklistQuestionType::Phone, true, $base + 80),
                    Q::mk('chem_incident_followup_ts', 'Last VOC incident corrective action checkpoint', null, ChecklistQuestionType::DateTime, false, $base + 90),
                ]
            ),
            self::tpl(
                name: 'Facility Access Review',
                description: 'Badging, expiry sweeps, tailgating drills, SOC correlation.',
                sortBase: 1300,
                extras: fn (int $base) => [
                    Q::mk('acc_expired_pct', '% expired badges still active (SOC sample)', null, ChecklistQuestionType::Number, true, $base + 40),
                    Q::mk('acc_tailgate_drill_recent', 'Date of last tailgating awareness drill', null, ChecklistQuestionType::Date, false, $base + 50),
                    Q::mk('acc_zones_ok', 'All sensitive zones segmented per policy?', null, ChecklistQuestionType::Boolean, true, $base + 60),
                    Q::mk('acc_soc_dl', 'Physical security distro email', null, ChecklistQuestionType::Email, true, $base + 70),
                    Q::mk('acc_evidence_ticket', 'PSIM ticket linkage', null, ChecklistQuestionType::Url, false, $base + 80),
                ]
            ),
            self::tpl(
                name: 'Vendor Due Diligence Checklist',
                description: 'Financial health, SOC reports, DPIA artefacts, contingency testing.',
                sortBase: 1400,
                extras: fn (int $base) => [
                    Q::mk('vdd_soc2', 'SOC2 Type II artifact on file?', null, ChecklistQuestionType::Boolean, true, $base + 40),
                    Q::mk('vdd_dpa_signed', 'DPA countersigned?', null, ChecklistQuestionType::Boolean, true, $base + 50),
                    Q::mk('vdd_assessment_grade', 'Due diligence disposition', null, ChecklistQuestionType::Radio, true, $base + 60,
                        [
                            ['value' => 'approve', 'label' => 'Approve'],
                            ['value' => 'cond', 'label' => 'Approve with conditions'],
                            ['value' => 'defer', 'label' => 'Defer'],
                            ['value' => 'reject', 'label' => 'Reject'],
                        ]
                    ),
                    Q::mk('vdd_procurement_mail', 'Procurement liaison mailbox', null, ChecklistQuestionType::Email, true, $base + 70),
                    Q::mk('vdd_comments', 'Notes / compensating controls', null, ChecklistQuestionType::Textarea, false, $base + 80),
                    Q::mk('vdd_next_review_dt', 'Next scheduled diligence checkpoint', null, ChecklistQuestionType::DateTime, false, $base + 90),
                ]
            ),
            self::tpl(
                name: 'Business Continuity Drills Audit',
                description: 'RTO/RPO tests, failover evidence, stakeholder comms rehearsals.',
                sortBase: 1500,
                extras: fn (int $base) => [
                    Q::mk('bc_failover_recent', 'Date/time failover rehearsal conducted', null, ChecklistQuestionType::DateTime, true, $base + 40),
                    Q::mk('bc_rpo_met', 'RPO envelopes met?', null, ChecklistQuestionType::Boolean, true, $base + 50),
                    Q::mk('bc_workstream_select', 'Workstreams exercised', null, ChecklistQuestionType::Checkbox, true, $base + 60,
                        [
                            ['value' => 'compute', 'label' => 'Compute / platform failover'],
                            ['value' => 'data', 'label' => 'Data restoration'],
                            ['value' => 'comms', 'label' => 'Customer comms rehearsals'],
                            ['value' => 'people', 'label' => 'People / HR routing'],
                            ['value' => 'third_party', 'label' => 'Third-party failover dependencies'],
                        ]
                    ),
                    Q::mk('bc_commander_digits', 'Crisis commander after-hours DID', null, ChecklistQuestionType::Phone, true, $base + 70),
                    Q::mk('bc_portal_evidence_url', 'BCMS evidence locker URL', null, ChecklistQuestionType::Url, false, $base + 80),
                    Q::mk('bc_rto_hours', 'Observed failover-to-recovery elapsed hours', null, ChecklistQuestionType::Number, false, $base + 90),
                ]
            ),
        ];
    }

    /**
     * @param  callable(int): array<int,mixed>  $extras
     */
    private static function tpl(string $name, string $description, int $sortBase, callable $extras): array
    {
        $core = [
            Q::mk('site_name', 'Site or location identifier', null, ChecklistQuestionType::Text, true, $sortBase + 10),
            Q::mk('risk_assessment_date', 'Date of last risk assessment for this scope', null, ChecklistQuestionType::Date, true, $sortBase + 20),
            Q::mk('overall_readiness', 'Overall readiness disposition', null, ChecklistQuestionType::Select, true, $sortBase + 30,
                ComplianceTemplateDefinitions::complianceLevels()
            ),
        ];

        return [
            'name' => $name,
            'description' => $description,
            'status' => self::canonicalStatusFor($name),
            'questions' => array_merge($core, $extras($sortBase)),
        ];
    }

    private static function canonicalStatusFor(string $name): string
    {
        // Keep most templates publishable while leaving a deliberate mix for dashboards/demos.
        return match ($name) {
            'HIPAA Privacy Walkthrough', 'Facility Access Review' => 'published',
            'Chemical Handling Audit' => 'draft',
            'Vendor Due Diligence Checklist' => 'archived',
            default => 'published',
        };
    }
}

/** Tiny helper for readability inside definitions. */
final class Q
{
    public static function mk(
        string $key,
        string $label,
        ?string $helpText,
        ChecklistQuestionType $type,
        bool $required,
        int $sortOrder,
        ?array $options = null,
    ): array {
        return [
            'key' => $key,
            'label' => $label,
            'help_text' => $helpText,
            'type' => $type,
            'is_required' => $required,
            'sort_order' => $sortOrder,
            'options' => $options,
        ];
    }
}
