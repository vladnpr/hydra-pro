@extends('adminlte::page')

@section('title', 'Звіт по витратам розвідки')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Звіт по витратам розвідки</h1>
        <a href="{{ url()->previous() }}" class="btn btn-default no-print">
            <i class="fas fa-arrow-left"></i> Назад
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body p-5" id="printableArea">
                    <div class="report-content">
                        <h4 class="mb-4">Позиція "{{ $position_name }}"</h4>

                        <h5 class="mt-4"><strong>Витрати</strong></h5>

                        @if(count($lost_drones) > 0)
                            <p class="mb-1"><strong>Борти:</strong></p>
                            @foreach($lost_drones as $drone)
                                <p class="mb-0">{{ $drone }}</p>
                            @endforeach
                        @endif

                        @if(count($ammunition) > 0)
                            <h5 class="mt-4"><strong>БК:</strong></h5>
                            @foreach($ammunition as $item)
                                <p class="mb-0">{{ $item->name }} - {{ (int)$item->total_quantity }} шт.</p>
                            @endforeach
                        @endif

                        @if(count($lost_drones) == 0 && count($ammunition) == 0)
                            <p class="text-muted">Витрат за цю зміну не зафіксовано.</p>
                        @endif
                    </div>
                </div>
                <div class="card-footer text-right no-print">
                    <button type="button" class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print mr-1"></i> Друкувати / Копіювати
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <style>
        .report-content {
            font-family: "Source Sans Pro", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
            font-size: 1.1rem;
            line-height: 1.5;
            color: #212529;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .content-wrapper {
                background: white !important;
            }
            .main-header, .main-sidebar, .main-footer {
                display: none !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
            .p-5 {
                padding: 0 !important;
            }
        }
    </style>
@endsection
