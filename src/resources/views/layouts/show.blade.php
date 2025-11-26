@push('css')
<link rel="stylesheet" href="{{ asset('css/layouts/show.css') }}">
@endpush

<div class="page-container">
    <div class="attendance-header">
        <p class="page-title">勤怠詳細</p>
    </div>

    <div class="form-wrap">
        <form action="{{ $formAction }}" method="POST">
            @csrf
            @if(!empty($method))
            @method($method)
            @endif

            <div class="attendance-show-area">
                <div class="partitioned-area">
                    <label class="col-1" for="">名前</label>
                    <span class="col-2">{{ $userName }}</span>
                </div>
                <div class="partitioned-area">
                    <label class="col-1" for="">日付</label>
                    <span class="col-2">{{ $date->format('Y年') }}
                    </span>
                    <div class="col-3"></div>
                    <span class="col-4">{{ $date->format('n月j日') }}
                    </span>
                </div>
                <input type="hidden" name="date" value="{{ $date }}">
                <div class="partitioned-area">
                    <label class="col-1" for="">出勤・退勤</label>
                    <input class="col-2" type="time" name="clock_in" value="{{  old('clock_in', $attendance->clock_in_formatted) }}" {{ $readonly ? 'readonly style=border:none' : '' }}>
                    <span class="col-3">〜</span>
                    <input class="col-4" type="time" name="clock_out" value="{{ old('clock_out', $attendance->clock_out_formatted) }}" {{ $readonly ? 'readonly style=border:none' : '' }}>

                    <span class="error-message">
                        @error('clock_in')
                        {!! $message !!}
                        @enderror
                    </span>
                </div>
                @foreach($attendance->breaks as $break)
                <div class="partitioned-area">
                    <label class="col-1">休憩{{ $loop->iteration }}</label>

                    <input class="col-2" type="time"
                        name="break_start[]"
                        value="{{ old('break_start.' . ($loop->index), $break->break_start_formatted) }}"
                        {{ $readonly ? 'readonly style=border:none' : '' }}>
                    <span class="col-3">〜</span>
                    <input class="col-4" type="time"
                        name="break_end[]"
                        value="{{ old('break_end.' . ($loop->index), $break->break_end_formatted) }}"
                        {{ $readonly ? 'readonly style=border:none' : '' }}>

                    <span class="error-message">
                        @error('break_start.' . $loop->index)
                        <div class="error">{{ $message }}</div>
                        @enderror

                        @error('break_end.' . $loop->index)
                        <div class="error">{!! $message !!}</div>
                        @enderror
                    </span>
                </div>
                @endforeach

                @if ($requestStatus !== 0 && $requestStatus !== 1)
                @php
                $nextBreakNumber = $attendance->breaks->count() + 1;
                @endphp
                <div class="partitioned-area">
                    <label class="col-1">休憩{{ $nextBreakNumber }}</label>

                    <input class="col-2" type="time"
                        name="break_start[]"
                        value="{{ old('break_start.' . $attendance->breaks->count()) }}"
                        {{ $readonly ? 'readonly style=border:none' : '' }}>

                    <span class="col-3">〜</span>

                    <input class="col-4" type="time"
                        name="break_end[]"
                        value="{{ old('break_end.' . $attendance->breaks->count()) }}"
                        {{ $readonly ? 'readonly style=border:none' : '' }}>
                </div>
                @endif

                <div class="partitioned-area__last">
                    <label class="col-1" for="">備考</label>
                    <textarea name="remarks" rowspan="15" {{ $readonly ? 'readonly' : '' }}>{{ old('remarks', $remarks) }}</textarea>

                    <span class="error-message">
                        @error('remarks')
                        {!! $message !!}
                        @enderror
                    </span>
                </div>
            </div>
            {!! $actionHtml !!}
        </form>
    </div>
</div>