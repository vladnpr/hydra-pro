@php
    /** @var \App\DTOs\UGVDutyReportDTO $reportData */
@endphp
<div class="report-header mb-4">
    <h4 class="mb-3">ПДП "{{ $reportData->getCombatShiftData()->getPositionName() }}" ({{$reportData->getCombatShiftData()->getType()->label()}})</h4>
    <h5>{{ $reportData->getCombatShiftData()->getTeamNumber()}} в/с </h5>
    <h5>@if($reportData->getDronesRemaining()->count() == 0)
            Не БГ
        @else
            БГ
        @endif
    </h5>
</div>
<div class="remains-section">
    <h5 class="font-weight-bold mb-3">В наявності</h5>
    <div class="drones-block mb-4">
        <p class="font-weight-bold mb-2">БГ Борти:</p>
        <ul class="list-unstyled pl-0">
            @php /** @var \App\DTOs\UGVDroneDTO $ugvDroneDTO */ @endphp
            @forelse($reportData->getDronesRemaining() as $ugvDroneDTO)
                <li class="mb-1">{{$ugvDroneDTO->getName()}}</li>
            @empty
                <li>Відсутні</li>
            @endforelse
        </ul>
    </div>
</div>
@if($reportData->getAmmoRemaining()->count() > 0)
    <div class="ammunition-block">
        <p class="font-weight-bold mb-2">БК:</p>
        <ol class="pl-0" style="list-style-type: none;">
            @php /** @var \App\DTOs\AmmunitionRemainingDTO $ammunitionRemainingDTO */ @endphp
            @foreach($reportData->getAmmoRemaining() as $index => $ammunitionRemainingDTO)
                <li class="mb-1">{{ $index+1 . '. ' . $ammunitionRemainingDTO->getName() . ' - ' . $ammunitionRemainingDTO->getQuantity() . ' шт.' }} </li>
            @endforeach
        </ol>
    </div>
@endif
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
