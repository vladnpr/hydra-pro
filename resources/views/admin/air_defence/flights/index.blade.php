@extends('adminlte::page')

@section('title', 'Вильоти ППО')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Вильоти ППО</h1>
        </div>
        <div class="col-sm-6">
            <a href="{{ route('air-defence.races.create') }}" class="btn btn-primary float-right">
                <i class="fas fa-plus"></i> Додати виліт
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Позиція / Координати</th>
                        <th>Час</th>
                        <th>Дрон / БК</th>
                        <th>Результат</th>
                        <th>Детонація</th>
                        <th>Дії</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($flights as $flight)
                        <tr>
                            <td>
                                <strong>{{ $flight->position?->name }}</strong><br>
                                <small class="text-muted">{{ $flight->coordinates }}</small>
                            </td>
                            <td>
                                <small>
                                    Початок: {{ $flight->start_time?->format('d.m.y H:i') }}<br>
                                    Кінець: {{ $flight->end_time?->format('d.m.y H:i') }}
                                </small>
                            </td>
                            <td>
                                {{ $flight->drone?->name }}<br>
                                <small class="text-muted">{{ $flight->ammunition?->name }}</small>
                            </td>
                            <td>{{ $flight->result }}</td>
                            <td>
                                @if($flight->detonation)
                                    <span class="badge badge-success">Так</span>
                                @else
                                    <span class="badge badge-danger">Ні</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('air-defence.races.edit', $flight->id) }}" class="btn btn-sm btn-default">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('air-defence.races.destroy', $flight->id) }}" method="POST" onsubmit="return confirm('Ви впевнені?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @if($flight->comment)
                        <tr>
                            <td colspan="6" class="bg-light py-1">
                                <small><strong>Коментар:</strong> {{ $flight->comment }}</small>
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Вильотів не знайдено</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $flights->links() }}
        </div>
    </div>
@endsection
