<div class="action-buttons">
    <form action="/management/data-master/restore/{{ $type }}/{{ $id }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" class="action-btn" title="Restore" style="color: #1f9ad6;">
            <i data-lucide="rotate-ccw"></i>
        </button>
    </form>
    <form action="/management/data-master/force-delete/{{ $type }}/{{ $id }}" method="POST" style="display:inline;" onsubmit="return confirm('Permanently delete this item? This action cannot be undone.')">
        @csrf
        <button type="submit" class="action-btn" title="Delete Permanently" style="color: #d25764;">
            <i data-lucide="trash-2"></i>
        </button>
    </form>
</div>
