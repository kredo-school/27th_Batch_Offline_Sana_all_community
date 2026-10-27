<div class="event-actions dropdown">
    <button type="button" class="event-menu-button" id="eventMenu{{ $event->id }}" data-bs-toggle="dropdown"
        aria-expanded="false" aria-label="event-menu">
        <span aria-hidden="true">⋮</span>
    </button>

    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="eventMenu{{ $event->id }}">
        <li>
            <a href="{{ route('events.edit', ['event' => $event->id]) }}" class="dropdown-item">
                Edit
            </a>
        </li>

        <li>
            <hr class="dropdown-divider">
        </li>

        <li>
            <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal"
                data-bs-target="#deleteEventModal{{ $event->id }}">
                Delete
            </button>
        </li>
    </ul>
</div>
