@if($activeTab == 'levels')
    @foreach($levels as $level)
        <tr>
            <td>{{ $level->levelid }}</td>
            <td>{{ $level->levelname }}</td>
            <td>
                <div class="action-buttons">
                    <button class="action-btn action-reset" 
                            onclick="openEditLevelModal('{{ $level->levelid }}', '{{ $level->levelname }}')"
                            title="Edit">
                        <i data-lucide="edit-3"></i>
                    </button>
                    <form action="/management/levels/{{ $level->levelid }}/delete" method="POST" style="display:inline;" onsubmit="return confirm('Delete this level?')">
                        @csrf
                        <button type="submit" class="action-btn" style="color: #d25764;" title="Delete">
                            <i data-lucide="trash-2"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
    @endforeach

    @if($levels->isEmpty())
        <tr>
            <td colspan="3" style="text-align: center; padding: 20px; color: #64748b;">No levels found.</td>
        </tr>
    @endif
@endif
