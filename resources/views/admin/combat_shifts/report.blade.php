@extends('adminlte::page')

@section('title', 'Звіт по залишку')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Звіт по залишку</h1>
        <div>
            <a href="{{ route('combat_shifts.show', $shift->id) }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Назад до деталей
            </a>
            <button onclick="window.print()" class="btn btn-success ml-2">
                <i class="fas fa-print"></i> Друкувати
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body p-5" id="report-content">
                    <div class="report-header mb-4">
                        <h4 class="mb-3">Позиція {{ $shift->position_name }}</h4>
                        <h5>День {{ $dayNumber }}</h5>
                    </div>

                    <div class="crew-section mb-4">
                        @foreach($shift->crew as $index => $member)
                            <p class="mb-1">{{ $index + 1 }}. {{ $member['callsign'] }}</p>
                        @endforeach
                    </div>

                    <div class="remains-section">
                        <h5 class="font-weight-bold mb-3">В наявності</h5>

                        <div class="drones-block mb-4">
                            <p class="font-weight-bold mb-2">Дрони:</p>
                            <ul class="list-unstyled pl-0">
                                @forelse($shift->drones as $drone)
                                    @if($drone['quantity'] > 0)
                                        <li class="mb-1">{{ $drone['name'] }} {{ $drone['model'] }} - {{ $drone['quantity'] }} шт</li>
                                    @endif
                                @empty
                                    <li>Відсутні</li>
                                @endforelse
                            </ul>
                        </div>

                        <div class="ammunition-block">
                            <p class="font-weight-bold mb-2">БК:</p>
                            <ol class="pl-0" style="list-style-type: none;">
                                @forelse($shift->ammunition as $index => $item)
                                    @if($item['quantity'] > 0)
                                        <li class="mb-1">{{ $index + 1 }}. {{ $item['name'] }} - {{ $item['quantity'] }} шт</li>
                                    @endif
                                @empty
                                    <li>Відсутні</li>
                                @endforelse
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <style>
        @media print {
            .main-header, .main-sidebar, .main-footer, .content-header .btn {
                display: none !important;
            }
            .content-wrapper {
                margin-left: 0 !important;
                background-color: white !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
            .card-body {
                padding: 0 !important;
            }
            body {
                background-color: white !important;
            }
        }
        #report-content {
            font-family: "Courier New", Courier, monospace;
            font-size: 1.1rem;
            line-height: 1.6;
            color: #000;
        }
        .report-header h4, .report-header h5 {
            font-weight: bold;
        }
    </style>
@endsection
