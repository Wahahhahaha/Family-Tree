@if($activeTab == 'social')
    @foreach($socials as $social)
        <tr>
            <td>{{ $social->socialid }}</td>
            <td>{{ $social->socialname }}</td>
            <td><code>{{ $social->socialicon }}</code></td>
            <td>
                <div class="action-buttons">
                    <button class="action-btn action-reset" 
                            onclick="openEditModal('{{ $social->socialid }}', '{{ $social->socialname }}', '{{ $social->socialicon }}')"
                            title="Edit">
                        <i data-lucide="edit-3"></i>
                    </button>
                    <form action="/management/social-media/{{ $social->socialid }}/delete" method="POST" style="display:inline;" onsubmit="return confirm('Delete this item?')">
                        @csrf
                        <button type="submit" class="action-btn" style="color: #d25764;" title="Delete">
                            <i data-lucide="trash-2"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
    @endforeach

    @if($socials->isEmpty())
        <tr>
            <td colspan="4" style="text-align: center; padding: 20px; color: #64748b;">No data found.</td>
        </tr>
    @endif
@endif
