@php
    /** @var \App\DTOs\UGVDutyReportDTO $reportData */
@endphp
<div class="report-header mb-4">
    <h4 class="mb-3">ПДП "{{ $reportData->getCombatShiftData()->getPositionName() }}" (НРК)</h4>
</div>
<div class="remains-section">
    <div class="drones-block mb-4">
        <p class="font-weight-bold mb-2">Рейси:</p>
        <ul class="list-unstyled pl-0">
            @foreach($reportData->getRaces() as $key => $value)
                <li class="mb-1">{{ $key . ' - '. $value }}</li>
            @endforeach
        </ul>
    </div>
</div>
