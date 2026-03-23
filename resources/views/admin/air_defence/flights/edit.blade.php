@extends('adminlte::page')

@section('title', 'Редагувати виліт ППО')

@section('content_header')
    <h1>Редагувати виліт ППО #{{ $flight->id }}</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary">
                <form action="{{ route('air-defence.flights.update', $flight->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="air_defence_drone_id">Дрон</label>
                                    <select name="air_defence_drone_id" id="air_defence_drone_id" class="form-control @error('air_defence_drone_id') is-invalid @enderror" required>
                                        <option value="">Оберіть дрон</option>
                                        @foreach($drones as $drone)
                                            <option value="{{ $drone->id }}" {{ old('air_defence_drone_id', $flight->air_defence_drone_id) == $drone->id ? 'selected' : '' }}>
                                                {{ $drone->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('air_defence_drone_id')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="air_defence_ammunition_id">БК</label>
                                    <select name="air_defence_ammunition_id" id="air_defence_ammunition_id" class="form-control @error('air_defence_ammunition_id') is-invalid @enderror" required>
                                        <option value="">Оберіть БК</option>
                                        @foreach($ammunition as $item)
                                            <option value="{{ $item->id }}" {{ old('air_defence_ammunition_id', $flight->air_defence_ammunition_id) == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('air_defence_ammunition_id')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="coordinates">Координати (необов'язково)</label>
                                    <input type="text" name="coordinates" id="coordinates" class="form-control @error('coordinates') is-invalid @enderror" value="{{ old('coordinates', $flight->coordinates) }}">
                                    @error('coordinates')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_time">Час початку</label>
                                    <input type="datetime-local" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time', $flight->start_time?->format('Y-m-d\TH:i')) }}">
                                    @error('start_time')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_time">Час кінця</label>
                                    <input type="datetime-local" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time', $flight->end_time?->format('Y-m-d\TH:i')) }}">
                                    @error('end_time')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="result">Результат</label>
                                    <select name="result" id="result" class="form-control @error('result') is-invalid @enderror" required onchange="handleResultChange()">
                                        <option value="влучання" {{ old('result', $flight->result) == 'влучання' ? 'selected' : '' }}>Влучання</option>
                                        <option value="в районі цілі" {{ old('result', $flight->result) == 'в районі цілі' ? 'selected' : '' }}>В районі цілі</option>
                                        <option value="втрата борта" {{ old('result', $flight->result) == 'втрата борта' ? 'selected' : '' }}>Втрата борта</option>
                                        <option value="борт повернувся" {{ old('result', $flight->result) == 'борт повернувся' ? 'selected' : '' }}>Борт повернувся</option>
                                    </select>
                                    @error('result')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="detonation">Детонація</label>
                                    <select name="detonation" id="detonation" class="form-control @error('detonation') is-invalid @enderror" required>
                                        <option value="1" {{ old('detonation', $flight->detonation ? '1' : '0') == '1' ? 'selected' : '' }}>Так</option>
                                        <option value="0" {{ old('detonation', $flight->detonation ? '1' : '0') == '0' ? 'selected' : '' }}>Ні</option>
                                    </select>
                                    @error('detonation')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="stream_switch" name="stream_switch" value="1" {{ old('stream_switch', $flight->stream === '+') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="stream_switch">Стрім</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="comment">Коментар</label>
                            <textarea name="comment" id="comment" class="form-control @error('comment') is-invalid @enderror" rows="3">{{ old('comment', $flight->comment) }}</textarea>
                            @error('comment')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('air-defence.flights.index') }}" class="btn btn-default">Скасувати</a>
                        <button type="submit" class="btn btn-primary float-right">Зберегти</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function handleResultChange() {
            const result = document.getElementById('result').value;
            const detonation = document.getElementById('detonation');

            if (result === 'борт повернувся') {
                detonation.value = '0';
                detonation.disabled = true;
                if (!document.getElementById('detonation_hidden')) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'detonation';
                    hiddenInput.id = 'detonation_hidden';
                    hiddenInput.value = '0';
                    detonation.parentNode.appendChild(hiddenInput);
                }
            } else {
                detonation.disabled = false;
                const hiddenInput = document.getElementById('detonation_hidden');
                if (hiddenInput) {
                    hiddenInput.parentNode.removeChild(hiddenInput);
                }
            }
        }

        $(document).ready(function() {
            handleResultChange();
        });
    </script>
@endsection
