@php
    /** @var \App\DTOs\FPVDutyReportDTO $reportData */
@endphp
<div class="report-header mb-4">
    <h4 class="mb-3">ПДП "{{ $reportData->getCombatShiftData()->getPositionName() }}" ({{$reportData->getCombatShiftData()->getType()->label()}})</h4>
</div>
<div class="remains-section">
    <div class="drones-block mb-4">
        <p class="font-weight-bold mb-2">Вильоти:</p>
        <ul class="list-unstyled pl-0">
            @php /** @var array $flights */ @endphp
            @foreach($reportData->getFlights() as $key => $value)
                <li class="mb-1">{{ $key . ' - '. $value }}</li>
            @endforeach
        </ul>
    </div>
</div>
