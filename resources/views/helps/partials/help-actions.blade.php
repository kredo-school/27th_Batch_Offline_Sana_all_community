<div class="help-actions dropdown">
    <button type="button" class="help-menu-button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Help actions">
        ⋮
    </button>

    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
        <li>
            <a href="{{ route('helps.edit', $help) }}" class="dropdown-item">
                Edit
            </a>
        </li>

        <li>
            <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal"
                data-bs-target="#deleteHelpModal{{ $help->id }}">
                Delete
            </button>
        </li>
    </ul>
</div>
