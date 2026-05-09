<?php

namespace App\Enums;

enum ExportType: string
{
    case ChecklistInstance = 'checklist_instance';
    case ChecklistReport = 'checklist_report';
    case ChecklistTemplate = 'checklist_template';
    case ComplianceSnapshot = 'compliance_snapshot';
    case AuditorActivity = 'auditor_activity';
}
