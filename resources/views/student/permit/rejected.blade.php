{{-- <!-- resources/views/student/page/rejected.blade.php -->

@foreach($permits as $permit)
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $permit->title_activity }}</td>
        <td>{{ $permit->venue }}</td>

        <!-- SAFE DATE DISPLAY (NO MORE ERRORS!) -->
        <td>
            <strong>Start:</strong> {{ $permit->start_date_formatted }}<br>
            <small class="text-muted">{{ $permit->start_date_human }}</small>
        </td>
        <td>
            @if($permit->date_end)
                <strong>End:</strong> {{ $permit->end_date_formatted }}<br>
                <small class="text-muted">{{ $permit->end_date_human }}</small>
            @else
                <span class="text-muted">No end date</span>
            @endif
        </td>

        <td>{{ $permit->time_range }}</td>
        <td>
            <span class="badge badge-danger">
                {{ $permit->getCurrentStatus() }}
            </span>
        </td>
        <td>
            <a href="{{ route('student.permit.show', $permit->hashed_id) }}" class="btn btn-sm btn-outline-primary">
                View Details
            </a>
        </td>
    </tr>
@endforeach --}}
