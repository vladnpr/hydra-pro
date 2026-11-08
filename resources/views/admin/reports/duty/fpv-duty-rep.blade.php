@php
    /** @var \App\DTOs\FPVDutyReportDTO $reportData */
@endphp
<div class="card-body p-5" id="report-content">
    <div class="report-header mb-4">
        <h4 class="mb-3">Позиція "{{ $reportData->getCombatShiftData()->getPositionName() }}" (FPV)</h4>
        <h5>{{ $reportData->getCombatShiftData()->getTeamNumber()}} в/с </h5>
        <h5>@if($reportData->getDronesRemainingCollection()->count() == 0) Не БГ @else БГ @endif </h5>
    </div>
    <div class="remains-section">
        <h5 class="font-weight-bold mb-3">В наявності</h5>
        <div class="drones-block mb-4">
            <p class="font-weight-bold mb-2">БГ Борти:</p>
            <ul class="list-unstyled pl-0">
                @php /** @var \App\DTOs\FPVDronesRemainingDTO $FPVDronesRemainingDTO */ @endphp
                @forelse($reportData->getDronesRemainingCollection() as $FPVDronesRemainingDTO)
                    <li class="mb-1">{{$FPVDronesRemainingDTO->getName() . ' ' . $FPVDronesRemainingDTO->getModel() .' - '. $FPVDronesRemainingDTO->getQuantity() . ' шт.'}}</li>
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
            <p class="font-weight-bold mb-2">Вильоти:</p>
            <ul class="list-unstyled pl-0">
                @php /** @var array $flights */ @endphp
                @foreach($reportData->getFlights() as $key => $value)
                    <li class="mb-1">{{ $key . ' - '. $value }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
